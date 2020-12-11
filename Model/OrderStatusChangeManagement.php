<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model;

use DistriMedia\Connector\Api\Data\TrackIdInterface;
use DistriMedia\Connector\Api\OrderStatusChangeManagementInterface;
use DistriMedia\Connector\Api\Data\ShippedItemInterface;
use DistriMedia\Connector\Api\Data\ShippedItemInterfaceFactory;
use DistriMedia\Connector\Api\Data\ProductInterface;
use DistriMedia\Connector\Api\Data\ProductInterfaceFactory;
use DistriMedia\Connector\Helper\ErrorHandlingHelper;
use DistriMedia\Connector\Service\OrderSyncInterface;
use DistriMedia\Connector\Ui\Component\Listing\Column\SyncStatus\Options;
use DistriMedia\SoapClient\Struct\OrderItem;
use DistriMedia\SoapClient\Struct\Response\Inventory\StockItem;
use Exception;
use Magento\Framework\Notification\NotifierPool;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Rest\Request\Deserializer\Xml;
use Magento\ProductAlert\Model\Stock;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentManagementInterface;
use Magento\Sales\Model\Convert\OrderFactory as OrderConverterFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

class OrderStatusChangeManagement implements OrderStatusChangeManagementInterface
{
    const ORDER_STATUS = 'OrderStatus';

    /**
     * Magento Increment ID
     */
    const ORDER_NUMBER = 'OrderNumber';

    /**
     * Internal DistriMedia ID
     */
    const ORDER_ID = 'OrderID';

    const NUMBER_COLLI = 'NumberColli';
    const CARRIER = 'Carrier';
    const TRACK_AND_TRACE_URL = 'TrackAndTraceURL';
    const TRACK_IDS = 'TrackIDs';
    const SHIPPED_ITEMS = 'ShippedItems';
    const STATUS_OK = 'OK';

    private $deserializer;
    private $orderSync;
    private $orderManagement;
    private $orderFetcher;
    private $orderConverterFactory;
    private $trackFactory;
    private $objectManager;
    private $shipmentManagement;
    private $logger;
    private $config;
    private $productCollectionFactory;
    private $eanAttribute;
    private $errorHandlingHelper;

    /**
     * @var ProductCollection $productCollection
     */
    private $productCollection;

    private $notifierPool;

    private $shippedItemInterfaceFactory;

    private $productInterfaceFactory;

    private $orderRepository;

    public function __construct(
        Xml $deserializer,
        OrderSyncInterface $orderSync,
        OrderManagementInterface $orderManagement,
        OrderFetcherInterface $orderFetcher,
        OrderConverterFactory $orderConverterFactory,
        OrderRepositoryInterface $orderRepository,
        TrackFactory $trackFactory,
        ObjectManagerInterface $objectManager,
        ShipmentManagementInterface $shipmentManagement,
        LoggerInterface $logger,
        ConfigInterface $config,
        ProductCollectionFactory $productCollectionFactory,
        ErrorHandlingHelper $errorHandlingHelper,
        NotifierPool $notifierPool,
        ShippedItemInterfaceFactory $shippedItemInterfaceFactory,
        ProductInterfaceFactory $productInterfaceFactory
    )
    {
        $this->deserializer = $deserializer;
        $this->orderSync = $orderSync;
        $this->orderManagement = $orderManagement;
        $this->orderFetcher = $orderFetcher;
        $this->orderConverterFactory = $orderConverterFactory;
        $this->trackFactory = $trackFactory;
        $this->objectManager = $objectManager;
        $this->shipmentManagement = $shipmentManagement;
        $this->logger = $logger;
        $this->config = $config;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->errorHandlingHelper = $errorHandlingHelper;
        $this->notifierPool = $notifierPool;
        $this->shippedItemInterfaceFactory = $shippedItemInterfaceFactory;
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(
        string $OrderStatus,
        string $OrderID,
        string $OrderNumber,
        string $NumberColli = null,
        string $Carrier = null,
        string $TrackAndTraceURL = null,
        $TrackIDs = [],
        $ShippedItems = []
    )
    {
        if (!$this->config->isEnabled()) {
            throw new \Exception("DistriMedia Connector is not enabled");
        }

        /* @var Order $order */
        $order = $this->orderFetcher->getOrderByDistriMediaData($OrderNumber, $OrderID);

        if ($order === null) {
            throw new \Exception("Could not find order with DistriMedia Inc ID {$OrderID} or Magento Inc ID {$OrderNumber}");
        }

        //this means that there's only 1 track ID
        if (array_key_exists(TrackIdInterface::TRACK_ID, $TrackIDs)) {
            $TrackIDs = [$TrackIDs];
        }

        //this means that there's only 1 shipped item
        if (array_key_exists(ShippedItemInterface::PRODUCT, $ShippedItems)) {
            $ShippedItems = [$ShippedItems];
        }


        $data = [
            self::ORDER_ID => $OrderID,
            self::ORDER_STATUS => $OrderStatus,
            self::ORDER_NUMBER => $OrderNumber,
            self::NUMBER_COLLI => $NumberColli,
            self::CARRIER => $Carrier,
            self::TRACK_AND_TRACE_URL => $TrackAndTraceURL,
            self::TRACK_IDS => $TrackIDs,
            self::SHIPPED_ITEMS => $ShippedItems
        ];

        try {
            switch ($OrderStatus) {
                case Options::STATUS_CANCELLED:
                    $this->notifyShopOwner($order);
                    break;
                case Options::STATUS_PARTLY_SHIPPED:
                case Options::STATUS_SHIPPED:
                    $this->shipOrder($order, $data);
                    break;
            }

            $this->updateOrderStatus($order, $data);
        } catch (\Exception $exception) {
            //
        }

        return '<Status>' . self::STATUS_OK . '</Status>';
    }

    /**
     * @param Order $order
     */
    private function notifyShopOwner(Order $order): void
    {

        $subject = __('Manual Action required. Order %1 has been canceled by DistriMedia ERP', $order->getIncrementId())->render();
        $message = __('Order %1 has been canceled by DistriMedia ERP', $order->getIncrementId())->render();
        $this->notifierPool->addMajor($message, $message);
        $this->errorHandlingHelper->sendErrorEmail([$message], $subject, $subject);
        $this->logger->critical($message);
    }

    private function updateOrderStatus(Order $order, array $data)
    {
        try {
            $possibleOptions = Options::getDistriMediaStatusses();
            $extAttrs = $order->getExtensionAttributes();

            $status = $data[self::ORDER_STATUS];
            $internalStatus = isset($possibleOptions[$status]) ? $possibleOptions[$status] : null;

            if (!$internalStatus) {
                throw new Exception("Cannot find status {$status}");
            }

            $extAttrs->setDistriMediaSyncStatus($internalStatus);
            $order->setExtensionAttributes($extAttrs);
            $this->orderRepository->save($order);
        } catch (\Exception $exception) {
            $this->logger->critical("Error updating Order {$order->getIncrementId()}: " . $exception->getMessage());
        }
    }

    private function shipOrder(Order $order, array $data)
    {
        $shipment = $this->createShipment($order, $data);

        if ($shipment instanceof ShipmentInterface) {
            $this->_saveShipment($shipment);
            $this->shipmentManagement->notify($shipment->getId());
        }
    }

    private function createShipment(Order $order, array $data): ?ShipmentInterface
    {
        $shipment = null;

        try {
            /* @var \Magento\Sales\Model\Convert\Order $converter */
            $converter = $this->orderConverterFactory->create();
            $shipment = $converter->toShipment($order);

            /* @var Track $track */
            $track = $this->trackFactory->create();

            $trackNumber = $data[self::TRACK_AND_TRACE_URL];

            $track->setDescription('BPost');
            $track->setTitle("BPost");
            $track->setCarrierCode($data[self::CARRIER]);
            $track->setNumber($trackNumber);

            $m2OrderItems = [];

            if (array_key_exists(self::SHIPPED_ITEMS, $data)) {
                $shippedItems = $data[self::SHIPPED_ITEMS];
                $m2OrderItems = $this->getOrderItems($shippedItems, $order);
            }

            if (empty($m2OrderItems)) {
                throw new \Exception(("No order items found"));
            }

            foreach ($m2OrderItems as $item) {
                /* @var \Magento\Sales\Model\Order\Item $orderItem */
                $orderItem = $item['orderItem'];

                /* @var ShippedItemInterface $shippedItem */
                $shippedItem = $item['shippedItem'];

                /* @var ProductInterface $product */
                $product = $item['product'];

                // Check if order item has qty to ship or is virtual
                if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                }

                $qtyShipped = $product->getPieces();

                // Create shipment item with qty
                $shipmentItem = $converter->itemToShipmentItem($orderItem)->setQty($qtyShipped);

                // Add shipment item to shipment
                $shipment->addItem($shipmentItem);
            }

            if ($shipment->getItems() === null) {
                throw new \Exception(("Order {$order->getIncrementId()} is already shipped"));
            }

            $shipment->addTrack($track);
            $shipment->register();
            $shipment->getOrder()->setIsInProcess(true);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            $this->logger->warning($message);
            throw $exception;
        }

        return $shipment;
    }

    /**
     * Save shipment and order in one transaction
     *
     * @param Shipment $shipment
     * @return $this
     */
    protected function _saveShipment(Shipment $shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transaction = $this->objectManager->create(
            \Magento\Framework\DB\Transaction::class
        );

        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->save();

        return $this;
    }

    /**
     * I try to match the order items with the data sent to this endpoint based on the EAN CODE saved on the order item
     * @param array $orderItems
     * @param Order $order
     * @return array
     * @throws \Exception
     */
    private function getOrderItems(array $shippedItems, Order $order)
    {
        $result = [];
        $m2OrderItems = $order->getAllItems();

        /** @var ShippedItemInterface $shippedItem */
        foreach ($shippedItems as $key => $shippedItemData) {
            $shippedItem = $this->shippedItemInterfaceFactory->create(['data' => $shippedItemData]);
            /* @var ProductInterface $product */
            $products = $shippedItem->getProduct();
            if (array_key_exists(ProductInterface::EAN, $products)) {
                $products = [$products];
            }
            foreach ($products as $productData) {
                $product = $this->productInterfaceFactory->create(['data' => $productData]);
                $eanCode = $product->getEAN();
                $match = false;
                foreach ($m2OrderItems as $m2OrderItem) {
                    $orderItemExtensionAttrs = $m2OrderItem->getExtensionAttributes();

                    if ($orderItemExtensionAttrs) {
                        $orderItemEanCode = $orderItemExtensionAttrs->getDistriMediaEanCode();
                        if ($eanCode === $orderItemEanCode) {
                            $orderItems[$key]['orderItem'] = $m2OrderItem;
                            $result[] = [
                                'orderItem' => $m2OrderItem,
                                'shippedItem' => $shippedItem,
                                'product' => $product
                            ];
                            $match = true;
                            break;
                        }
                    }
                }

                if (!$match) {
                    throw new \Exception("No order item found for ean code = {$eanCode}");
                }
            }

        }

        return $result;
    }
}

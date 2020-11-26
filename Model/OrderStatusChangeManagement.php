<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model;

use DistriMedia\Connector\Api\OrderStatusChangeManagementInterface;
use DistriMedia\Connector\Helper\ErrorHandlingHelper;
use DistriMedia\Connector\Service\OrderSyncInterface;
use DistriMedia\Connector\Ui\Component\Listing\Column\SyncStatus\Options;
use DistriMedia\SoapClient\Struct\Response\Inventory\StockItem;
use Magento\Framework\Notification\NotifierPool;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Rest\Request\Deserializer\Xml;
use Magento\ProductAlert\Model\Stock;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\OrderManagementInterface;
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

    public function __construct(
        Xml $deserializer,
        OrderSyncInterface $orderSync,
        OrderManagementInterface $orderManagement,
        OrderFetcherInterface $orderFetcher,
        OrderConverterFactory $orderConverterFactory,
        TrackFactory $trackFactory,
        ObjectManagerInterface $objectManager,
        ShipmentManagementInterface $shipmentManagement,
        LoggerInterface $logger,
        ConfigInterface $config,
        ProductCollectionFactory $productCollectionFactory,
        ErrorHandlingHelper $errorHandlingHelper,
        NotifierPool $notifierPool
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
    }

    public function execute(
        string $OrderID,
        string $OrderNumber,
        string $OrderStatus,
        string $NumberColli,
        string $Carrier,
        string $TrackAndTraceURL,
        $TrackIDs,
        $ShippedItems
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
    }

    /**
     * @param Order $order
     */
    private function notifyShopOwner(Order $order): void
    {

        $subject = __('Manual Action required. Order %1 has been canceled by DistriMedia ERP', $order->getIncrementId())->getText();
        $message =__('Order %1 has been canceled by DistriMedia ERP', $order->getIncrementId())->getText();
        $this->notifierPool->addMajor($message->getText(), $message->getText());
        $this->errorHandlingHelper->sendErrorEmail([$message->getText()], $subject, $subject);
        $this->logger->critical($message);
    }

    private function updateOrderStatus(Order $order, array $data)
    {
        $order->setDistriMediaSyncStatus($data[self::ORDER_STATUS]);
        $order->setDistriMediaIncrementId($data[self::ORDER_ID]);

        $order->save();
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

            $track->setDescription($trackNumber);
            $track->setTitle("BPost");
            $track->setCarrierCode($data[self::CARRIER]);

            $m2OrderItems = $this->getOrderItems($data[self::SHIPPED_ITEMS]['Product'], $order);

            if (empty($m2OrderItems)) {
                throw new \Exception(("No order items found"));
            }

            foreach ($m2OrderItems as $item) {
                $orderItem = $item['orderItem'];
                // Check if order item has qty to ship or is virtual
                if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                }

                $qtyShipped = $item[StockItem::PIECES];

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
    private function getOrderItems(array $orderItems, Order $order)
    {
        $m2OrderItems = $order->getAllItems();

        //this means that there's only product
        if (array_key_exists(StockItem::EAN, $orderItems)) {
            $orderItems = [$orderItems];
        }

        foreach ($orderItems as $key => $orderItem) {
            $eanCode = $orderItem[StockItem::EAN];
            $match = false;
            foreach ($m2OrderItems as $m2OrderItem) {
                $orderItemExtensionAttrs = $m2OrderItem->getExtensionAttributes();

                if ($orderItemExtensionAttrs) {
                    $orderItemEanCode = $orderItemExtensionAttrs->getDistriMediaEanCode();
                    if ($eanCode === $orderItemEanCode) {
                        $orderItems[$key]['orderItem'] = $m2OrderItem;
                        $match = true;
                        break;
                    }
                }
            }

            if (!$match) {
                throw new \Exception("No order item found for ean code = {$eanCode}");
            }
        }

        return $orderItems;
    }
}

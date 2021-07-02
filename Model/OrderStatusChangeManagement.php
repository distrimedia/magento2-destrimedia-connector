<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model;

use DistriMedia\Connector\Api\Data\ProductInterface;
use DistriMedia\Connector\Api\Data\ProductInterfaceFactory;
use DistriMedia\Connector\Api\Data\ShippedItemInterface;
use DistriMedia\Connector\Api\Data\ShippedItemInterfaceFactory;
use DistriMedia\Connector\Api\Data\TrackIdInterface;
use DistriMedia\Connector\Api\OrderStatusChangeManagementInterface;
use DistriMedia\Connector\DistriMediaException;
use DistriMedia\Connector\Helper\ErrorHandlingHelper;
use DistriMedia\Connector\Service\OrderSyncInterface;
use DistriMedia\Connector\Ui\Component\Listing\Column\SyncStatus\Options;
use DistriMedia\SoapClient\Struct\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Notification\NotifierPool;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Rest\Request\Deserializer\Xml;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentManagementInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Convert\OrderFactory as OrderConverterFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;

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
    const STATUS_ERROR = 'ERROR';

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
    private $errorHandlingHelper;
    /**
     * @var ProductCollection
     */
    private $productCollection;
    private $notifierPool;
    private $shippedItemInterfaceFactory;
    private $productInterfaceFactory;
    private $orderRepository;

    private $shipmentConverter;
    private $orderItemRepository;
    private $shipmentRepository;
    private $orderFactory;

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
        ProductInterfaceFactory $productInterfaceFactory,
        OrderItemRepositoryInterface $orderItemRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        OrderFactory $orderFactory
    ) {
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
        $this->orderItemRepository = $orderItemRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->orderFactory = $orderFactory;
    }

    /**
     * {@inheritDoc}
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
    ) {
        if (!$this->config->isEnabled()) {
            throw new DistriMediaException('DistriMedia Connector is not enabled');
        }

        /* @var Order $order */
        $order = $this->orderFetcher->getOrderByDistriMediaData($OrderNumber, $OrderID);

        if ($order === null) {
            throw new DistriMediaException(
                "Could not find order with DistriMedia Inc ID {$OrderID} or Magento Inc ID {$OrderNumber}"
            );
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
            self::SHIPPED_ITEMS => $ShippedItems,
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

            $this->updateOrderStatus($order->getId(), $data);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            return '<Status>' . self::STATUS_ERROR . '</Status>' .
                '<Message>' . $exception->getMessage() . '</Message>';
        }

        return '<Status>' . self::STATUS_OK . '</Status>';
    }

    private function notifyShopOwner(Order $order): void {
        $subject = __(
            'Order %1 has been canceled by DistriMedia ERP. Please take action in Magento (create Credit Memo).',
            $order->getIncrementId()
        )->render();
        $message = __('Order %1 has been canceled by DistriMedia ERP', $order->getIncrementId())->render();
        $this->notifierPool->addMajor($message, $message);
        $this->errorHandlingHelper->sendErrorEmail([$message], $subject, $subject);
        $this->logger->critical($message);
    }

    private function updateOrderStatus(string $orderId, array $data) {
        try {
            $order = $this->orderFactory->create()->load($orderId);

            $possibleOptions = Options::getDistriMediaStatusses();
            $extAttrs = $order->getExtensionAttributes();

            $status = $data[self::ORDER_STATUS];
            $internalStatus = isset($possibleOptions[$status]) ? $possibleOptions[$status] : null;

            if (!$internalStatus) {
                throw new DistriMediaException("Cannot find status {$status}");
            }

            $extAttrs->setDistriMediaSyncStatus($internalStatus);
            $order->setExtensionAttributes($extAttrs);
            $this->orderRepository->save($order);
        } catch (\Exception $exception) {
            $this->logger->critical("Error updating Order {$order->getIncrementId()}: " . $exception->getMessage());
        }
    }

    private function shipOrder(Order $order, array $data) {
        $shipment = $this->createShipment($order, $data);

        if ($shipment instanceof ShipmentInterface) {
            $shipment->register();
            $this->saveShipment($shipment);

            $this->shipmentManagement->notify($shipment->getId());
        }
    }

    private function hasCompleteBundles(string $orderId): array
    {
        $order = $this->orderRepository->get($orderId);
        $orderItems = $order->getAllItems();
        $converter = $this->getShipmentConverter();

        $bundles = [];
        foreach ($orderItems as $orderItem) {
            $parent = $orderItem->getParentItem();
            if ($parent && $parent->getProductType() === 'bundle') {
                $qtyShipped = (int) $orderItem->getExtensionAttributes()->getDistriMediaShippedQty();
                $qtyInvoiced = (int) $orderItem->getQtyInvoiced();
                $isCompleteSimple = (bool) ($qtyInvoiced <= $qtyShipped);
                if (!array_key_exists($parent->getSku(), $bundles)) {
                    $bundles[$parent->getSku()] = [
                        'item' => $parent,
                        'simples' => [
                            $orderItem->getSku() => $isCompleteSimple
                        ],
                    ];
                } else {
                    $bundles[$parent->getSku()]['simples'][$orderItem->getSku()] = $isCompleteSimple;
                }
            }
        }

        $items = [];
        foreach ($bundles as $bundle) {
            $simples = $bundle['simples'];
            /** @var OrderItemInterface $bundleItem */
            $bundleItem = $bundle['item'];
            if (!in_array(false, $simples)) {
                if ((int)$bundleItem->getQtyShipped() <= 0) {
                    $shipmentItem = $converter->itemToShipmentItem($bundleItem)->setQty($bundleItem->getQtyInvoiced());
                    $items[] = $shipmentItem;
                }
            }
        }

        return $items;
    }

    private function getShipmentConverter(): \Magento\Sales\Model\Convert\Order
    {
        if ($this->shipmentConverter === null) {
            /* @var \Magento\Sales\Model\Convert\Order $converter */
            $this->shipmentConverter = $this->orderConverterFactory->create();
        }

        return $this->shipmentConverter;
    }

    private function createShipment(Order $order, array $data): ?ShipmentInterface
    {
        $shipment = null;

        try {
            $converter = $this->getShipmentConverter();
            $shipment = $converter->toShipment($order);

            $m2OrderItems = [];

            if (array_key_exists(self::SHIPPED_ITEMS, $data)) {
                $shippedItems = $data[self::SHIPPED_ITEMS];
                $m2OrderItems = $this->getOrderItems($shippedItems, $order);
            }

            $shipmentItems = [];

            foreach ($m2OrderItems as $item) {
                /* @var \Magento\Sales\Model\Order\Item $orderItem */
                $orderItems = $item['orderItems'];

                /* @var ShippedItemInterface $shippedItem */
                $shippedItem = $item['shippedItem'];

                /* @var ProductInterface $product */
                $product = $item['product'];

                $createdShipmentItems = $this->createShipmentItems($orderItems, $shippedItem, $product, $order, $converter);
                $shipmentItems = array_merge($createdShipmentItems, $shipmentItems);
            }

            foreach ($shipmentItems as $shipmentItem) {
                // Add shipment item to shipment
                $shipment->addItem($shipmentItem);
            }

            $bundleItems = $this->hasCompleteBundles($order->getId());

            foreach ($bundleItems as $bundleItem) {
                $shipment->addItem($bundleItem);
            }

            if ($shipment->getItems() === null) {
                throw new DistriMediaException(("Order {$order->getIncrementId()} is already shipped"));
            }

            $trackNumber = $data[self::TRACK_AND_TRACE_URL];
            $carrierCode = $data[self::CARRIER];

            $track = $this->createTrack($trackNumber, $carrierCode);

            $shipment->addTrack($track);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            $this->logger->warning($message);
            throw new DistriMediaException($exception->getMessage());
        }

        return $shipment;
    }

    /**
     * @param array $orderItems
     * @param ShippedItemInterface $shippedItem
     * @param ProductInterface $product
     * @param Order $order
     * @param \Magento\Sales\Model\Convert\Order $converter
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createShipmentItems(
        array $orderItems,
        ShippedItemInterface $shippedItem,
        ProductInterface $product,
        Order $order,
        \Magento\Sales\Model\Convert\Order $converter
    ): array
    {
        $result = [];

        foreach ($orderItems as $orderItem) {
            // Check if order item has qty to ship or is virtual
            $isVirtual = (bool) $orderItem->getIsVirtual();
            $qtyToShip = (int) $orderItem->getQtyInvoiced() - (int) $orderItem->getQtyToShip();

            $qtyShipped = (int) $product->getPieces();

            if (!$qtyToShip || $isVirtual) {
                // Create shipment item with qty
                $shipmentItem = $converter->itemToShipmentItem($orderItem)->setQty($qtyShipped);

                $result[] = $shipmentItem;
            }

            $orderItemExtenstionAttrs = $orderItem->getExtensionAttributes();
            $qtyShipped = $orderItemExtenstionAttrs->getDistriMediaShippedQty() + $qtyShipped;
            $orderItemExtenstionAttrs->setDistriMediaShippedQty($qtyShipped);
            $orderItem->setExtensionAttributes($orderItemExtenstionAttrs);

            $this->orderItemRepository->save($orderItem);
        }

        return $result;
    }

    private function createTrack(string $trackNumber, string $carrierCode)
    {
        /* @var Track $track */
        $track = $this->trackFactory->create();

        $track->setDescription('Uw transporteur');
        $track->setTitle('Uw transporteur');
        $track->setCarrierCode($carrierCode);
        $track->setNumber($trackNumber);

        return $track;
    }
    /**
     * Save shipment and order in one transaction
     * @return $this
     */
    protected function saveShipment(Shipment $shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transaction = $this->objectManager->create(
            \Magento\Framework\DB\Transaction::class
        );

        $this->shipmentRepository->save($shipment);

        foreach ($shipment->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            $this->orderItemRepository->save($orderItem);
        }

        $order = $this->orderFactory->create()->load($shipment->getOrderId());
        $this->orderRepository->save($order);

        return $this;
    }

    private function stripLeadingZeros(string $str): string
    {
        return ltrim($str, '0');
    }

    /**
     * I try to match the order items with the data sent to this endpoint based on the EAN CODE saved on the order item
     * @param array $shippedItems
     * @return array
     * @throws DistriMediaException
     */
    private function getOrderItems(array $shippedItems, Order $order)
    {
        $result = [];
        $m2OrderItems = $order->getAllItems();

        /* @var ShippedItemInterface $shippedItem */
        foreach ($shippedItems as $key => $shippedItemData) {
            $shippedItem = $this->shippedItemInterfaceFactory->create(['data' => $shippedItemData]);
            /* @var ProductInterface $product */
            $products = $shippedItem->getProduct() ?: [];
            if (array_key_exists(ProductInterface::EAN, $products)) {
                $products = [$products];
            }
            foreach ($products as $productData) {
                $product = $this->productInterfaceFactory->create(['data' => $productData]);
                $eanCode = $product->getEAN() ? $this->stripLeadingZeros($product->getEAN()) : '';

                if (!empty($eanCode)) {
                    $items = $this->getOrderItemsByEanCode($order, $eanCode);

                    if (!empty($items)) {
                        $result[] = [
                            'orderItems' => $items,
                            'shippedItem' => $shippedItem,
                            'product' => $product,
                        ];
                    } else {
                        throw new DistriMediaException(("No order item found for ean code = {$eanCode}"));
                    }
                }
            }
        }

        if (empty($result)) {
            throw new DistriMediaException('No order items found');
        }

        return $result;
    }

    private function getOrderItemsByEanCode(Order $order, string $eanCode): array
    {
        $result = [];
        $m2OrderItems = $order->getAllItems();

        foreach ($m2OrderItems as $m2OrderItem) {
            $orderItemExtensionAttrs = $m2OrderItem->getExtensionAttributes();

            if ($orderItemExtensionAttrs) {
                $orderItemEanCode = $orderItemExtensionAttrs->getDistriMediaEanCode();
                if (!empty($orderItemEanCode)) {
                    $orderItemEanCode = $this->stripLeadingZeros($orderItemEanCode);
                }
                if ($eanCode === $orderItemEanCode) {
                    $result[] = $m2OrderItem;
                }
            }
        }

        return $result;
    }
}

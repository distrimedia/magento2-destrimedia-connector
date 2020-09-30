<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Service;

use DistriMedia\Connector\Model\ConfigInterface;
use DistriMedia\Connector\Ui\Component\Listing\Column\SyncStatus\Options;
use DistriMedia\SoapClient\InvalidOrderException;
use DistriMedia\SoapClient\Service\Order as DistriMediaOrderService;
use DistriMedia\SoapClient\Struct\Order as DistriMediaOrderStruct;
use DistriMedia\SoapClient\Struct\Response\Order as DistriMediaOrderResponse;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Psr\Log\LoggerInterface;

class OrderSync extends AbstractSync implements OrderSyncInterface
{
    /**
     * @var DistriMediaOrderService
     */
    private $distriMediaOrderService;
    private $orderRepository;
    private $extensionFactory;
    private $orderCollectionFactory;
    private $orderBuilderFactory;

    public function __construct(
        LoggerInterface $logger,
        ConfigInterface $config,
        OrderRepositoryInterface $orderRepository,
        OrderExtensionFactory $orderExtensionFactory,
        OrderBuilderFactory $orderBuilderFactory,
        OrderCollectionFactory $orderCollectionFactory
    )
    {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->extensionFactory = $orderExtensionFactory;
        $this->orderBuilderFactory = $orderBuilderFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($logger, $config);
    }

    /**
     * I create a new Inventory service
     */
    private function init(): void
    {
        if ($this->distriMediaOrderService === null) {
            $uri = $this->config->getApiUri();
            $password = $this->config->getApiPassword();
            $webshopCode = $this->config->getWebshopCode();

            if (!empty($uri) && !empty($password) && !empty($webshopCode)) {
                $this->distriMediaOrderService = new DistriMediaOrderService($uri, $webshopCode, $password);
            } else {
                throw new \Exception("Invalid DistriMedia Configuration. Some fields are missing (uri, webshopcode or password)");
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function syncDistriMediaOrder(DistriMediaOrderStruct $distriMediaOrder)
    {
        $this->init();
        return $this->distriMediaOrderService->createOrder($distriMediaOrder);
    }

    /**
     * @inheritDoc
     */
    public function cancelOrder(OrderInterface $order): ? bool
    {
        $this->init();
        try {
            $extensionAttrs = $order->getExtensionAttributes();
            $referenceId = $extensionAttrs->getDistriMediaIncrementId();

            $this->distriMediaOrderService->changeOrderStatusByReferenceId(
                $referenceId,
                DistriMediaOrderService::ORDER_STATUS_CANCEL
            );

            //update the model
            $extensionAttrs->setDistriMediaSyncStatus(Options::SYNC_STATUS_PENDING_CANCELED);

            $order->setExtensionAttributes($extensionAttrs);
            $this->orderRepository->save($order);

            return true;
        } catch (InvalidOrderException $invalidOrderException) {
            $this->logger->critical($invalidOrderException->getMessage());
            throw $invalidOrderException;
        }
    }

    /**
     * @inheritDoc
     */
    public function preprareOrderForSync(OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes() ?: $this->extensionFactory->create();

        $queueStatus = $extensionAttributes->getDistriMediaSyncStatus();
        if ((int)$queueStatus === Options::SYNC_STATUS_NOT_SYNCED) {
            try {
                $status = Options::SYNC_STATUS_FAILED;

                /* @var OrderBuilder $orderBuilder */
                $orderBuilder = $this->orderBuilderFactory->create();
                $distriMediaOrder = $orderBuilder->convert($order);

                /* @var DistriMediaOrderResponse $result */
                $result = $this->syncDistriMediaOrder($distriMediaOrder);

                if ($result instanceof DistriMediaOrderResponse) {
                    $incrementId = $result->getOrderID();

                    if ($incrementId !== null) {
                        $extensionAttributes->setDistriMediaIncrementId($incrementId);
                        $status = Options::SYNC_STATUS_SYNCED;
                    } else {
                        throw new \Exception($result->getReason());
                    }
                }

                //update the model
                $extensionAttributes->setDistriMediaSyncStatus($status);

                $order->setExtensionAttributes($extensionAttributes);
                $this->orderRepository->save($order);
            } catch (\Exception $e) {
                $this->logger->critical(
                    "DistriMedia SyncOrders Cron: failed to sync message: " . $e->getMessage()
                );
            }
        }
    }
}

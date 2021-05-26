<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Cron;

use DistriMedia\Connector\Model\ConfigInterface;
use DistriMedia\Connector\Model\OrderFetcherInterface;
use DistriMedia\Connector\Service\OrderSyncInterface;
use DistriMedia\Connector\Ui\Component\Listing\Column\SyncStatus\Options;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * I am responsible for syncing Paid orders and canceled orders to DistriMedia
 */
class SyncOrders
{
    const MAX_SYNC_ATTEMPTS = 3;

    private $orderSync;
    private $orderFetcher;
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(
        OrderSyncInterface $orderSync,
        OrderFetcherInterface $orderFetcher,
        ConfigInterface $config,
        LoggerInterface $logger
    ) {
        $this->orderFetcher = $orderFetcher;
        $this->orderSync = $orderSync;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        if ($this->config->isEnabled()) {
            $orders = $this->orderFetcher->getUnsyncedOrdersInProgress();

            /* @var Order $order */
            foreach ($orders as $order) {
                $order = $this->orderFetcher->getOrderByEntityId($order->getId());
                $isPaid = $this->isOrderCompletelyPaid($order);
                $orderExtAttrs = $order->getExtensionAttributes();
                $syncStatus = (int) $orderExtAttrs->getDistriMediaSyncStatus();
                $syncAttempts = (int) $orderExtAttrs->getDistriMediaSyncAttempts();
                $allowedStatus = in_array(
                    $syncStatus,
                    [Options::SYNC_STATUS_NOT_SYNCED, Options::SYNC_STATUS_RETRY]
                ) ? true : false;
                if ($isPaid && $allowedStatus && $syncAttempts < self::MAX_SYNC_ATTEMPTS) {
                    try {
                        $this->orderSync->preprareOrderForSync($order);
                    } catch (\Throwable $e) {
                        $this->logger->error(
                            'Failed to sync order with id: ' . $order->getId(),
                            ['exception' => $e]
                        );
                    }
                }
            }

            return $this;
        }
    }

    /**
     * Only orders that are completely paid should be synced
     */
    private function isOrderCompletelyPaid(Order $order): bool
    {
        $totalDue = $order->getBaseTotalDue();
        if ($totalDue === floatval(0)) {
            return true;
        }

        return false;
    }
}

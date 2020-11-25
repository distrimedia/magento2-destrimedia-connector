<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Cron;

use DistriMedia\Connector\Model\ConfigInterface;
use DistriMedia\Connector\Model\OrderFetcherInterface;
use DistriMedia\Connector\Service\OrderSyncInterface;
use DistriMedia\SoapClient\InvalidOrderException;
use Magento\Framework\Api\FilterFactory;
use Magento\Framework\Api\Search\FilterGroupFactory;
use Magento\Framework\Api\SearchCriteriaFactory;
use Magento\Sales\Model\Order;
use DistriMedia\Connector\Ui\Component\Listing\Column\SyncStatus\Options;

/**
 * I am responsible for syncing Paid orders and canceled orders to DistriMedia
 * @package DistriMedia\Connector\Cron
 */
class SyncOrders
{
    private $orderSync;
    private $orderFetcher;
    private $config;

    const MAX_SYNC_ATTEMPTS = 3;

    public function __construct(
        OrderSyncInterface $orderSync,
        OrderFetcherInterface $orderFetcher,
        ConfigInterface $config
    )
    {
        $this->orderFetcher = $orderFetcher;
        $this->orderSync = $orderSync;
        $this->config = $config;
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
                $allowedStatus = in_array($syncStatus, [Options::SYNC_STATUS_NOT_SYNCED, Options::SYNC_STATUS_RETRY]) ? true : false;

                if ($isPaid && $allowedStatus && $syncAttempts < self::MAX_SYNC_ATTEMPTS) {
                    $this->orderSync->preprareOrderForSync($order);
                }
            }

            return $this;
        }
    }

    /**
     * Only orders that are completely paid should be synced
     * @param Order $order
     * @return bool
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

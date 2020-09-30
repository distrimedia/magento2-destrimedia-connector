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

/**
 * I am responsible for syncing Paid orders and canceled orders to DistriMedia
 * @package DistriMedia\Connector\Cron
 */
class SyncOrders
{
    private $orderSync;
    private $orderFetcher;
    private $config;

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
            $orders = $this->orderFetcher->getUnSyncedOrders();
            /* @var Order $order */
            foreach ($orders as $order) {
                $result = $this->isOrderCompletelyPaid($order);
                if ($result) {
                    $this->orderSync->preprareOrderForSync($order);
                }
            }

            $canceledOrders = $this->orderFetcher->getCanceledOrders();

            foreach ($canceledOrders as $order) {
                if (!empty($referenceId)) {
                    try {
                        $this->orderSync->cancelOrder($order);
                    } catch (InvalidOrderException $invalidOrderException) {
                        //already logged
                    }
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

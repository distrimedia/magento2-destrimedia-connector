<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Observer;

use DistriMedia\Connector\Model\ConfigInterface;
use DistriMedia\Connector\Service\OrderSyncInterface;
use DistriMedia\SoapClient\InvalidOrderException;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;

class SyncCanceledOrder implements ObserverInterface
{
    private $orderSync;
    private $messageManager;
    private $config;

    public function __construct(
        OrderSyncInterface $orderSync,
        ManagerInterface $messageManager,
        ConfigInterface $config
    )
    {
        $this->orderSync = $orderSync;
        $this->messageManager = $messageManager;
        $this->config = $config;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order instanceof OrderInterface && $this->config->isEnabled()) {
            try {
                $this->orderSync->cancelOrder($order);
            } catch (InvalidOrderException $invalidOrderException) {
                $this->messageManager->addErrorMessage($invalidOrderException->getMessage());
            }
        }
    }
}

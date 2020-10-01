<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Plugin;

use DistriMedia\Connector\Model\ConfigInterface;
use DistriMedia\Connector\Service\OrderSyncInterface;
use DistriMedia\SoapClient\InvalidOrderException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

/**
 * When a shopowner creates a credit memo in the shop, we should try to cancel the order in DistriMedia. Only if it's succesful in DistriMedia, the credit memo is allowed.
 * Class CancelOrderAfterCreditMemoCreation
 * @package DistriMedia\Connector\Plugin
 */
class CancelOrderAfterCreditMemoCreation
{
    private $orderSync;
    private $messageManager;
    private $config;
    private $orderRepository;

    public function __construct(
        OrderSyncInterface $orderSync,
        ManagerInterface $messageManager,
        ConfigInterface $config,
        OrderRepositoryInterface $orderRepository
    )
    {
        $this->orderSync = $orderSync;
        $this->messageManager = $messageManager;
        $this->config = $config;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param CreditmemoManagementInterface $subject
     * @param CreditmemoInterface $result
     * @return CreditmemoInterface
     * @throws \Exception
     */
    public function beforeRefund(CreditmemoManagementInterface $subject, CreditmemoInterface $result)
    {
        if ($this->config->isEnabled()) {
            try {
                $order = $this->orderRepository->get($result->getOrderId());

                //we should be able to refund shipped items
                if ($order->getStatus() !== Order::STATE_COMPLETE) {
                    $this->orderSync->cancelOrder($order);
                }
                return $result;
            } catch (InvalidOrderException $invalidOrderException) {
                $this->messageManager->addErrorMessage($invalidOrderException->getMessage());
                throw new \Exception($invalidOrderException->getMessage());
            }
        }
    }
}

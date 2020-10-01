<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Plugin;

use DistriMedia\Connector\Model\ConfigInterface;
use DistriMedia\Connector\Service\OrderBuilder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use DistriMedia\SoapClient\Service\Customer as CustomerService;

/**
 * I am responsible for syncing the updated address to DistriMedia
 * Only if DistriMedia allows the change of the address,the transaction can go through.
 * Class SyncUpdatedShippingAddress
 * @package DistriMedia\Connector\Plugin
 */
class SyncUpdatedShippingAddress
{
    private $orderRepository;
    private $orderBuilder;
    private $customerService;
    private $messageManager;
    private $config;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderBuilder $orderBuilder,
        CustomerService $customerService,
        ManagerInterface $messageManager,
        ConfigInterface $config
    )
    {
        $this->orderRepository = $orderRepository;
        $this->orderBuilder = $orderBuilder;
        $this->customerService = $customerService;
        $this->messageManager = $messageManager;
        $this->config = $config;
    }

    public function aroundSave(OrderAddressRepositoryInterface $subject, $proceed, OrderAddressInterface $orderAddress)
    {
        if($this->config->isEnabled()) {
            if ($orderAddress->getAddressType() === 'shipping') {
                $orderId = $orderAddress->getParentId();

                if (!empty($orderId)) {
                    $order = $this->orderRepository->get($orderId);

                    $extensionAttrs = $order->getExtensionAttributes();
                    $distriMediaIncrementId = $extensionAttrs->getDistriMediaIncrementId();

                    $distriMediaCustomer = $this->orderBuilder->getDistriMediaCustomerFromMagentoOrder($order, $orderAddress);

                    $this->customerService->changeCustomer($distriMediaCustomer, $distriMediaIncrementId);

                    return $proceed($orderAddress);
                }
            } else {
                throw new \Exception("Shipping address cannot be changed");
            }
        } else {
            return $proceed($orderAddress);
        }
    }
}

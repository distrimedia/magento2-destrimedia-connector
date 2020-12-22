<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Plugin;

use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository as MagentoOrderRepository;

class SetExtensionAttributesOnOrder
{
    const DISTRI_MEDIA_SYNC_STATUS = 'distri_media_sync_status';
    const DISTRI_MEDIA_SYNC_ATTEMPTS = 'distri_media_sync_attempts';
    const DISTRI_MEDIA_INCREMENT_ID = 'distri_media_increment_id';

    /**
     * @var OrderExtensionFactory
     */
    private $extensionFactory;

    /**
     * OrderRepository constructor.
     */
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory
    ) {
        $this->extensionFactory = $orderExtensionFactory;
    }

    /**
     * @param MagentoOrderRepository $subject
     * @return OrderInterface
     */
    public function afterGet(
        MagentoOrderRepository $subject,
        OrderInterface $order
    ) {
        /** @var OrderExtension $extensionAttributes */
        $extensionAttributes = $order->getExtensionAttributes() ?: $this->extensionFactory->create();

        $extensionAttributes->setDistriMediaSyncStatus($order->getData(self::DISTRI_MEDIA_SYNC_STATUS));
        $extensionAttributes->setDistriMediaSyncAttempts($order->getData(self::DISTRI_MEDIA_SYNC_ATTEMPTS));
        $extensionAttributes->setDistriMediaIncrementId($order->getData(self::DISTRI_MEDIA_INCREMENT_ID));

        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }

    public function beforeSave(
        MagentoOrderRepository $subject,
        OrderInterface $order
    ) {
        $extensionAttributes = $order->getExtensionAttributes() ?: $this->extensionFactory->create();
        if ($extensionAttributes !== null) {
            if ($extensionAttributes->getDistriMediaSyncStatus() !== null) {
                $order->setDistriMediaSyncStatus($extensionAttributes->getDistriMediaSyncStatus());
            }
            if ($extensionAttributes->getDistriMediaSyncAttempts() !== null) {
                $order->setDistriMediaSyncAttempts($extensionAttributes->getDistriMediaSyncAttempts());
            }
            if ($extensionAttributes->getDistriMediaIncrementId() !== null) {
                $order->setDistriMediaIncrementId($extensionAttributes->getDistriMediaIncrementId());
            }
        }

        return [$order];
    }
}

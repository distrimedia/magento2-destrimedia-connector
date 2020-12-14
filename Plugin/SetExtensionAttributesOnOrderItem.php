<?php

namespace DistriMedia\Connector\Plugin;

use DistriMedia\Connector\Model\ConfigInterface;
use Magento\Sales\Api\Data\OrderItemExtension;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\ItemRepository as MagentoOrderItemRepository;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Product;

class SetExtensionAttributesOnOrderItem
{
    const DISTRI_MEDIA_EAN_CODE = 'distri_media_ean_code';
    const DISTRI_MEDIA_EXTERNAL_REF = 'distri_media_external_ref';

    /**
     * @var OrderItemExtensionFactory
     */
    private $extensionFactory;

    private $config;

    /**
     * OrderRepository constructor.
     * @param OrderItemExtensionFactory $orderExtensionFactory
     */
    public function __construct(
        OrderItemExtensionFactory $orderExtensionFactory,
        ConfigInterface $config,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->config = $config;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->extensionFactory = $orderExtensionFactory;
    }

    /**
     * @param MagentoOrderItemRepository $subject
     * @param OrderItemInterface $orderItem
     * @return OrderItemInterface
     */
    public function afterGet(
        MagentoOrderItemRepository $subject,
        OrderItemInterface $orderItem
    ) {

        /** @var OrderItemExtension $extensionAttributes */
        $extensionAttributes = $orderItem->getExtensionAttributes() ?: $this->extensionFactory->create();

        $extensionAttributes->setDistriMediaEanCode($orderItem->getData(self::DISTRI_MEDIA_EAN_CODE));
        $extensionAttributes->setDistriMediaExternalRef($orderItem->getData(self::DISTRI_MEDIA_EXTERNAL_REF));

        $orderItem->setExtensionAttributes($extensionAttributes);

        return $orderItem;
    }

    public function beforeSave(
        MagentoOrderItemRepository $subject,
        OrderItemInterface $orderItem
    ) {
        $extensionAttributes = $orderItem->getExtensionAttributes() ?: $this->extensionFactory->create();
        if ($extensionAttributes !== null) {
            if ($extensionAttributes->getDistriMediaEanCode() !== null && $extensionAttributes->getDistriMediaExternalRef() !== null) {
                $orderItem->setDistriMediaEanCode($extensionAttributes->getDistriMediaEanCode());
                $orderItem->setDistriMediaExternalRef($extensionAttributes->getDistriMediaExternalRef());

            } else {
                $eanCode = $this->config->getEanCodeAttributeCode();
                $externalRef = $this->config->getExternalRefAttributeCode();

                $collection = $this->productCollectionFactory->create()
                    ->addAttributeToSelect($eanCode)
                    ->addAttributeToSelect(Product::SKU)
                    ->addAttributeToFilter(Product::SKU, $orderItem->getSku());

                if ($externalRef !== Product::SKU) {
                    $collection->addAttributeToSelect($externalRef);
                }

                $product = $collection->getFirstItem();

                if ($product !== NULL) {
                    $orderItem->setDistriMediaEanCode($product->getData($eanCode));
                    $orderItem->setDistriMediaExternalRef($product->getData($externalRef));
                }
            }
        }
        return [$orderItem];
    }

    public function afterGetList(
        MagentoOrderItemRepository $subject,
        \Magento\Sales\Model\ResourceModel\Order\Item\Collection $orderItems
    ) : \Magento\Sales\Model\ResourceModel\Order\Item\Collection
    {
        $products = [];
        /* @var \Magento\Sales\Model\ResourceModel\Order\Item $entity */
        foreach ($orderItems as $entity) {
            $extensionAttributes = $entity->getExtensionAttributes();
            $extensionAttributes->setDistriMediaEanCode($entity->getData(self::DISTRI_MEDIA_EAN_CODE));
            $extensionAttributes->setDistriMediaExternalRef($entity->getData(self::DISTRI_MEDIA_EXTERNAL_REF));
            $entity->setExtensionAttributes($extensionAttributes);

            $products[] = $entity;
        }
        return $orderItems;
    }
}

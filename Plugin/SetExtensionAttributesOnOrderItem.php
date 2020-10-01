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

        $orderItem->setExtensionAttributes($extensionAttributes);

        return $orderItem;
    }

    public function beforeSave(
        MagentoOrderItemRepository $subject,
        OrderItemInterface $orderItem
    ) {
        $extensionAttributes = $orderItem->getExtensionAttributes() ?: $this->extensionFactory->create();
        if ($extensionAttributes !== null) {
            if ($extensionAttributes->getDistriMediaEanCode() !== null) {
                $orderItem->setDistriMediaEanCode($extensionAttributes->getDistriMediaEanCode());
            } else {
                $eanCode = $this->config->getEanCodeAttributeCode();

                $product = $this->productCollectionFactory->create()
                    ->addAttributeToSelect($eanCode)
                    ->addAttributeToSelect(Product::SKU)
                    ->addAttributeToFilter(Product::SKU, $orderItem->getSku())
                    ->getFirstItem();

                if ($product !== NULL) {
                    $orderItem->setDistriMediaEanCode($product->getData($eanCode));
                }
            }
        }
        return [$orderItem];
    }
}

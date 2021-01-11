<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Plugin;

use DistriMedia\Connector\Model\ConfigInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Sales\Api\Data\OrderItemExtension;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\ItemRepository as MagentoOrderItemRepository;
use Magento\Sales\Model\ResourceModel\Order\Item;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;

class SetExtensionAttributesOnOrderItem
{
    const DISTRI_MEDIA_EAN_CODE = 'distri_media_ean_code';
    const DISTRI_MEDIA_EXTERNAL_REF = 'distri_media_external_ref';
    const DISTRI_MEDIA_HS_CODE = 'distri_media_hs_code';
    const DISTRI_MEDIA_COUNTRY_ORIGIN = 'distri_media_country_origin';

    /**
     * @var OrderItemExtensionFactory
     */
    private $extensionFactory;
    private $config;
    private $productCollectionFactory;

    /**
     * OrderRepository constructor.
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
        $extensionAttributes->setDistriMediaHsCode($orderItem->getData(self::DISTRI_MEDIA_HS_CODE));
        $extensionAttributes->setDistriMediaCountryOrigin($orderItem->getData(self::DISTRI_MEDIA_COUNTRY_ORIGIN));

        $orderItem->setExtensionAttributes($extensionAttributes);

        return $orderItem;
    }

    public function beforeSave(
        MagentoOrderItemRepository $subject,
        OrderItemInterface $orderItem
    ) {
        $extensionAttributes = $orderItem->getExtensionAttributes() ?: $this->extensionFactory->create();
        if ($extensionAttributes !== null) {
            if ($extensionAttributes->getDistriMediaEanCode() !== null &&
                $extensionAttributes->getDistriMediaExternalRef() !== null &&
                $extensionAttributes->getDistriMediaHsCode() !== null &&
                $extensionAttributes->getDistriMediaCountryOrigin() !== null) {
                $orderItem->setDistriMediaEanCode($extensionAttributes->getDistriMediaEanCode());
                $orderItem->setDistriMediaExternalRef($extensionAttributes->getDistriMediaExternalRef());
                $orderItem->setDistriMediaHsCode($extensionAttributes->getDistriMediaHsCode());
                $orderItem->setDistriMediaCountryOrigin($extensionAttributes->getDistriMediaCountryOrigin());
            } else {
                $eanCode = $this->config->getEanCodeAttributeCode();
                $externalRef = $this->config->getExternalRefAttributeCode();
                $hsCode = $this->config->getHSCodeAttribute();
                $countryOrigin = $this->config->getCountryOriginAttribute();

                $collection = $this->productCollectionFactory->create()
                    ->addAttributeToSelect($eanCode)
                    ->addAttributeToSelect($hsCode)
                    ->addAttributeToSelect($countryOrigin)
                    ->addAttributeToSelect(Product::SKU)
                    ->addAttributeToFilter(Product::SKU, $orderItem->getSku());

                if ($externalRef !== Product::SKU) {
                    $collection->addAttributeToSelect($externalRef);
                }

                $product = $collection->getFirstItem();

                if ($product !== null) {
                    $orderItem->setDistriMediaEanCode($product->getData($eanCode));
                    $orderItem->setDistriMediaExternalRef($product->getData($externalRef));
                    $orderItem->setDistriMediaHsCode($product->getData($hsCode));
                    $orderItem->setDistriMediaCountryOrigin($product->getData($countryOrigin));
                }
            }
        }

        return [$orderItem];
    }

    public function afterGetList(
        MagentoOrderItemRepository $subject,
        Collection $orderItems
    ): Collection {
        $products = [];
        /* @var Item $entity */
        foreach ($orderItems as $entity) {
            $extensionAttributes = $entity->getExtensionAttributes();
            $extensionAttributes->setDistriMediaEanCode($entity->getData(self::DISTRI_MEDIA_EAN_CODE));
            $extensionAttributes->setDistriMediaExternalRef($entity->getData(self::DISTRI_MEDIA_EXTERNAL_REF));
            $extensionAttributes->setDistriMediaHsCode($entity->getData(self::DISTRI_MEDIA_HS_CODE));
            $extensionAttributes->setDistriMediaCountryOrigin($entity->getData(self::DISTRI_MEDIA_COUNTRY_ORIGIN));

            $entity->setExtensionAttributes($extensionAttributes);

            $products[] = $entity;
        }

        return $orderItems;
    }
}

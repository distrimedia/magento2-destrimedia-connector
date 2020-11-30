<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Api\Data;

interface ShippedItemInterface
{
    const DATE_SHIPPED = 'DateShipped';
    const PRODUCT = 'Product';

    /**
     * @return string
     */
    public function getDateShipped();

    /**
     * @param $dateShipped
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setDateShipped($dateShipped);

    /**
     * @return \DistriMedia\Connector\Api\Data\ProductInterface[]
     */
    public function getProduct();

    /**
     * @param \DistriMedia\Connector\Api\Data\ProductInterface[] $product
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setProduct($product);
}

<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model\Data;

use DistriMedia\Connector\Api\Data\ShippedItemInterface;
use Magento\Framework\Model\AbstractModel;

class ShippedItem extends AbstractModel implements ShippedItemInterface
{
    /**
     * @inheritDoc
     */
    public function getDateShipped()
    {
       return $this->getData(self::DATE_SHIPPED);
    }

    /**
     * @inheritDoc
     */
    public function setDateShipped($dateShipped)
    {
        return $this->setData(self::DATE_SHIPPED, $dateShipped);
    }

    /**
     * @inheritDoc
     */
    public function getProduct()
    {
        return $this->getData(self::PRODUCT);
    }

    /**
     * @inheritDoc
     */
    public function setProduct($product)
    {
        return $this->setData(self::PRODUCT, $product);
    }
}

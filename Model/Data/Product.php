<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model\Data;

use DistriMedia\Connector\Api\Data\ProductInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class TrackId
 * @package DistriMedia\Connector\Model\Data
 */
class Product extends AbstractModel implements ProductInterface
{
    /**
     * @inheritDoc
     */
    public function getEAN()
    {
       return $this->getData(self::EAN);
    }

    /**
     * @inheritDoc
     */
    public function setEAN($ean)
    {
        return $this->setData(self::EAN, $ean);
    }

    /**
     * @inheritDoc
     */
    public function getPieces()
    {
        return $this->getData(self::PIECES);
    }

    /**
     * @inheritDoc
     */
    public function setPieces($pieces)
    {
        return $this->setData(self::PIECES, $pieces);
    }

    /**
     * @inheritDoc
     */
    public function getExternalRef()
    {
        return $this->getData(self::EXTERNAL_REF);
    }

    /**
     * @inheritDoc
     */
    public function setExternalRef($externalRef)
    {
        return $this->setData(self::EXTERNAL_REF, $externalRef);
    }

    /**
     * @inheritDoc
     */
    public function getExtRef()
    {
        return $this->getData(self::EXT_REF);
    }

    /**
     * @inheritDoc
     */
    public function setExtRef($externalRef)
    {
        return $this->setData(self::EXT_REF, $externalRef);
    }

    /**
     * @inheritDoc
     */
    public function getDescription1()
    {
        return $this->getData(self::DESCRIPION_1);
    }

    /**
     * @inheritDoc
     */
    public function setDescription1($description)
    {
        return $this->setData(self::DESCRIPION_1, $description);
    }

    /**
     * @inheritDoc
     */
    public function getDescription2()
    {
        return $this->getData(self::DESCRIPION_2);
    }

    /**
     * @inheritDoc
     */
    public function setDescription2($description)
    {
        return $this->setData(self::DESCRIPION_2, $description);
    }

    /**
     * @inheritDoc
     */
    public function getDescription3()
    {
        return $this->getData(self::DESCRIPION_3);
    }

    /**
     * @inheritDoc
     */
    public function setDescription3($description)
    {
        return $this->setData(self::DESCRIPION_3, $description);
    }
}

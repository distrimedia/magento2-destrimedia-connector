<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model\Data;

use DistriMedia\Connector\Api\Data\DistriMedia;
use DistriMedia\Connector\Api\Data\OrderlineInterface;
use Magento\Framework\Model\AbstractModel;

class Orderline extends AbstractModel implements OrderlineInterface
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
    public function getDescription1()
    {
        return $this->getData(self::DESCRIPTION1);
    }

    /**
     * @inheritDoc
     */
    public function setDescription1($description1)
    {
        return $this->setData(self::DESCRIPTION1, $description1);
    }

    /**
     * @inheritDoc
     */
    public function getDueDate()
    {
        return $this->getData(self::DESCRIPTION1);
    }

    /**
     * @inheritDoc
     */
    public function setDueDate($dueDate)
    {
        return $this->setData(self::DUE_DATE, $dueDate);
    }
}

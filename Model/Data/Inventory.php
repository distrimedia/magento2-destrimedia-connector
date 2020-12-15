<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model\Data;

use DistriMedia\Connector\Api\Data\InventoryInterface;
use Magento\Framework\Model\AbstractModel;

class Inventory extends AbstractModel implements InventoryInterface
{
    /**
     * @inheridoc
     */
    public function getProduct()
    {
        return $this->getData(self::PRODUCT);
    }

    /**
     * @inheridoc
     */
    public function setProduct($product)
    {
        return $this->setData(self::PRODUCT, $product);
    }

    /**
     * @inheridoc
     */
    public function getEAN()
    {
        return $this->getData(self::EAN);
    }

    /**
     * @inheridoc
     */
    public function setEAN($ean)
    {
        return $this->setData(self::EAN, $ean);
    }

    /**
     * @inheridoc
     */
    public function getPieces()
    {
        return $this->getData(self::PIECES);
    }

    /**
     * @inheridoc
     */
    public function setPieces($pieces)
    {
        return $this->setData(self::PIECES, $pieces);
    }

    /**
     * @inheridoc
     */
    public function getExtRef()
    {
        return $this->getData(self::EXT_REF);
    }

    /**
     * @inheridoc
     */
    public function setExtRef($extRef)
    {
        return $this->setData(self::EXT_REF, $extRef);
    }

    /**
     * @inheridoc
     */
    public function getExternalRef()
    {
        return $this->getData(self::EXT_REF);
    }

    /**
     * @inheridoc
     */
    public function setExternalRef($extRef)
    {
        return $this->setData(self::EXT_REF, $extRef);
    }

    /**
     * @inheridoc
     */
    public function getClaimable()
    {
        return $this->getData(self::CLAIMABLE);
    }

    /**
     * @inheridoc
     */
    public function setClaimable($claimable)
    {
        return $this->setData(self::CLAIMABLE, $claimable);
    }

    /**
     * @inheridoc
     */
    public function getClaimed()
    {
        return $this->getData(self::CLAIMED);
    }

    /**
     * @inheridoc
     */
    public function setClaimed($claimed)
    {
        return $this->setData(self::CLAIMED, $claimed);
    }

    /**
     * @inheridoc
     */
    public function getProblem()
    {
        return $this->getData(self::PROBLEM);
    }

    /**
     * @inheridoc
     */
    public function setProblem($problem)
    {
        return $this->setData(self::PROBLEM, $problem);
    }

    /**
     * @inheridoc
     */
    public function getOverdue()
    {
        return $this->getData(self::OVERDUE);
    }

    /**
     * @inheridoc
     */
    public function setOverdue($overdue)
    {
        return $this->setData(self::OVERDUE, $overdue);
    }

    /**
     * @inheridoc
     */
    public function getBlocked()
    {
        return $this->getData(self::BLOCKED);
    }

    /**
     * @inheridoc
     */
    public function setBlocked($blocked)
    {
        return $this->setData(self::BLOCKED, $blocked);
    }

    /**
     * @inheridoc
     */
    public function getDLB()
    {
        return $this->getData(self::DLB);
    }

    /**
     * @inheridoc
     */
    public function setDLB($dlb)
    {
        return $this->setData(self::DLB, $dlb);
    }

    /**
     * @inheridoc
     */
    public function toDataArray()
    {
        return [
            self::EAN => $this->getEAN(),
            self::EXT_REF => $this->getExtRef(),
            self::PIECES => $this->getPieces(),
            self::CLAIMED => $this->getClaimed(),
            self::CLAIMABLE => $this->getClaimable(),
            self::PROBLEM => $this->getProblem(),
            self::BLOCKED => $this->getBlocked(),
            self::OVERDUE => $this->getOverdue(),
            self::EXTERNAL_REF => $this->getExternalRef(),
            self::PRODUCT => $this->getProduct(),
        ];
    }
}

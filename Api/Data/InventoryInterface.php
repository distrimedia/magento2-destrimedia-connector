<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Api\Data;

interface InventoryInterface
{
    const PRODUCT = 'Product';
    const EAN = 'EAN';
    const PIECES = 'Pieces';
    const EXT_REF = 'ExtRef';
    const EXTERNAL_REF = 'ExternalRef';
    const CLAIMABLE = 'Claimable';
    const CLAIMED = 'Claimed';
    const PROBLEM = 'Problem';
    const OVERDUE = 'Overdue';
    const BLOCKED = 'Blocked';
    const DLB = 'DLB';

    /**
     * @return string
     */
    public function getProduct();

    /**
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setProduct($product);

    /**
     * @return string
     */
    public function getEAN();

    /**
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setEAN($ean);

    /**
     * @return string
     */
    public function getPieces();

    /**
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setPieces($pieces);

    /**
     * @return mixed
     */
    public function getExtRef();

    /**
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setExtRef($extRef);

    /**
     * @return mixed
     */
    public function getExternalRef();

    /**
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setExternalRef($extRef);

    /**
     * @return mixed
     */
    public function getClaimable();

    /**
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setClaimable($claimable);

    /**
     * @return mixed
     */
    public function getClaimed();

    /**
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setClaimed($claimed);

    /**
     * @return mixed
     */
    public function getProblem();

    /**
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setProblem($problem);

    /**
     * @return mixed
     */
    public function getOverdue();

    /**
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setOverdue($overdue);

    /**
     * @return mixed
     */
    public function getBlocked();

    /**
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setBlocked($blocked);

    /**
     * @return array
     */
    public function getDLB();

    /**
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setDLB($dlb);

    /**
     * @return array
     */
    public function toDataArray();
}

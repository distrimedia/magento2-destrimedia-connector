<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Api\Data;

interface InventoryInterface
{
    const PRODUCT = 'Product';
    const EAN = 'EAN';
    const PIECES = 'Pieces';
    const EXT_REF = 'ExtRef';
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
     * @param $product
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setProduct($product);

    /**
     * @return string
     */
    public function getEAN();

    /**
     * @param $ean
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setEAN($ean);

    /**
     * @return string
     */
    public function getPieces();

    /**
     * @param $pieces
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setPieces($pieces);

    /**
     * @return mixed
     */
    public function getExtRef();

    /**
     * @param $extRef
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setExtRef($extRef);

    /**
     * @return mixed
     */
    public function getClaimable();

    /**
     * @param $claimable
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setClaimable($claimable);

    /**
     * @return mixed
     */
    public function getClaimed();

    /**
     * @param $claimed
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setClaimed($claimed);

    /**
     * @return mixed
     */
    public function getProblem();

    /**
     * @param $problem
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setProblem($problem);

    /**
     * @return mixed
     */
    public function getOverdue();

    /**
     * @param $overdue
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setOverdue($overdue);

    /**
     * @return mixed
     */
    public function getBlocked();

    /**
     * @param $blocked
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setBlocked($blocked);

    /**
     * @return array
     */
    public function getDLB();

    /**
     * @param $dlb
     * @return \DistriMedia\Connector\Api\Data\InventoryInterface
     */
    public function setDLB($dlb);

    /**
     * @return array
     */
    public function toDataArray();
}

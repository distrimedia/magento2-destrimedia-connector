<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Api\Data;

interface OrderlineInterface
{
    const EAN = 'EAN';
    const PIECES = 'Pieces';
    const EXTERNAL_REF = 'ExternalRef';
    const DESCRIPTION1 = 'Description1';
    const DUE_DATE = 'DueDate';

    /**
     * @return string
     */
    public function getEAN();

    /**
     * @param $ean
     * @return DistriMedia\Connector\Api\Data\OrderlineInterface
     */
    public function setEAN($ean);

    /**
     * @return string
     */
    public function getPieces();

    /**
     * @param $pieces
     * @return DistriMedia\Connector\Api\Data\OrderlineInterface
     */
    public function setPieces($pieces);

    /**
     * @return string
     */
    public function getExternalRef();

    /**
     * @param $xternalRef
     * @return DistriMedia\Connector\Api\Data\OrderlineInterface
     */
    public function setExternalRef($xternalRef);

    /**
     * @return string
     */
    public function getDescription1();

    /**
     * @param $description1
     * @return DistriMedia\Connector\Api\Data\OrderlineInterface
     */
    public function setDescription1($description1);

    /**
     * @return string
     */
    public function getDueDate();

    /**
     * @param $dueDate
     * @return DistriMedia\Connector\Api\Data\OrderlineInterface
     */
    public function setDueDate($dueDate);
}

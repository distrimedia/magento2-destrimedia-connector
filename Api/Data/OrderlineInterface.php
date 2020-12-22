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
     * @return \DistriMedia\Connector\Api\Data\OrderlineInterface
     */
    public function setEAN($ean);

    /**
     * @return string
     */
    public function getPieces();

    /**
     * @return \DistriMedia\Connector\Api\Data\OrderlineInterface
     */
    public function setPieces($pieces);

    /**
     * @return string
     */
    public function getExternalRef();

    /**
     * @return \DistriMedia\Connector\Api\Data\OrderlineInterface
     */
    public function setExternalRef($externalRef);

    /**
     * @return string
     */
    public function getDescription1();

    /**
     * @return \DistriMedia\Connector\Api\Data\OrderlineInterface
     */
    public function setDescription1($description1);

    /**
     * @return string
     */
    public function getDueDate();

    /**
     * @return \DistriMedia\Connector\Api\Data\OrderlineInterface
     */
    public function setDueDate($dueDate);
}

<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Api\Data;

interface ProductInterface
{
    const EAN = 'EAN';
    const PIECES = 'Pieces';
    const EXTERNAL_REF = 'ExternalRef';
    const EXT_REF = 'ExtRef';
    const DESCRIPION_1 = 'Description1';
    const DESCRIPION_2 = 'Description2';
    const DESCRIPION_3 = 'Description3';

    /**
     * @return string
     */
    public function getEAN();

    /**
     * @return \DistriMedia\Connector\Api\Data\ProductInterface
     */
    public function setEAN($ean);

    /**
     * @return string
     */
    public function getPieces();

    /**
     * @return \DistriMedia\Connector\Api\Data\ProductInterface
     */
    public function setPieces($pieces);

    /**
     * @return string
     */
    public function getExternalRef();

    /**
     * @return \DistriMedia\Connector\Api\Data\ProductInterface
     */
    public function setExternalRef($externalRef);

    /**
     * @return string
     */
    public function getExtRef();

    /**
     * @return \DistriMedia\Connector\Api\Data\ProductInterface
     */
    public function setExtRef($externalRef);

    /**
     * @return string
     */
    public function getDescription1();

    /**
     * @return \DistriMedia\Connector\Api\Data\ProductInterface
     */
    public function setDescription1($description);

    /**
     * @return string
     */
    public function getDescription2();

    /**
     * @return \DistriMedia\Connector\Api\Data\ProductInterface
     */
    public function setDescription2($description);

    /**
     * @return string
     */
    public function getDescription3();

    /**
     * @return \DistriMedia\Connector\Api\Data\ProductInterface
     */
    public function setDescription3($description);
}

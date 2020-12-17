<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model\Data;

use DistriMedia\Connector\Api\Data\TrackIdInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class TrackId
 */
class TrackId extends AbstractModel implements TrackIdInterface
{
    /**
     * {@inheritDoc}
     */
    public function getNumberColli()
    {
        return $this->getData(self::NUMBER_COLLI);
    }

    /**
     * {@inheritDoc}
     */
    public function setNumberColli($numberColli)
    {
        return $this->setData(self::NUMBER_COLLI, $numberColli);
    }

    /**
     * {@inheritDoc}
     */
    public function getCarrier()
    {
        return $this->getData(self::CARRIER);
    }

    /**
     * {@inheritDoc}
     */
    public function setCarrier($carrier)
    {
        return $this->setData(self::CARRIER, $carrier);
    }

    /**
     * {@inheritDoc}
     */
    public function getExecutingCarrier()
    {
        return $this->getData(self::EXECUTING_CARRIER);
    }

    /**
     * {@inheritDoc}
     */
    public function setExecutingCarrier($executingCarrier)
    {
        return $this->setData(self::EXECUTING_CARRIER, $executingCarrier);
    }

    /**
     * {@inheritDoc}
     */
    public function getWeight()
    {
        return $this->getData(self::WEIGHT);
    }

    /**
     * {@inheritDoc}
     */
    public function setWeight($weight)
    {
        return $this->setData(self::WEIGHT, $weight);
    }

    /**
     * {@inheritDoc}
     */
    public function getAWB()
    {
        return $this->getData(self::AWB);
    }

    /**
     * {@inheritDoc}
     */
    public function setAWB($awb)
    {
        return $this->setData(self::AWB, $awb);
    }

    /**
     * {@inheritDoc}
     */
    public function getTrackID()
    {
        return $this->getData(self::TRACK_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setTrackID($trackID)
    {
        return $this->setData(self::TRACK_ID, $trackID);
    }

    /**
     * {@inheritDoc}
     */
    public function getBoxType()
    {
        return $this->getData(self::BOX_TYPE);
    }

    /**
     * {@inheritDoc}
     */
    public function setBoxType($boxType)
    {
        return $this->setData(self::BOX_TYPE, $boxType);
    }

    /**
     * {@inheritDoc}
     */
    public function getReference()
    {
        return $this->getData(self::REFERENCE);
    }

    /**
     * {@inheritDoc}
     */
    public function setReference($reference)
    {
        return $this->setData(self::REFERENCE, $reference);
    }

    /**
     * {@inheritDoc}
     */
    public function getShippedDate()
    {
        return $this->getData(self::SHIPPED_DATE);
    }

    /**
     * {@inheritDoc}
     */
    public function setShippedDate($shippedDate)
    {
        return $this->setData(self::SHIPPED_DATE, $shippedDate);
    }

    /**
     * {@inheritDoc}
     */
    public function getTrackAndTraceURL()
    {
        return $this->getData(self::TRACK_AND_TRACE_URL);
    }

    /**
     * {@inheritDoc}
     */
    public function setTrackAndTraceURL($trackAndTrace)
    {
        return $this->setData(self::TRACK_AND_TRACE_URL, $trackAndTrace);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderline()
    {
        return $this->getData(self::ORDERLINE);
    }

    /**
     * {@inheritDoc}
     */
    public function setOrderline($orderline)
    {
        return $this->setData(self::ORDERLINE, $orderline);
    }

    /**
     * {@inheritDoc}
     */
    public function getPackage()
    {
        return $this->getData(self::PACKAGE);
    }

    /**
     * {@inheritDoc}
     */
    public function setPackage($package)
    {
        return $this->setData(self::PACKAGE, $package);
    }
}

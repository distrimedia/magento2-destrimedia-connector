<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model\Data;

use DistriMedia\Connector\Api\Data\PackageInterface;
use Magento\Framework\Model\AbstractModel;

class Package extends AbstractModel implements PackageInterface
{
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
    public function getBoxNumber()
    {
        return $this->getData(self::BOX_NUMBER);
    }

    /**
     * {@inheritDoc}
     */
    public function setBoxNumber($boxNumber)
    {
        return $this->setData(self::BOX_NUMBER, $boxNumber);
    }

    /**
     * {@inheritDoc}
     */
    public function getVolume()
    {
        return $this->getData(self::VOLUME);
    }

    /**
     * {@inheritDoc}
     */
    public function setVolume($volume)
    {
        return $this->setData(self::VOLUME, $volume);
    }

    /**
     * {@inheritDoc}
     */
    public function getHeight()
    {
        return $this->getData(self::HEIGHT);
    }

    /**
     * {@inheritDoc}
     */
    public function setHeight($height)
    {
        return $this->setData(self::HEIGHT, $height);
    }

    /**
     * {@inheritDoc}
     */
    public function getWidth()
    {
        return $this->getData(self::WIDTH);
    }

    /**
     * {@inheritDoc}
     */
    public function setWidth($width)
    {
        return $this->setData(self::WIDTH, $width);
    }

    /**
     * {@inheritDoc}
     */
    public function getLength()
    {
        return $this->getData(self::LENGTH);
    }

    /**
     * {@inheritDoc}
     */
    public function setLength($length)
    {
        return $this->setData(self::LENGTH, $length);
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * {@inheritDoc}
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
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
    public function setTrackAndTraceURL($trackAndTraceURL)
    {
        return $this->setData(self::TRACK_AND_TRACE_URL, $trackAndTraceURL);
    }
}

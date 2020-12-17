<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Api\Data;

interface PackageInterface
{
    const AWB = 'AWB';
    const TRACK_ID = 'TrackID';
    const BOX_TYPE = 'BoxType';
    const REFERENCE = 'Reference';
    const WEIGHT = 'Weight';
    const BOX_NUMBER = 'BoxNumber';
    const VOLUME = 'Volume';
    const HEIGHT = 'Height';
    const WIDTH = 'Width';
    const LENGTH = 'Length';
    const DESCRIPTION = 'Description';
    const TRACK_AND_TRACE_URL = 'TrackAndTraceURL';

    /**
     * @return string
     */
    public function getAWB();

    /**
     * @return \DistriMedia\Connector\Api\Data\TrackIdInterface
     */
    public function setAWB($awb);

    /**
     * @return string
     */
    public function getTrackID();

    /**
     * @return \DistriMedia\Connector\Api\Data\TrackIdInterface
     */
    public function setTrackID($trackID);

    /**
     * @return string
     */
    public function getBoxType();

    /**
     * @return \DistriMedia\Connector\Api\Data\TrackIdInterface
     */
    public function setBoxType($boxType);

    /**
     * @return string
     */
    public function getReference();

    /**
     * @return \DistriMedia\Connector\Api\Data\TrackIdInterface
     */
    public function setReference($reference);

    /**
     * @return string
     */
    public function getWeight();

    /**
     * @return \DistriMedia\Connector\Api\Data\TrackIdInterface
     */
    public function setWeight($weight);

    /**
     * @return string
     */
    public function getBoxNumber();

    /**
     * @return \DistriMedia\Connector\Api\Data\TrackIdInterface
     */
    public function setBoxNumber($boxNumber);

    /**
     * @return string
     */
    public function getVolume();

    /**
     * @return \DistriMedia\Connector\Api\Data\TrackIdInterface
     */
    public function setVolume($volume);

    /**
     * @return string
     */
    public function getHeight();

    /**
     * @return \DistriMedia\Connector\Api\Data\TrackIdInterface
     */
    public function setHeight($height);

    /**
     * @return string
     */
    public function getWidth();

    /**
     * @return \DistriMedia\Connector\Api\Data\TrackIdInterface
     */
    public function setWidth($width);

    /**
     * @return string
     */
    public function getLength();

    /**
     * @return \DistriMedia\Connector\Api\Data\TrackIdInterface
     */
    public function setLength($length);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return \DistriMedia\Connector\Api\Data\TrackIdInterface
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getTrackAndTraceURL();

    /**
     * @return \DistriMedia\Connector\Api\Data\TrackIdInterface
     */
    public function setTrackAndTraceURL($trackAndTraceURL);
}

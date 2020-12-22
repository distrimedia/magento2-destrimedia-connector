<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Ui\Component\Listing\Column\SyncStatus;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    const SYNC_STATUS_NOT_SYNCED = 0;
    const SYNC_STATUS_SYNCED = 1;
    const SYNC_STATUS_RETRY = 2;
    const SYNC_STATUS_FAILED = 3;
    const SYNC_STATUS_PENDING_CANCELED = 4;

    const SYNC_STATUS_RCV = 5;
    const SYNC_STATUS_PCK = 6;
    const SYNC_STATUS_SCN = 7;
    const SYNC_STATUS_RDY = 8;
    const SYNC_STATUS_LBL = 9;
    const SYNC_STATUS_SHP = 10;
    const SYNC_STATUS_PSH = 11;
    const SYNC_STATUS_CNL = 12;

    const STATUS_RECEIVED_IN_SYSTEM = 'RCV';
    const STATUS_READY_FOR_PICKING = 'PCK';
    const STATUS_IS_BEING_PROCESSED = 'SCN';
    const STATUS_PACKED_AND_WAITED_FOR_SHIPMENT_LABEL = 'RDY';
    const STATUS_PACKED_AND_LABELED_WAITING_FOR_SHIPMENT = 'LBL';
    const STATUS_SHIPPED = 'SHP';
    const STATUS_PARTLY_SHIPPED = 'PSH';
    const STATUS_CANCELLED = 'CNL';

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::SYNC_STATUS_NOT_SYNCED, 'label' => __('Not Synced')],
            ['value' => self::SYNC_STATUS_SYNCED, 'label' => __('Synced')],
            ['value' => self::SYNC_STATUS_RETRY, 'label' => __('Retrying')],
            ['value' => self::SYNC_STATUS_FAILED, 'label' => __('Failed')],
            ['value' => self::SYNC_STATUS_PENDING_CANCELED, 'label' => __('Pending Cancel')],
            ['value' => self::SYNC_STATUS_RCV, 'label' => __('Received in system')],
            ['value' => self::SYNC_STATUS_PCK, 'label' => __('Ready for picking')],
            ['value' => self::SYNC_STATUS_SCN, 'label' => __('Order is being processed / picked')],
            ['value' => self::SYNC_STATUS_RDY, 'label' => __('Packed, waiting for shipment label')],
            ['value' => self::SYNC_STATUS_LBL, 'label' => __('Packed and labeled, waiting for shipment')],
            ['value' => self::SYNC_STATUS_SHP, 'label' => __('Shipped')],
            ['value' => self::SYNC_STATUS_PSH, 'label' => __('Partly Shipped')],
            ['value' => self::SYNC_STATUS_CNL, 'label' => __('Canceled')],
        ];
    }

    /**
     * Maps the Distrimedia id with the internal status
     * @return array
     */
    public static function getDistriMediaStatusses()
    {
        return [
            self::STATUS_RECEIVED_IN_SYSTEM => self::SYNC_STATUS_RCV,
            self::STATUS_READY_FOR_PICKING => self::SYNC_STATUS_PCK,
            self::STATUS_IS_BEING_PROCESSED => self::SYNC_STATUS_SCN,
            self::STATUS_PACKED_AND_WAITED_FOR_SHIPMENT_LABEL => self::SYNC_STATUS_RDY,
            self::STATUS_PACKED_AND_LABELED_WAITING_FOR_SHIPMENT => self::SYNC_STATUS_LBL,
            self::STATUS_SHIPPED => self::SYNC_STATUS_SHP,
            self::STATUS_PARTLY_SHIPPED => self::SYNC_STATUS_PSH,
            self::STATUS_CANCELLED => self::SYNC_STATUS_CNL,
        ];
    }
}

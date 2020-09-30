<?php

namespace DistriMedia\Connector\Ui\Component\Listing\Column\SyncStatus;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    const SYNC_STATUS_NOT_SYNCED = 0;
    const SYNC_STATUS_SYNCED     = 1;
    const SYNC_STATUS_FAILED     = 2;
    const SYNC_STATUS_PENDING_CANCELED   = 3;

    const STATUS_RECEIVED_IN_SYSTEM = 'RCV';
    const STATUS_READY_FOR_PICKING = 'PCK';
    const STATUS_IS_BEING_PROCESSED = 'SCN';
    const STATUS_PACKED_AND_WAITED_FOR_SHIPMENT_LABEL = 'RDY';
    const STATUS_PACKED_AND_LABELED_WAITING_FOR_SHIPMENT = 'LBL';
    const STATUS_SHIPPED  = 'SHP';
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
            ['value' => self::SYNC_STATUS_FAILED,   'label' => __('Failed')],
            ['value' => self::SYNC_STATUS_PENDING_CANCELED,   'label' => __('Pending Cancel')],
            ['value' => self::STATUS_RECEIVED_IN_SYSTEM,   'label' => __('Received in system')],
            ['value' => self::STATUS_READY_FOR_PICKING,   'label' => __('Ready for picking')],
            ['value' => self::STATUS_PACKED_AND_WAITED_FOR_SHIPMENT_LABEL,   'label' => __('Packed, waiting for shipment label')],
            ['value' => self::STATUS_PACKED_AND_LABELED_WAITING_FOR_SHIPMENT,   'label' => __('Packed and labeled, waiting for shipment')],
            ['value' => self::STATUS_SHIPPED,   'label' => __('Shipped')],
            ['value' => self::STATUS_SHIPPED,   'label' => __('Partly hipped')],
            ['value' => self::STATUS_CANCELLED,   'label' => __('Canceled')],
        ];
    }
}

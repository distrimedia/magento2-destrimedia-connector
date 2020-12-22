<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model\Flag;

class Status extends \Magento\Framework\Flag
{
    const STATUS_RUNNING = 'running';
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR   = 'error';
    const STATUS_PENDING = 'pending';

    /**
     * Flag code
     *
     * @var string
     */
    protected $_flagCode = 'distri_media_connector_stock_cron_status';
}

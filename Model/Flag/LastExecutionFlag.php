<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model\Flag;

class LastExecutionFlag extends \Magento\Framework\Flag
{
    /**
     * Flag code
     *
     * @var string
     */
    protected $_flagCode = 'distri_media_connector_stock_cron_last_execution';
}

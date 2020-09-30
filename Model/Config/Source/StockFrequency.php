<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model\Config\Source;

class StockFrequency
{
    /**
     * @var array
     */
    protected static $_options;

    const CRON_DAILY = 'D';

    const CRON_WEEKLY = 'W';

    const CRON_MONTHLY = 'M';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!self::$_options) {
            self::$_options = [
                ['label' => __('Daily'), 'value' => self::CRON_DAILY]
            ];
        }
        return self::$_options;
    }
}

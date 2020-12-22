<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model\Config\Source;

class SendInvoices
{
    /**
     * @var array
     */
    protected static $_options;

    const SEND_INVOICES_ALWAYS = '1';
    const SEND_INVOICES_NEVER  = '0';
    const SEND_INVOICES_ONLY_OUTSIDE_EU = '2';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!self::$_options) {
            self::$_options = [
                ['label' => __('No'), 'value' => self::SEND_INVOICES_NEVER],
                ['label' => __('Yes'), 'value' => self::SEND_INVOICES_ALWAYS],
                ['label' => __('Only for deliveries outside EU'), 'value' => self::SEND_INVOICES_ONLY_OUTSIDE_EU],
            ];
        }

        return self::$_options;
    }
}

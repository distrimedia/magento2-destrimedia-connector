<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Logger\Handler;

use Magento\Framework\Logger\Handler\Base as BaseHandler;

class ConnectorHandler extends BaseHandler
{
    /**
     * File name
     *
     * @var string
     */
    protected $fileName = '/var/log/distrimedia_connector.log';
}

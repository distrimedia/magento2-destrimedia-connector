<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Service;

use DistriMedia\Connector\Model\ConfigInterface;
use Psr\Log\LoggerInterface;
use DistriMedia\SoapClient\Service\AbstractSoapClient;

class AbstractSync
{
    protected $logger;
    protected $config;

    public function __construct(
        LoggerInterface $logger,
        ConfigInterface $config

    )
    {
        $this->logger = $logger;
        $this->config = $config;
    }
}

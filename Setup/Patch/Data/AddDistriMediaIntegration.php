<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Setup\Patch\Data;

use DistriMedia\Connector\Helper\TokenBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

class AddDistriMediaIntegration implements DataPatchInterface
{
    private $tokenBuilder;
    private $logger;

    public function __construct(
        TokenBuilder $tokenBuilder,
        LoggerInterface $logger
    ) {
        $this->tokenBuilder = $tokenBuilder;
        $this->logger = $logger;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        try {
            $this->tokenBuilder->createToken();
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
        }

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Setup\Patch\Data;

use DistriMedia\Connector\Helper\TokenBuilder;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class AddEanCodeAttribute
 */
class AddDistriMediaIntegration implements DataPatchInterface
{
    private $tokenBuilder;

    public function __construct(
        TokenBuilder $tokenBuilder
    ) {
        $this->tokenBuilder = $tokenBuilder;
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
        $this->tokenBuilder->createToken();
    }
}

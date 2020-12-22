<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

class AddEanCodeAttribute implements DataPatchInterface
{
    private $moduleDataSetup;
    private $eavSetupFactory;
    private $logger;

    /**
     * PatchInitial constructor.
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        try {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'dm_ean_code',
                [
                    'type' => 'varchar',
                    'label' => 'DistriMedia EAN code',
                    'input' => 'text',
                    'visible' => true,
                    'required' => false,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'group' => 'General',
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                ]
            );
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $this;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}

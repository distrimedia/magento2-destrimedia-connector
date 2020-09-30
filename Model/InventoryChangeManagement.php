<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model;

use DistriMedia\Connector\Api\Data\InventoryInterface;
use DistriMedia\Connector\Api\InventoryChangeManagementInterface;
use DistriMedia\Connector\Service\StockSyncInterface;
use DistriMedia\SoapClient\Struct\Response\Inventory\StockItem;
use Magento\Framework\Webapi\Rest\Request\Deserializer\Xml;

class InventoryChangeManagement implements InventoryChangeManagementInterface
{
    private $deserializer;
    private $stockSync;
    private $config;

    public function __construct(
        Xml $deserializer,
        StockSyncInterface $stockSync,
        ConfigInterface $config
    )
    {
        $this->deserializer = $deserializer;
        $this->stockSync = $stockSync;
        $this->config = $config;
    }

    public function execute(InventoryInterface $inventory)
    {
        if (!$this->config->isEnabled()) {
            throw new \Exception("DistriMedia Connector is not enabled");
        }

        $data = $inventory->toDataArray();
        $inventoryItem = new StockItem($data);
        $this->stockSync->processStock([$inventoryItem]);
    }
}

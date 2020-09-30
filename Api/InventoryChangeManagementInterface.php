<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Api;

use DistriMedia\Connector\Api\Data\InventoryInterface;

interface InventoryChangeManagementInterface
{
    /**
     * I expect an xml string in format of InventoryChange and will update the stock of the provided EAN code
     * @param $Inventory
     * @return mixed
     */
    public function execute(InventoryInterface $Inventory);
}

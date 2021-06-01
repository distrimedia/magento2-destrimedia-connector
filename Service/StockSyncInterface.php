<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Service;

use DistriMedia\SoapClient\Struct\Response\Inventory\StockItem;

/**
 * Interface StockSyncInterface
 */
interface StockSyncInterface
{
    /**
     * I fetch all the stock from the API and update it in Magento
     * Returns array of errors
     */
    public function fetchAllStock(): array;

    /**
     * I can process an array of stock items and save the values in magento using bulk actions.
     * if Magento's MSI system is not used, a slightly different approach will be executed.
     * Returns array of errors
     * @param StockItem[] $stockDatas
     */
    public function processStock(array $stockDatas): array;
}

<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Service;

use DistriMedia\SoapClient\Struct\Response\Inventory\StockItem;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface StockSyncInterface
 * @package DistriMedia\Connector\Service
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
     * @param StockItem[] $stockItems
     * @return array
     */
    public function processStock(array $stockItems): array;
}

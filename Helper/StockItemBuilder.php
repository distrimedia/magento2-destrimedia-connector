<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Helper;

use Magento\AsynchronousOperations\Model\MassSchedule;
use Magento\AsynchronousOperations\Model\MassScheduleFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\CatalogInventory\Model\Configuration;

class StockItemBuilder
{
    /**
     * @var StockItemInterfaceFactory
     */
    private $stockItemInterfaceFactory;

    /**
     * @var MassScheduleFactory
     */
    private $massScheduleFactory;

    private $sourceItemInterfaceFactory;

    private $scopeConfig;

    private $backordersEnabled;

    public function __construct(
        StockItemInterfaceFactory $stockItemInterfaceFactory,
        MassScheduleFactory $massScheduleFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->massScheduleFactory       = $massScheduleFactory;
        $this->stockItemInterfaceFactory = $stockItemInterfaceFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param string $sku
     * @param string $qty
     */
    public function createStockItemInterface(int $qty): StockItemInterface
    {
        $stockStatus = (int) ($qty > 0);

        if ($qty == 0 && $this->getBackordersEnabled()) {
            $stockStatus = Stock::STOCK_IN_STOCK;
        }
        /* @var StockItemInterface $stockItemInterface */
        $stockItemInterface = $this->stockItemInterfaceFactory->create();

        //if the qty is positive, we can save it in the stock
        $stockItemInterface->setQty($qty);
        $stockItemInterface->setUseConfigBackorders(true);
        $stockItemInterface->setUseConfigEnableQtyInc(true);
        $stockItemInterface->setUseConfigManageStock(true);
        $stockItemInterface->setUseConfigMaxSaleQty(true);
        $stockItemInterface->setUseConfigMinSaleQty(true);
        $stockItemInterface->setUseConfigNotifyStockQty(true);
        $stockItemInterface->setUseConfigQtyIncrements(true);
        $stockItemInterface->setStockStatusChangedAuto(1);
        $stockItemInterface->setUseConfigMinQty(true);
        $stockItemInterface->setIsInStock($stockStatus);

        return $stockItemInterface;
    }

    /**
     * There is no way to inject the msi module if we want to support use of both with our without msi
     * so we need to use the object manager
     * @param string|null $sourceCode
     * @return mixed
     */
    public function createSourceItemInterface(int $qty, string $sku, string $sourceCode = 'default')
    {
        $sourceItemInterfaceFactory = ObjectManager::getInstance()->create(
            'Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory'
        );

        $stockStatus = (int) ($qty > 0);

        if ($qty == 0 && $this->getBackordersEnabled()) {
            $stockStatus = Stock::STOCK_IN_STOCK;
        }

        $sourceItemInterface = $sourceItemInterfaceFactory->create();
        $sourceItemInterface->setStatus($stockStatus);
        $sourceItemInterface->setQuantity($qty);
        $sourceItemInterface->setSku($sku);
        $sourceItemInterface->setSourceCode($sourceCode);

        return $sourceItemInterface;
    }

    public function massScheduleDataToQueue(array $data, string $topic)
    {
        /* @var MassSchedule $massSchedule */
        $massSchedule = $this->massScheduleFactory->create();

        $massSchedule->publishMass(
            $topic,
            $data
        );
    }

    public function getBackordersEnabled()
    {
        if ($this->backordersEnabled !== null) {
            return $this->backordersEnabled;
        }

        $backorders = $this->scopeConfig->getValue(Configuration::XML_PATH_BACKORDERS);
        $backordersThreshold = $this->scopeConfig->getValue(Configuration::XML_PATH_MIN_QTY);
        $this->backordersEnabled = (
                $backorders == Stock::BACKORDERS_YES_NONOTIFY || $backorders == Stock::BACKORDERS_YES_NOTIFY
            ) && $backordersThreshold < 0;

        return $this->backordersEnabled;
    }
}

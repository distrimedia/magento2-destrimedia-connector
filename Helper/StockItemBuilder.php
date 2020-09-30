<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Helper;

use Magento\AsynchronousOperations\Model\MassSchedule;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\AsynchronousOperations\Model\MassScheduleFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;

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

    public function __construct(
        StockItemInterfaceFactory $stockItemInterfaceFactory,
        MassScheduleFactory $massScheduleFactory
    )
    {
        $this->massScheduleFactory       = $massScheduleFactory;
        $this->stockItemInterfaceFactory = $stockItemInterfaceFactory;
    }

    /**
     * @param string $sku
     * @param string $qty
     * @return StockItemInterface
     */
    public function createStockItemInterface(int $qty): StockItemInterface
    {
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
        $stockItemInterface->setIsInStock($qty > 0);

        return $stockItemInterface;
    }

    /**
     * There is no way to inject the msi module if we want to support use of both with our without msi, so we need to use the object manager
     * @param int $qty
     * @param string $sku
     * @param string|null $sourceCode
     * @return mixed
     */
    public function createSourceItemInterface(int $qty, string $sku, string $sourceCode = null) {
        $sourceItemInterface = ObjectManager::getInstance()->create('Magento\InventoryApi\Api\Data\SourceItemInterface');
        $sourceItemInterface->setStatus((int) $qty > 0);
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
}

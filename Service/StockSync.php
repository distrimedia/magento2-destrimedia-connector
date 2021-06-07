<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Service;

use DistriMedia\Connector\DistriMediaException;
use DistriMedia\Connector\Helper\StockItemBuilder;
use DistriMedia\Connector\Model\ConfigInterface;
use DistriMedia\SoapClient\Service\Inventory as DistriMediaInventoryService;
use Magento\AsynchronousOperations\Model\MassScheduleFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterfaceFactory;
use Magento\Framework\App\Cache;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Indexer\CacheContextFactory;
use Magento\Framework\Module\Manager as ModuleManager;
use Psr\Log\LoggerInterface;

class StockSync extends AbstractSync implements StockSyncInterface
{
    const SKU_ATTRIBUTE = 'sku';
    const MSI_MODULE = 'Magento_Inventory';

    private $inventoryService;
    private $stockItemBuilder;
    private $massScheduleFactory;
    private $moduleManager;
    private $productCollectionFactory;
    private $_productCollection;
    private $stockRegistryFactory;
    private $stockRegistry;
    private $sourceItemsSaveInterface;
    private $cacheContextFactory;
    private $appCache;
    private $eventManager;

    public function __construct(
        LoggerInterface $logger,
        ConfigInterface $config,
        StockItemBuilder $stockItemBuilder,
        MassScheduleFactory $massScheduleFactory,
        ModuleManager $moduleManager,
        ProductCollectionFactory $productCollectionFactory,
        StockRegistryInterfaceFactory $stockRegistryFactory,
        CacheContextFactory $cacheContextFactory,
        Cache $appCache,
        ManagerInterface $eventManager
    ) {
        $this->config = $config;
        $this->stockItemBuilder = $stockItemBuilder;
        $this->massScheduleFactory = $massScheduleFactory;
        $this->moduleManager = $moduleManager;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->stockRegistryFactory = $stockRegistryFactory;
        $this->cacheContextFactory = $cacheContextFactory;
        $this->appCache = $appCache;
        $this->eventManager = $eventManager;
        parent::__construct($logger, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAllStock(): array
    {
        $this->init();
        $stock = $this->inventoryService->fetchTotalInventory()->getInventory();

        return $this->processStock($stock);
    }

    /**
     * I create a new Inventory service
     * @throws DistriMediaException
     */
    private function init(): void
    {
        if ($this->inventoryService === null) {
            $uri = $this->config->getApiUri();
            $password = $this->config->getApiPassword();
            $webshopCode = $this->config->getWebshopCode();
            if (!empty($uri) && !empty($password) && !empty($webshopCode)) {
                $this->inventoryService = new DistriMediaInventoryService($uri, $webshopCode, $password);
            } else {
                throw new DistriMediaException(
                    'Invalid DistriMedia Configuration. Some fields are missing (uri, webshopcode or password)'
                );
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function processStock(array $stockDatas): array
    {
        $errors = [];

        $useMsi = true;
        if (!$this->moduleManager->isEnabled(self::MSI_MODULE)) {
            $useMsi = false;
        }

        $eanAttr = $this->config->getEanCodeAttributeCode();

        $stockItems = [];
        $productIds = [];
        foreach ($stockDatas as $stockData) {
            try {
                $ean = $stockData->getEan();
                $product = $this->getProductByEan($ean, $eanAttr);

                if ($eanAttr === self::SKU_ATTRIBUTE) {
                    $sku = $ean;
                } else {
                    if ($product instanceof Product) {
                        $sku = $product->getSku();
                    } else {
                        throw new DistriMediaException(
                            "Stock sync problem: Could not find product with {$eanAttr} = {$ean}"
                        );
                    }
                }
                $qty = (int) $stockData->getClaimable();

                if ($useMsi) {
                    $sourceItem = $this->stockItemBuilder->createSourceItemInterface($qty, $sku);
                    $stockItems[$sku] = $sourceItem;
                } else {
                    $stockItemInterface = $this->stockItemBuilder->createStockItemInterface($qty);
                    $stockItems[$sku] = $stockItemInterface;
                }
                if ($product instanceof Product) {
                    $productIds[] = $product->getId();
                }
            } catch (\Exception $exception) {
                $errors[] = $exception->getMessage();
                $this->logger->critical($exception->getMessage());
            }
        }

        if ($useMsi) {
            if (!empty($stockItems)) {
                $inventorySaveApi = $this->getSourceItemsSaveInterface();
                $inventorySaveApi->execute($stockItems);
            }
        } else {
            foreach ($stockItems as $sku => $stockItem) {
                $this->getStockRegistry()->updateStockItemBySku($sku, $stockItem);
            }
        }

        $this->flushCache($productIds);
        $errors[] = __(count($stockItems) . ' Products are updated');

        return $errors;
    }

    private function flushCache(array $productIds = []): void
    {
        /** @var CacheContext $cacheContext */
        $cacheContext = $this->cacheContextFactory->create();

        $cacheContext->registerEntities(Product::CACHE_TAG, $productIds);

        //Emulate Magento\Indexer\Model\Indexer\CacheCleaner
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $cacheContext]);
        $identities = $cacheContext->getIdentities();
        if (!empty($identities)) {
            $this->appCache->clean($identities);
        }
    }

    private function getStockRegistry(): StockRegistryInterface
    {
        if ($this->stockRegistry === null) {
            $this->stockRegistry = $this->stockRegistryFactory->create();
        }

        return $this->stockRegistry;
    }

    private function getSourceItemsSaveInterface()
    {
        if ($this->sourceItemsSaveInterface === null) {
            $this->sourceItemsSaveInterface = ObjectManager::getInstance()->create('\Magento\InventoryApi\Api\SourceItemsSaveInterface');
        }

        return $this->sourceItemsSaveInterface;
    }

    private function getProductByEan(string $eanValue, string $eanAttributeCode): ?Product
    {
        if ($this->_productCollection === null) {
            $this->_productCollection = $this->productCollectionFactory->create()
                                               ->addAttributeToSelect($eanAttributeCode)
                                               ->addAttributeToSelect('sku');
        }

        return $this->_productCollection->getItemByColumnValue($eanAttributeCode, $eanValue);
    }
}

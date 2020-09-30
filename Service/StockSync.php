<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Service;

use DistriMedia\Connector\Helper\StockItemBuilder;
use DistriMedia\Connector\Model\ConfigInterface;
use DistriMedia\SoapClient\Service\AbstractSoapClient;
use DistriMedia\SoapClient\Service\Inventory as DistriMediaInventoryService;
use Magento\AsynchronousOperations\Model\MassSchedule;
use Psr\Log\LoggerInterface;
use Magento\AsynchronousOperations\Model\MassScheduleFactory;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class StockSync extends AbstractSync implements StockSyncInterface
{
    const SKU_ATTRIBUTE = 'sku';
    const PRODUCT_MASS_SCHEDULE_PUT = 'async.magento.cataloginventory.api.stockregistryinterface.updatestockitembysku.put';
    const PRODUCT_MSI_MASS_SCHEDULE_POST = 'async.magento.inventoryapi.api.sourceitemssaveinterface.execute.post';

    const MSI_MODULE = 'Magento_Inventory';

    /**
     * @var DistriMediaInventoryService $inventoryService
     */
    private $inventoryService;

    private $stockItemBuilder;
    private $massScheduleFactory;
    private $moduleManager;
    private $productCollectionFactory;

    /**
     * @var ProductCollection $_productCollection
     */
    private $_productCollection;

    public function __construct(
        LoggerInterface $logger,
        ConfigInterface $config,
        StockItemBuilder $stockItemBuilder,
        MassScheduleFactory $massScheduleFactory,
        ModuleManager $moduleManager,
        ProductCollectionFactory $productCollectionFactory
    )
    {
        $this->config = $config;
        $this->stockItemBuilder = $stockItemBuilder;
        $this->massScheduleFactory = $massScheduleFactory;
        $this->moduleManager = $moduleManager;
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct($logger, $config);
    }

    /**
     * @inheritDoc
     */
    public function fetchAllStock(): array
    {
        $this->init();
        $stock = $this->inventoryService->fetchTotalInventory()->getInventory();

        return $this->processStock($stock);
    }

    /**
     * I create a new Inventory service
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
                throw new \Exception("Invalid DistriMedia Configuration. Some fields are missing (uri, webshopcode or password)");
            }
        }
    }
    /**
     * @inheritDoc
     */
    public function processStock(array $stockItems): array
    {
        $errors = [];

        $useMsi = true;
        if (!$this->moduleManager->isEnabled(self::MSI_MODULE)) {
            $useMsi = false;
        }

        $eanAttr = $this->config->getEanCodeAttributeCode();

        $bulkMessage = [];

        foreach ($stockItems as $stockItem) {
            try {
                $ean = $stockItem->getEan();
                if ($eanAttr === self::SKU_ATTRIBUTE) {
                    $sku = $ean;
                } else {
                    $product = $this->getProductByEan($ean, $eanAttr);
                    if ($product !== null) {
                        $sku = $product->getData('sku');
                    } else {
                        throw new \Exception("Stock sync problem: Could not find product with {$eanAttr} = {$ean}");
                    }
                }
                $qty = (int) $stockItem->getPieces();

                if ($useMsi) {
                    $sourceItem = $this->stockItemBuilder->createSourceItemInterface($qty, $sku);
                    $bulkMessage[] = $sourceItem;
                } else {
                    $stockItemInterface = $this->stockItemBuilder->createStockItemInterface($qty);

                    $productArray = [
                        'productSku' => $sku,
                        'stockItem' => $stockItemInterface
                    ];

                    $bulkMessage[] = $productArray;
                }
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
                $errors[] = $exception->getMessage();
            }
        }


        if (!empty($bulkMessage)) {
            /** @var MassSchedule $massSchedule */
            $massSchedule = $this->massScheduleFactory->create();

            $topic = self::PRODUCT_MASS_SCHEDULE_PUT;

            if ($useMsi === true) {
                $topic = self::PRODUCT_MSI_MASS_SCHEDULE_POST;
            }

            $massSchedule->publishMass(
                $topic,
                $bulkMessage
            );
        }

        $errors[] = __(count($bulkMessage) . " Products are updated");

        return $errors;
    }

    private function getProductByEan(string $eanValue, string $eanAttributeCode)
    {
        if ($this->_productCollection === null) {
            $this->_productCollection = $this->productCollectionFactory->create()
                ->addAttributeToSelect($eanAttributeCode)
                ->addAttributeToSelect('sku');
        }

        return $this->_productCollection->getItemByColumnValue($eanAttributeCode, $eanValue);
    }
}

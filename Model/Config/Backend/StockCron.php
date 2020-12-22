<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model\Config\Backend;

use DistriMedia\Connector\DistriMediaException;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\App\Config\ValueFactory as ConfigValueFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class StockCron extends ConfigValue
{
    const CRON_STRING_PATH = 'crontab/default/jobs/distrimedia_connector_sync_stock/schedule/cron_expr';

    private $configValueFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ConfigValueFactory $configValueFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->configValueFactory = $configValueFactory;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function afterSave()
    {
        $time = $this->getData('groups/stock_subscription_cron/fields/time/value');

        $cronExprArray = [
            (int) $time[1], //Minute
            (int) $time[0], //Hour
            '*', //Day of the Month
            '*', //Month of the Year
            '*', //Day of the Week
        ];

        $cronExprString = join(' ', $cronExprArray);

        try {
            $this->configValueFactory->create()->load(
                self::CRON_STRING_PATH,
                'path'
            )->setValue(
                $cronExprString
            )->setPath(
                self::CRON_STRING_PATH
            )->save();
        } catch (\Exception $e) {
            throw new DistriMediaException(__('We can\'t save the cron expression: ' . $e->getMessage()));
        }

        return parent::afterSave();
    }
}

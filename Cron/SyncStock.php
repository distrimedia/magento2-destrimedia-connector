<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Cron;

use DistriMedia\Connector\Model\ConfigInterface;
use DistriMedia\Connector\Service\StockSyncInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;

/**
 * I am responsible for syncing the complete inventory once a day.
 * Class SyncStock
 * @package DistriMedia\Connector\Cron
 */
class SyncStock
{
    const XML_PATH_ERROR_TEMPLATE = 'distrimedia/stock_cron/error_email_template';
    const XML_PATH_ERROR_IDENTITY = 'distrimedia/stock_cron/error_email_identity';
    const XML_PATH_ERROR_RECIPIENT = 'distrimedia/stock_cron/error_email';

    private $stockSync;
    private $_errors = [];
    private $scopeConfig;
    private $transportBuilder;
    private $inlineTranslation;
    private $config;

    public function __construct(
        StockSyncInterface $stockSync,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ConfigInterface $config
    )
    {
        $this->stockSync = $stockSync;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->config = $config;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function execute()
    {
        if ($this->config->isEnabled()) {
            $this->processStock();
            $this->_sendErrorEmail();
        }

        return $this;
    }

    public function processStock()
    {
        $this->_errors = $this->stockSync->fetchAllStock();
    }

    /**
     * Send email to administrator if error
     *
     * @return $this
     */
    protected function _sendErrorEmail()
    {
        if (count($this->_errors)) {
            if (!$this->scopeConfig->getValue(
                self::XML_PATH_ERROR_TEMPLATE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ) {
                return $this;
            }

            $this->inlineTranslation->suspend();

            $transport = $this->transportBuilder->setTemplateIdentifier(
                $this->scopeConfig->getValue(
                    self::XML_PATH_ERROR_TEMPLATE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->setTemplateOptions(
                [
                    'area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )->setTemplateVars(
                ['warnings' => implode("<br />", $this->_errors)]
            )->setFrom(
                $this->scopeConfig->getValue(
                    self::XML_PATH_ERROR_IDENTITY,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->addTo(
                $this->scopeConfig->getValue(
                    self::XML_PATH_ERROR_RECIPIENT,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->getTransport();

            $transport->sendMessage();

            $this->inlineTranslation->resume();
            $this->_errors[] = [];
        }
        return $this;
    }
}

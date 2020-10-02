<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model;

use Magento\Directory\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * I return config values, specific for the DistriMedia module
 * @package DistriMedia\Connector\Model
 */
class Config implements ConfigInterface
{
    const XML_PATH_DISTRIMEDIA_SETTINGS_ENABLED = 'distrimedia/settings/enabled';
    const XML_PATH_DISTRIMEDIA_SETTINGS_API_URI = 'distrimedia/settings/api_uri';
    const XML_PATH_DISTRIMEDIA_SETTINGS_API_PASSWORD = 'distrimedia/settings/api_password';
    const XML_PATH_DISTRIMEDIA_SETTINGS_WEBSHOP_CODE = 'distrimedia/settings/webshop_code';
    const XML_PATH_DISTRIMEDIA_SETTINGS_EAN_CODE_ATTRIBUTE = 'distrimedia/settings/ean_code_attribute';
    const XML_PATH_DISTRIMEDIA_SETTINGS_EXTERNAL_REF_ATTRIBUTE = 'distrimedia/settings/external_ref_attribute';
    const XML_PATH_DISTRIMEDIA_SETTINGS_EXTERNAL_USE_RETENTION_DAYS = 'distrimedia/settings/use_retention_days';
    const XML_PATH_DISTRIMEDIA_SETTINGS_EXTERNAL_RETENTION_DAYS = 'distrimedia/settings/retention_days';
    const XML_PATH_DISTRIMEDIA_SETTINGS_EXTERNAL_USE_CANCELLATION_DAYS = 'distrimedia/settings/use_cancellation_days';
    const XML_PATH_DISTRIMEDIA_SETTINGS_EXTERNAL_CANCELLATION_DAYS = 'distrimedia/settings/cancellation_days';
    const XML_PATH_DISTRIMEDIA_SETTINGS_SEND_INVOICES = 'distrimedia/settings/send_invoices';
    const XML_PATH_DISTRIMEDIA_BPOST_USE_BPOST_LOCKERS_AND_PICKUP = 'distrimedia/bpost/use_bpost_lockers_and_pickup';
    const XML_PATH_DISTRIMEDIA_CONSUMER_ID = 'distrimedia/settings/consumer_id';
    const XML_PATH_ERROR_TEMPLATE = 'distrimedia/settings/error_email_template';
    const XML_PATH_ERROR_IDENTITY = 'distrimedia/settings/error_email_identity';
    const XML_PATH_ERROR_RECIPIENT = 'distrimedia/settings/error_email';

    private $scopeConfig;
    private $encryptor;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor
    )
    {
        $this->encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritDoc
     */
    public function getApiUri(): string
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_DISTRIMEDIA_SETTINGS_API_URI) ?: '';

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getApiPassword(): string
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_DISTRIMEDIA_SETTINGS_API_PASSWORD) ?: '';

        if (!empty($value)) {
            $value = $this->encryptor->decrypt($value);
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getWebshopCode(): string
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_DISTRIMEDIA_SETTINGS_WEBSHOP_CODE) ?: '';

        if (!empty($value)) {
            $value = $this->encryptor->decrypt($value);
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        $value = (bool)$this->scopeConfig->getValue(self::XML_PATH_DISTRIMEDIA_SETTINGS_ENABLED) ?: false;

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getLocaleOfStoreId(int $storeId): string
    {
        $value = $this->scopeConfig->getValue(
            Data::XML_PATH_DEFAULT_LOCALE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?: '';

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getEanCodeAttributeCode(): string
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_DISTRIMEDIA_SETTINGS_EAN_CODE_ATTRIBUTE) ?: '';

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getExternalRefAttributeCode(): string
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_DISTRIMEDIA_SETTINGS_EXTERNAL_REF_ATTRIBUTE) ?: '';

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function useCancellationDays(): bool
    {
        $value = (bool)$this->scopeConfig->getValue(self::XML_PATH_DISTRIMEDIA_SETTINGS_EXTERNAL_USE_CANCELLATION_DAYS) ?: false;

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getCancellationDays(): int
    {
        $value = (int) $this->scopeConfig->getValue(self::XML_PATH_DISTRIMEDIA_SETTINGS_EXTERNAL_CANCELLATION_DAYS) ?: 0;

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function useRetentionDays(): bool
    {
        $value = (bool)$this->scopeConfig->getValue(self::XML_PATH_DISTRIMEDIA_SETTINGS_EXTERNAL_USE_RETENTION_DAYS) ?: false;

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getRetentionDays(): int
    {
        $value = (int) $this->scopeConfig->getValue(self::XML_PATH_DISTRIMEDIA_SETTINGS_EXTERNAL_RETENTION_DAYS) ?: 0;

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function useBPostLockersAndPickup(): bool
    {
        $value = (bool)$this->scopeConfig->getValue(self::XML_PATH_DISTRIMEDIA_BPOST_USE_BPOST_LOCKERS_AND_PICKUP) ?: false;

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function sendInvoices(): bool
    {
        $value = (bool)$this->scopeConfig->getValue(self::XML_PATH_DISTRIMEDIA_SETTINGS_SEND_INVOICES) ?: false;

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getConsumerId(): ? int
    {
        $value = (int) $this->scopeConfig->getValue(self::XML_PATH_DISTRIMEDIA_CONSUMER_ID) ?: null;

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getErrorEmailTemplate(): ? string
    {
        $value = (string) $this->scopeConfig->getValue(self::XML_PATH_ERROR_TEMPLATE) ?: null;

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getErrorEmailRecipient(): ? string
    {
        $value = (string) $this->scopeConfig->getValue(self::XML_PATH_ERROR_RECIPIENT) ?: null;

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getErrorEmailIdentity(): ? string
    {
        $value = (string) $this->scopeConfig->getValue(self::XML_PATH_ERROR_IDENTITY) ?: null;

        return $value;
    }
}

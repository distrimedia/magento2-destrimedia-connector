<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model;

interface ConfigInterface
{
    /**
     * Is the connector enabled
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @return string
     */
    public function getApiUri(): string;

    /**
     * Api password, this is an encrypted value
     * @return string
     */
    public function getApiPassword(): string;

    /**
     * Webshop code, this is an encrypted value
     * @return string
     */
    public function getWebshopCode(): string;

    /**
     * @return string
     */
    public function getEanCodeAttributeCode(): string;

    /**
     * @return string
     */
    public function getExternalRefAttributeCode(): string;

    /**
     * @return bool
     */
    public function useCancellationDays(): bool;

    /**
     * @return int
     */
    public function getCancellationDays(): int;

    /**
     * @return bool
     */
    public function useRetentionDays(): bool;

    /**
     * @return int
     */
    public function getRetentionDays(): int;

    /**
     * @return bool
     */
    public function useBPostLockersAndPickup(): bool;

    /**
     * @return string
     */
    public function getLocaleOfStoreId(int $storeId): string;

    /**
     * @return bool
     */
    public function sendInvoices(): bool;

    /**
     * @return int|null
     */
    public function getConsumerId(): ? int;
}

<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model;

interface ConfigInterface
{
    /**
     * Is the connector enabled
     */
    public function isEnabled(): bool;

    public function getApiUri(): string;

    /**
     * Api password, this is an encrypted value
     */
    public function getApiPassword(): string;

    /**
     * Returns after how many seconds the connection to DistriMedia will time out.
     */
    public function getTimeoutAterInSeconds(): int;

    /**
     * Webshop code, this is an encrypted value
     */
    public function getWebshopCode(): string;

    public function getEanCodeAttributeCode(): string;

    public function getExternalRefAttributeCode(): string;

    public function getCountryOriginAttribute(): string;

    public function getHSCodeAttribute(): string;

    public function useCancellationDays(): bool;

    public function getCancellationDays(): int;

    public function useRetentionDays(): bool;

    public function getRetentionDays(): int;

    public function useBPostLockersAndPickup(): bool;

    public function getLocaleOfStoreId(int $storeId): string;

    public function sendInvoices(): int;

    public function getConsumerId(): ? int;

    public function getErrorEmailRecipient(): ? string;

    public function getErrorEmailIdentity(): ? string;

    public function getSiteIndication(): string;

    public function getErrorEmailTemplate(): ? string;

    public function getEuCountries(): array;
}

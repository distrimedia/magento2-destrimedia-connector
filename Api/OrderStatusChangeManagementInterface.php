<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Api;

interface OrderStatusChangeManagementInterface
{
    /**
     * @param string $OrderStatus
     * @param string $OrderID
     * @param string $OrderNumber
     * @param string|null $NumberColli
     * @param string|null $Carrier
     * @param string|null $TrackAndTraceURL
     * @param DistriMedia\Connector\Api\Data\TrackIdInterface[] $TrackIDs
     * @param DistriMedia\Connector\Api\Data\ShippedItemInterface[] $ShippedItems
     * @return string
     */
    public function execute(
        string $OrderStatus,
        string $OrderID,
        string $OrderNumber,
        string $NumberColli = null,
        string $Carrier = null,
        string $TrackAndTraceURL = null,
        array $TrackIDs = [],
        array $ShippedItems = []
    );
}

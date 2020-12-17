<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Api;

interface OrderStatusChangeManagementInterface
{
    /**
     * @param array $TrackIDs
     * @param array $ShippedItems
     * @return mixed
     */
    public function execute(
        string $OrderStatus,
        string $OrderID,
        string $OrderNumber,
        string $NumberColli = null,
        string $Carrier = null,
        string $TrackAndTraceURL = null,
        $TrackIDs = [],
        $ShippedItems = []
    );
}

<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Api;

interface OrderStatusChangeManagementInterface
{
    /**
     * @param string $OrderID
     * @param string $OrderNumber
     * @param string $OrderStatus
     * @param string $NumberColli
     * @param string $Carrier
     * @param string $TrackAndTraceURL
     * @param mixed $TrackIDs
     * @param mixed $ShippedItems
     * @return mixed
     */
    public function execute(
        string $OrderID,
        string $OrderNumber,
        string $OrderStatus,
        string $NumberColli,
        string $Carrier,
        string $TrackAndTraceURL,
        $TrackIDs,
        $ShippedItems
    );
}

<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Service;

use Magento\Sales\Api\Data\InvoiceInterface;
use DistriMedia\SoapClient\Struct\Order as OrderStruct;
use Magento\Sales\Api\Data\OrderInterface;

interface OrderSyncInterface
{
    /**
     * @param OrderStruct $distriMediaOrder
     * @return mixed
     */
    public function syncDistriMediaOrder(OrderStruct $distriMediaOrder);

    /**
     * @param OrderInterface $order
     * @return mixed
     */
    public function preprareOrderForSync(OrderInterface $order);

    /**
     * @param OrderInterface $order
     * @return bool|null
     */
    public function cancelOrder(OrderInterface $order): ? bool;
}

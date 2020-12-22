<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Service;

use DistriMedia\SoapClient\Struct\Order as OrderStruct;
use Magento\Sales\Api\Data\OrderInterface;

interface OrderSyncInterface
{
    /**
     * @return mixed
     */
    public function syncDistriMediaOrder(OrderStruct $distriMediaOrder);

    /**
     * @return mixed
     */
    public function preprareOrderForSync(OrderInterface $order);

    public function cancelOrder(OrderInterface $order): ? bool;
}

<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Sales\Api\Data\InvoiceSearchResultInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterface as MagentoOrder;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;

/**
 * I am responsible for getting orders and invoices from Magento
 * Interface OrderFetcherInterface
 */
interface OrderFetcherInterface
{
    /**
     * @return OrderSearchResultInterface
     */
    public function getUnsyncedOrdersInProgress(): Collection;

    /**
     * @param $entityId
     * @return ProductInterface
     */
    public function getOrderByEntityId($entityId): OrderInterface;

    public function getPaidInvoicesByOrder(MagentoOrder $order): InvoiceSearchResultInterface;

    public function getOrderByDistriMediaData(string $magentoIncrementID, string $distriMediaIncrementID): ?MagentoOrder;
}

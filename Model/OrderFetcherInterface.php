<?php

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
 * @package DistriMedia\Connector\Model
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

    /**
     * @param MagentoOrder $order
     * @return InvoiceSearchResultInterface
     */
    public function getPaidInvoicesByOrder(MagentoOrder $order): InvoiceSearchResultInterface;

    /**
     * @param string $magentoIncrementID
     * @param string $distriMediaIncrementID
     * @return MagentoOrder|null
     */
    public function getOrderByDistriMediaData(string $magentoIncrementID, string $distriMediaIncrementID): ?MagentoOrder;
}

<?php

namespace DistriMedia\Connector\Model;

use Magento\Sales\Api\Data\InvoiceSearchResultInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterface as MagentoOrder;
use Magento\Sales\Api\Data\OrderSearchResultInterface;

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
    public function getOrdersInProgress(): OrderSearchResultInterface;

    /**
     * @param MagentoOrder $order
     * @return InvoiceSearchResultInterface
     */
    public function getPaidInvoicesByOrder(MagentoOrder $order): InvoiceSearchResultInterface;

    /**
     * @param string $distriMediaIncrementId
     * @return mixed
     */
    public function getOrderByDistriMediaIncrementId(string $distriMediaIncrementId);
}

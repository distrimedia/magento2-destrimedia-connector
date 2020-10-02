<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model;

use DistriMedia\Connector\Ui\Component\Listing\Column\SyncStatus\Options;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterFactory;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\FilterGroupFactory;
use Magento\Framework\Api\SearchCriteriaFactory;
use Magento\Sales\Api\Data\InvoiceSearchResultInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterface as MagentoOrder;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

/**
 * I am responsible for getting orders and invoices from Magento
 * Class OrderFetcher
 * @package DistriMedia\Connector\Helper
 */
class OrderFetcher implements OrderFetcherInterface
{
    private $searchCriteriaFactory;
    private $filterGroupFactory;
    private $filterFactory;
    private $orderRepository;
    private $invoiceRepository;
    private $orderCollectionFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        FilterFactory $filterFactory,
        FilterGroupFactory $filterGroupFactory,
        SearchCriteriaFactory $searchCriteriaFactory,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->filterFactory = $filterFactory;
        $this->filterGroupFactory = $filterGroupFactory;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
        $this->invoiceRepository = $invoiceRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @inheridoc
     */
    public function getOrdersInProgress(): OrderSearchResultInterface
    {
        /* @var Filter $paidOrderFilter */
        $paidOrderFilter = $this->filterFactory->create()
            ->setField('main_table.' . Order::STATE)
            ->setValue(Order::STATE_PROCESSING)
            ->setConditionType('eq');

        /* @var FilterGroup $filterGroup */
        $filterGroup = $this->filterGroupFactory
            ->create()
            ->setFilters([$paidOrderFilter]);

        /* @var SearchCriteria $searchCriteria */
        $searchCriteria = $this->searchCriteriaFactory
            ->create()
            ->setFilterGroups([$filterGroup]);

        $orders = $this->orderRepository->getList($searchCriteria);

        return $orders;
    }

    /**
     * @inheridoc
     */
    public function getPaidInvoicesByOrder(MagentoOrder $order): InvoiceSearchResultInterface
    {

        /* @var Filter $paidInvoicesFilter */
        $paidInvoicesFilter = $this->filterFactory->create();
        $paidInvoicesFilter
            ->setField(\Magento\Sales\Model\Order\Invoice::STATE)
            ->setValue(\Magento\Sales\Model\Order\Invoice::STATE_PAID)
            ->setConditionType('eq');

        /* @var Filter $orderFilter */
        $orderFilter = $this->filterFactory->create();
        $orderFilter
            ->setField(\Magento\Sales\Model\Order\Invoice::ORDER_ID)
            ->setValue($order->getEntityId())
            ->setConditionType('eq');


        $filterGroup = $this->filterGroupFactory->create()
            ->setFilters([$paidInvoicesFilter, $orderFilter]);

        $searchCriteria = $this->searchCriteriaFactory->create()->setFilterGroups([$filterGroup]);

        $invoices = $this->invoiceRepository->getList($searchCriteria);

        return $invoices;
    }

    /**
     * @inheridoc
     */
    public function getOrderByDistriMediaIncrementId(string $incrementId)
    {
        $order = $this->orderCollectionFactory->create()
            ->addFieldToSelect("*")
            ->addFieldToFilter('distri_media_increment_id', ['eq' => $incrementId])
            ->getFirstItem();

        return $order;
    }
}

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
use Magento\Sales\Model\ResourceModel\Order\Collection;
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
    )
    {
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
    public function getUnsyncedOrdersInProgress(): Collection
    {
        $orderCollection = $this->orderCollectionFactory->create()
            ->addFieldToSelect("*")
            ->addFieldToFilter("distri_media_increment_id", ['null' => true])
            ->addFieldToFilter("state", ['eq' => 'processing']);

        return $orderCollection;
    }

    public function getOrderByEntityId($entityId): OrderInterface
    {
        return $this->orderRepository->get($entityId);
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

        $filterInvoiceGroup = $this->filterGroupFactory->create()
            ->setFilters([$paidInvoicesFilter]);

        /* @var Filter $orderFilter */
        $orderFilter = $this->filterFactory->create();
        $orderFilter
            ->setField(\Magento\Sales\Model\Order\Invoice::ORDER_ID)
            ->setValue($order->getEntityId())
            ->setConditionType('eq');


        $filterOrderGroup = $this->filterGroupFactory->create()
            ->setFilters([$orderFilter]);

        $searchCriteria = $this->searchCriteriaFactory->create()->setFilterGroups([$filterInvoiceGroup, $filterOrderGroup]);

        $invoices = $this->invoiceRepository->getList($searchCriteria);

        return $invoices;
    }

    /**
     * @inheridoc
     */
    public function getOrderByDistriMediaData(string $magentoIncrementID, string $distriMediaIncrementID)
    {
        $order = $this->orderCollectionFactory->create()
            ->addFieldToSelect("*")
            ->addFieldToFilter(
                [
                    'increment_id', ['eq' => $magentoIncrementID],
                    'distri_media_increment_id', ['eq' => $distriMediaIncrementID]
                ]
            )
            ->getFirstItem();

        return $order;
    }
}

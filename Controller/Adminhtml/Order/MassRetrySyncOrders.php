<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Controller\Adminhtml\Order;

use DistriMedia\Connector\Ui\Component\Listing\Column\SyncStatus\Options;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassRetrySyncOrders extends AbstractMassAction implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'DistriMedia_Connector::settings';

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    private $logger;

    /**
     * MassRetrySyncOrders constructor.
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Redirect|ResultInterface
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countCancelOrder = 0;
        foreach ($collection->getItems() as $order) {
            $isQueued = false;
            $orderId = $order->getEntityId();

            try {
                $order = $this->orderRepository->get($orderId);

                $extAttrs = $order->getExtensionAttributes();

                //we cannot reschedule orders that are already pushed to distrimedia
                if (empty($extAttrs->getDistriMediaIncrementId())) {
                    $extAttrs->setDistriMediaSyncAttempts(0);
                    $extAttrs->setDistriMediaSyncStatus(Options::SYNC_STATUS_NOT_SYNCED);
                }

                $order->setExtensionAttributes($extAttrs);
                $this->orderRepository->save($order);
                $isQueued = true;
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
            }

            if ($isQueued === false) {
                continue;
            }

            ++$countCancelOrder;
        }

        $countNonCancelOrder = $collection->count() - $countCancelOrder;

        if ($countNonCancelOrder && $countCancelOrder) {
            $this->messageManager->addErrorMessage(
                __('Cannot reschedule Distrimedia sync for %1 order(s) ', $countNonCancelOrder)
            );
        } elseif ($countNonCancelOrder) {
            $this->messageManager->addErrorMessage(__('Cannot reschedule Distrimedia sync for the order(s).'));
        }

        if ($countCancelOrder) {
            $this->messageManager->addSuccessMessage(
                __('We rescheduled Distrimedia sync for %1 order(s).', $countCancelOrder)
            );
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }
}

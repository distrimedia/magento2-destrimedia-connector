<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Controller\Adminhtml\Stock;

use DistriMedia\Connector\Model\Flag\Status;
use Magento\Cron\Model\ResourceModel\Schedule\Collection as CronScheduleCollection;
use Magento\Cron\Model\Schedule;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Sync extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'DistriMedia_Connector::settings';
    const CRONJOB_NAME = 'distrimedia_connector_sync_stock';

    private $resultJsonFactory;
    private $dateTime;
    private $cronScheduleCollection;
    private $statusFlagData;
    private $statusFlag;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        DateTime $dateTime,
        CronScheduleCollection $cronScheduleCollection,
        Status $statusFlag
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->dateTime = $dateTime;
        $this->cronScheduleCollection = $cronScheduleCollection;
        $this->statusFlag = $statusFlag;
    }

    public function execute()
    {
        $success = false;
        $message = '';

        try {
            $scheduleId = $this->scheduleNewStockSyncJob();

            $success = true;
            $message = __("Stock Sync has been scheduled and will start shortly (schedule id: %1)", $scheduleId);
            $this->updateStatus(Status::STATUS_PENDING);
        } catch (\Exception $ex) {
            $message = "ERROR: {$ex->getMessage()}";
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([
            'valid'   => (int) $success,
            'message' => $message,
        ]);
    }

    private function scheduleNewStockSyncJob()
    {
        $createdAtTime   = $this->dateTime->gmtTimestamp();
        $scheduledAtTime = $createdAtTime;

        $schedule = $this->cronScheduleCollection->getNewEmptyItem();
        $schedule
            ->setJobCode(self::CRONJOB_NAME)
            ->setStatus(Schedule::STATUS_PENDING)
            ->setCreatedAt(strftime('%Y-%m-%d %H:%M:%S', $createdAtTime))
            ->setScheduledAt(strftime('%Y-%m-%d %H:%M', $scheduledAtTime))
            ->save();

        return $schedule->getScheduleId();
    }

    private function updateStatus(string $status)
    {
        if ($this->statusFlagData === null) {
            $statusFlag = $this->statusFlag->loadSelf();
            $this->statusFlagData = $statusFlag;
        }

        $this->statusFlagData->setFlagData($status);
        $this->statusFlagData->save();
    }
}

<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Cron;

use DistriMedia\Connector\Helper\ErrorHandlingHelper;
use DistriMedia\Connector\Model\Flag\LastExecutionFlag;
use DistriMedia\Connector\Model\Flag\Status;
use DistriMedia\Connector\Service\StockSyncInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * I am responsible for syncing the complete inventory once a day.
 * Class SyncStock
 * @package DistriMedia\Connector\Cron
 */
class SyncStock
{

    private $stockSync;
    private $config;
    private $lastExecutionFlag;
    private $statusFlag;
    private $dateTime;

    private $statusFlagData;

    public function __construct(
        StockSyncInterface $stockSync,
        ErrorHandlingHelper $errorHandlingHelper,
        LastExecutionFlag $lastExecutionFlag,
        Status $statusFlag,
        DateTime $dateTime
    )
    {
        $this->stockSync = $stockSync;
        $this->errorHandlingHelper = $errorHandlingHelper;
        $this->lastExecutionFlag = $lastExecutionFlag;
        $this->statusFlag = $statusFlag;
        $this->dateTime = $dateTime;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function execute()
    {
        if ($this->config->isEnabled()) {
            $lastExecutionFlag = $this->lastExecutionFlag->loadSelf();
            $now = $this->dateTime->gmtDate();
            $lastExecutionFlag->setFlagData($now);
            $lastExecutionFlag->save();

            $this->updateStatus(Status::STATUS_RUNNING);

            $this->processStock();
        }

        return $this;
    }

    public function processStock()
    {
        $errors = $this->stockSync->fetchAllStock();
        if (!empty($errors)) {
            $this->errorHandlingHelper->sendErrorEmail($errors);
            $this->updateStatus(Status::STATUS_ERROR);
        } else {
            $this->updateStatus(Status::STATUS_SUCCESS);
        }
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

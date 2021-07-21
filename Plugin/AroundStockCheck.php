<?php

namespace DistriMedia\Connector\Plugin;

use Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterface;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionService;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Exception\LocalizedException;

class AroundStockCheck
{
    const CHANGE_ORDER_STATUS_ACTION = 'distrimedia/order/change';
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function aroundExecute(
        SourceDeductionService $subject,
        \Closure $proceed,
        SourceDeductionRequestInterface $sourceDeductionRequest
    ) {
        try {
            $proceed($sourceDeductionRequest);
        } catch (LocalizedException $e) {
            //Catch the exception in order to remove the stock validation
            if (strpos($this->request->getPathInfo(), self::CHANGE_ORDER_STATUS_ACTION) === false) {
                throw $e;
            }
        }
    }
}

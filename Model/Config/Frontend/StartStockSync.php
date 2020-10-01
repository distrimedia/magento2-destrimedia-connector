<?php

namespace DistriMedia\Connector\Model\Config\Frontend;


use DistriMedia\Connector\Model\Flag\LastExecutionFlag;
use DistriMedia\Connector\Model\Flag\Status;
use Magento\Backend\Block\Template\Context;
use \Magento\Config\Block\System\Config\Form\Field;
use \Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class StartStockSync extends Field
{
    protected $_template = 'DistriMedia_Connector::stock_sync.phtml';
    /**
     * @var LastExecutionFlag
     */
    private $lastExecutionFlag;
    /**
     * @var Status
     */
    private $statusFlag;
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    public function __construct(
        Context $context,
        LastExecutionFlag $lastExecutionFlag,
        Status $statusFlag,
        TimezoneInterface $timezone,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->lastExecutionFlag = $lastExecutionFlag;
        $this->statusFlag = $statusFlag;
        $this->timezone = $timezone;
    }


    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : 'Start new stock sync';
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('distrimedia/stock/sync'),
            ]
        );

        return $this->_toHtml();
    }

    public function getLastExecutionDate()
    {
        $flag = $this->lastExecutionFlag->loadSelf();

        $time = $flag->getFlagData();

        $dateTimeZone = '';

        if ($time) {
            $dateTimeZone = $this->timezone->formatDate(new \DateTime($time), 2, true);
        }

        return $dateTimeZone;
    }

    public function getStatus()
    {
        $flag = $this->statusFlag->loadSelf();

        return $flag->getFlagData();
    }

}

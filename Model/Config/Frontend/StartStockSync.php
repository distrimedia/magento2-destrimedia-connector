<?php

namespace DistriMedia\Connector\Model\Config\Frontend;

use \Magento\Config\Block\System\Config\Form\Field;
use \Magento\Framework\Data\Form\Element\AbstractElement;

class StartStockSync extends Field
{
    protected $_template = 'DistriMedia_Connector::stock_sync.phtml';

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

}

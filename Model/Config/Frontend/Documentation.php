<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model\Config\Frontend;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Documentation extends Field
{
    protected $_template = 'DistriMedia_Connector::documentation.phtml';

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->toHtml();
    }
}

<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model\Config\Frontend;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Store\Model\Store;

class BaseUrl extends Field
{
    protected $_template = 'DistriMedia_Connector::base_url.phtml';
    private $config;

    public function __construct(
        Context $context,
        ScopeConfigInterface $config,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->toHtml();
    }

    public function getBaseUrl()
    {
        return $this->config->getValue(Store::XML_PATH_SECURE_BASE_URL);
    }
}

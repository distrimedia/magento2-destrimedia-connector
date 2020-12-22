<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model\Config\Frontend;

use DistriMedia\Connector\Model\ConfigInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\UrlFactory;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class AccessToken extends Field
{
    const CREATE_TOKEN_URL = 'distrimedia/token/create';

    protected $_template = 'DistriMedia_Connector::access_token.phtml';
    private $config;
    private $oauthService;
    private $urlFactory;

    public function __construct(
        Context $context,
        ConfigInterface $config,
        \Magento\Integration\Api\OauthServiceInterface $oauthService,
        UrlFactory $urlFactory,
        array $data = []
    ) {
        $this->config = $config;
        $this->oauthService = $oauthService;
        $this->urlFactory = $urlFactory;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->toHtml();
    }

    /**
     * I return the distirmedia access token
     * @return string
     */
    public function getAccessToken()
    {
        $accessToken = $this->oauthService->getAccessToken($this->config->getConsumerId());
        $result = '';

        if ($accessToken instanceof \Magento\Integration\Model\Oauth\Token) {
            $result = $accessToken->getToken();
        }

        return $result;
    }

    /**
     * @return string|null
     */
    public function createAccessTokenUrl()
    {
        $url = $this->urlFactory->create();

        return $url->getUrl(self::CREATE_TOKEN_URL);
    }
}

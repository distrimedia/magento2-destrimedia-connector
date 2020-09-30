<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Helper;

use DistriMedia\Connector\Model\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Integration\Api\AuthorizationServiceInterface;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Integration\Model\Integration;
use Magento\Integration\Model\Integration as IntegrationModel;
use Magento\Integration\Model\ResourceModel\Integration\Collection as IntegrationCollection;
use Magento\Integration\Model\ResourceModel\Integration\CollectionFactory as IntegrationCollectionFactory;

class TokenBuilder
{
    const INTEGRATION_NAME = 'DistriMedia';

    private $integrationService;
    private $authorizationService;
    private $oauthService;
    private $configWriter;
    private $integrationCollectionFactory;
    private $cacheTypeList;
    private $cacheFrontendPool;

    public function __construct(
        IntegrationServiceInterface $integrationService,
        IntegrationCollectionFactory $integrationCollectionFactory,
        AuthorizationServiceInterface $authorizationService,
        OauthServiceInterface  $oauthService,
        WriterInterface $configWriter,
        TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    ) {
        $this->integrationService = $integrationService;
        $this->authorizationService = $authorizationService;
        $this->oauthService = $oauthService;
        $this->configWriter = $configWriter;
        $this->integrationCollectionFactory = $integrationCollectionFactory;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\IntegrationException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createToken()
    {
        $integrationData = [
            Integration::NAME => self::INTEGRATION_NAME
        ];

        $resources = [
            'DistriMedia_Connector::stock',
            'DistriMedia_Connector::orders'
        ];

        try {
            $integration = $this->integrationService->create($integrationData);
        } catch (\Exception $exception) {
            //this means that an integration already exists.
            /** @var IntegrationCollection $collection */
            $collection = $this->integrationCollectionFactory->create();
            $collection->addFieldToSelect("*");
            $integration = $collection
                ->addFieldToFilter(Integration::NAME, self::INTEGRATION_NAME)
                ->getFirstItem();
        }

        if ($integration) {
            $this->authorizationService->grantPermissions($integration->getId(), $resources);

            $this->oauthService->createAccessToken($integration->getConsumerId());

            $this->configWriter->save(
                Config::XML_PATH_DISTRIMEDIA_CONSUMER_ID,
                $integration->getConsumerId()
            );

            $integration->setStatus(IntegrationModel::STATUS_ACTIVE)->save();

            $token = $this->oauthService->getAccessToken($integration->getConsumerId());

            $this->cacheTypeList->cleanType('config');

            foreach ($this->cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }

            return $token->getToken();
        }
    }
}

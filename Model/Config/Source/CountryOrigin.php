<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model\Config\Source;

use Magento\Directory\Model\CountryFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;

class CountryOrigin extends AbstractSource implements OptionSourceInterface
{
    protected $_configCacheType;
    protected $_storeManager;
    protected $_countryFactory;
    private $serializer;

    public function __construct(
        CountryFactory $countryFactory,
        StoreManagerInterface $storeManager,
        Config $configCacheType
    ) {
        $this->_countryFactory = $countryFactory;
        $this->_storeManager = $storeManager;
        $this->_configCacheType = $configCacheType;
    }

    public function getAllOptions()
    {
        $cacheKey = 'COUNTRYOFORIGIN_SELECT_STORE_' . $this->_storeManager->getStore()->getCode();
        if ($cache = $this->_configCacheType->load($cacheKey)) {
            $options = $this->getSerializer()->unserialize($cache);
        } else {
            /** @var \Magento\Directory\Model\Country $country */
            $country = $this->_countryFactory->create();
            /** @var \Magento\Directory\Model\ResourceModel\Country\Collection $collection */
            $collection = $country->getResourceCollection();
            $options = $collection->load()->toOptionArray();
            $this->_configCacheType->save($this->getSerializer()->serialize($options), $cacheKey);
        }

        return $options;
    }

    private function getSerializer()
    {
        if ($this->serializer === null) {
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Serialize\SerializerInterface::class);
        }

        return $this->serializer;
    }
}

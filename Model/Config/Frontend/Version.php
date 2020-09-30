<?php

namespace DistriMedia\Connector\Model\Config\Frontend;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * I am responsible for getting the installed version of the module
 * @package DistriMedia\Connector\Model
 */
class Version extends Field
{
    const COMPOSER_LOCK_FILE = 'composer.lock';
    const MODULE_NAME = 'DistriMedia_Connector';
    const COMPOSER_PACKAGE = 'distrimedia/magento2-distrimedia-connector';

    private $componentRegistrar;
    private $readFactory;
    private $directoryList;
    private $serializer;
    /**
     * Version constructor.
     * @param Context $context
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param ReadFactory $readFactory
     * @param DirectoryList $directoryList
     * @param array $data
     */
    public function __construct(
        Context $context,
        ComponentRegistrarInterface $componentRegistrar,
        ReadFactory $readFactory,
        DirectoryList $directoryList,
        Json $serializer,
        array $data = []
    )
    {
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        $this->directoryList = $directoryList;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setData('value', $this->getModuleVersion());

        return $element->getElementHtml();
    }

    /**
     * @return string
     */
    protected function getModuleVersion(): string
    {
        $result = '';
        try {
            $directoryRead = $this->readFactory->create($this->directoryList->getRoot());
            $composerJsonData = $directoryRead->readFile(self::COMPOSER_LOCK_FILE);
            $data = $this->serializer->unserialize($composerJsonData);

            if (array_key_exists('packages', $data)) {
                $packages = $data['packages'];
                foreach ($packages as $module) {
                    if (isset($module['name']) && $module['name'] === self::COMPOSER_PACKAGE) {
                        if (isset($module['version'])) {
                            $result = $module['version'];
                            break;
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->_logger->warning("Could not read module version of " . self::MODULE_NAME);
        }

        return $result;
    }
}

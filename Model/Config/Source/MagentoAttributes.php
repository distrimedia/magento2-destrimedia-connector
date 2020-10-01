<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model\Config\Source;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;

/**
 * I am responsible for listing all the product attributes in an array
 * @package Baldwin\MedipimConnector\Model\Config\Source
 */
class MagentoAttributes implements \Magento\Framework\Option\ArrayInterface
{
    private $productAttributeRepository;
    private $criteriaBuilder;

    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository,
        SearchCriteriaBuilder $criteriaBuilder
    )
    {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->criteriaBuilder = $criteriaBuilder;
    }

    public function toOptionArray()
    {
        $attributeArray = [];
        $attributes = $this->productAttributeRepository->getList($this->criteriaBuilder->create())->getItems();

        foreach ($attributes as $attribute) {

            $labels = $attribute->getFrontendLabels();
            $label = reset($labels);

            $attributeArray[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getAttributeCode() //$label->getLabel()
            ];
        }
        return $attributeArray;
    }
}

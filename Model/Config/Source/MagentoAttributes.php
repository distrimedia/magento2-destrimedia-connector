<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model\Config\Source;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * I am responsible for listing all the product attributes in an array
 */
class MagentoAttributes implements \Magento\Framework\Option\ArrayInterface
{
    private $productAttributeRepository;
    private $criteriaBuilder;

    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository,
        SearchCriteriaBuilder $criteriaBuilder
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->criteriaBuilder = $criteriaBuilder;
    }

    public function toOptionArray()
    {
        $attributeArray = [];
        $attributes = $this->productAttributeRepository->getList($this->criteriaBuilder->create())->getItems();

        foreach ($attributes as $attribute) {
            $attributeArray[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getAttributeCode(),
            ];
        }

        return $attributeArray;
    }
}

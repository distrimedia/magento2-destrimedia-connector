<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model\Order\Pdf\Items\Invoice;

use DistriMedia\Connector\Model\Config;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Tax\Helper\Data;

class DefaultInvoice extends \Magento\Sales\Model\Order\Pdf\Items\Invoice\DefaultInvoice
{
    private $config;

    public function __construct(
        Context $context,
        Registry $registry,
        Data $taxData,
        Filesystem $filesystem,
        FilterManager $filterManager,
        StringUtils $string,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        Config $config,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $taxData,
            $filesystem,
            $filterManager,
            $string,
            $resource,
            $resourceCollection,
            $data
        );

        $this->config = $config;
    }

    public function draw()
    {
        $order = $this->getOrder();
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $lines = [];

        // draw Product name
        $lines[0] = [
            [
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                'text' => $this->string->split(html_entity_decode($item->getName()), 35, true, true),
                'feed' => 35,
            ],
        ];

        // draw SKU
        $lines[0][] = [
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            'text' => $this->string->split(html_entity_decode($this->getSku($item)), 17),
            'feed' => 200,
            'align' => 'right',
        ];

        // draw HScode
        $lines[0][] = [
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            'text' => $this->string->split(html_entity_decode($this->getHSCode($item)), 17),
            'feed' => 275,
            'align' => 'right',
        ];

        // draw CountryOrigin
        $lines[0][] = [
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            'text' => $this->string->split(html_entity_decode($this->getCountryOrigin($item)), 17),
            'feed' => 275,
            'align' => 'right',
        ];

        $netto = $this->getWeight($item);

        // draw Weight
        $lines[0][] = [
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            'text' => $this->string->split(html_entity_decode($netto), 17),
            'feed' => 380,
            'align' => 'right',
        ];

        $netto = floatval($netto);
        $bruto = $netto + ($netto * 0.0523);

        // draw Weight bruto = netto  + 5,23%
        $lines[0][] = [
            'text' =>  $this->string->split(html_entity_decode((string) $bruto), 17),
            'feed' => 440,
            'align' => 'right',
        ];

        // draw QTY
        $lines[0][] = ['text' => $item->getQty() * 1, 'feed' => 485, 'align' => 'right'];

        // draw item Prices
        $i = 0;
        $prices = $this->getItemPricesForDisplay();
        $feedPrice = 335;
        $feedSubtotal = 565;
        foreach ($prices as $priceData) {
            if (isset($priceData['label'])) {
                // draw Price label
                $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedPrice, 'align' => 'right'];
                // draw Subtotal label
                $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedSubtotal, 'align' => 'right'];
                ++$i;
            }
            // draw Price
            $lines[$i][] = [
                'text' => $priceData['price'],
                'feed' => $feedPrice,
                'font' => 'bold',
                'align' => 'right',
            ];
            // draw Subtotal
            $lines[$i][] = [
                'text' => $priceData['subtotal'],
                'feed' => $feedSubtotal,
                'font' => 'bold',
                'align' => 'right',
            ];
            ++$i;
        }

        // draw Tax
        $lines[0][] = [
            'text' => $order->formatPriceTxt($item->getTaxAmount()),
            'feed' => 530,
            'font' => 'bold',
            'align' => 'right',
        ];

        // custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = [
                    'text' => $this->string->split(
                        $this->filterManager->stripTags($option['label']),
                        40,
                        true,
                        true
                    ),
                    'font' => 'italic',
                    'feed' => 35,
                ];

                // Checking whether option value is not null
                if ($option['value'] !== null) {
                    if (isset($option['print_value'])) {
                        $printValue = $option['print_value'];
                    } else {
                        $printValue = $this->filterManager->stripTags($option['value']);
                    }
                    $values = explode(', ', $printValue);
                    foreach ($values as $value) {
                        $lines[][] = [
                            'text' => $this->string->split($value, 30, true, true),
                            'feed' => 40,
                        ];
                    }
                }
            }
        }

        $lineBlock = ['lines' => $lines, 'height' => 20];

        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $this->setPage($page);
    }

    private function getHSCode($item)
    {
        $result = '';

        /** @var OrderItemInterface $orderItem */
        $orderItem = $item->getOrderItem();

        if ($orderItem instanceof OrderItemInterface) {
            $extAttrs = $orderItem->getExtensionAttributes();
            if ($extAttrs) {
                $result = $extAttrs->getDistriMediaHsCode() ?: '';
            }
        }

        return $result;
    }

    private function getCountryOrigin($item)
    {
        $result = '';

        /** @var OrderItemInterface $orderItem */
        $orderItem = $item->getOrderItem();

        if ($orderItem instanceof OrderItemInterface) {
            $extAttrs = $orderItem->getExtensionAttributes();
            if ($extAttrs) {
                $result = $extAttrs->getDistriMediaCountryOrigin() ?: '';
            }
        }

        return $result;
    }

    private function getWeight($item): string
    {
        $result = 0;

        /** @var OrderItemInterface $orderItem */
        $orderItem = $item->getOrderItem();

        if ($orderItem instanceof OrderItemInterface) {
            $result = $orderItem->getWeight();
        }

        return $result;
    }
}

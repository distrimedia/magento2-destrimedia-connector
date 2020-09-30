<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Service;

use DistriMedia\Connector\Model\OrderFetcherInterface;
use DistriMedia\Connector\Model\ConfigInterface;
use DistriMedia\SoapClient\Struct\Order as DistriMediaOrder;
use DistriMedia\SoapClient\Struct\Customer as DistriMediaCustomer;
use DistriMedia\SoapClient\Struct\AdditionalDocument as DistriMediaDocument;
use DistriMedia\SoapClient\Struct\OrderLine as DistriMediaOrderLine;
use DistriMedia\SoapClient\Struct\Product as DistriMediaProduct;
use DistriMedia\SoapClient\Struct\OrderItem as DistriMediaOrderItem;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

use Magento\Sales\Api\Data\InvoiceInterface as MagentoInvoice;
use Magento\Sales\Model\Order\Pdf\InvoiceFactory as PdfInvoiceFactory;
use Magento\Sales\Model\Order\Pdf\Invoice as PdfInvoice;
use Magento\Sales\Api\Data\OrderInterface as MagentoOrder;

/**
 * I am responsible for converting Magento orders to DistriMedia orders
 * @package DistriMedia\Connector\Service
 */
class OrderBuilder
{
    const NUMBER_OF_INVOICES = 5;
    const INVOICE_PDF_TITLE = 'DOCUMENT';
    const BPOST_SHIPPING_METHOD_PICKUP_POINT = 'bpostpickuppoint';
    const BPOST_SHIPPING_METHOD_PARCEL_LOCKER = 'bpostparcellocker';
    const BPOST_POINT_OFFICE = 'bpost_point_office';
    const BPOST_POINT_STREET = 'bpost_point_street';
    const BPOST_POINT_NR = 'bpost_point_nr';
    const BPOST_POINT_CITY = 'bpost_point_city';
    const BPOST_POINT_ZIP = 'bpost_point_zip';

    const BPOST_SHIPPING_METHODS = [
        self::BPOST_SHIPPING_METHOD_PARCEL_LOCKER,
        self::BPOST_SHIPPING_METHOD_PICKUP_POINT
    ];

    /**
     * @var PdfInvoiceFactory
     */
    private $pdfInvoiceFactory;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ProductCollection
     */
    private $_productCollection;

    private $orderFetcher;

    public function __construct(
        PdfInvoiceFactory $pdfInvoiceFactor,
        ConfigInterface $config,
        ProductCollectionFactory $productCollectionFactory,
        OrderFetcherInterface $orderFetcher
    )
    {
        $this->pdfInvoiceFactory = $pdfInvoiceFactor;
        $this->config = $config;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->orderFetcher = $orderFetcher;
    }

    /**
     * I am responsible for converting a complete magento order into a DistriMedia Order
     * @param MagentoOrder $order
     * @return DistriMediaOrder
     */
    public function convert(MagentoOrder $order): DistriMediaOrder
    {
        $distriMediaOrder = new DistriMediaOrder();

        $customer = $this->getDistriMediaCustomerFromMagentoOrder($order);
        $distriMediaOrder->setCustomer($customer);

        $orderItem = $this->getDistriMediaOrderItemFromMagentoOrder($order);
        $distriMediaOrder->setOrderItem($orderItem);

        $orderLines = $this->getDistriMediaOrderLinesFromOrder($order);
        $distriMediaOrder->setOrderLines($orderLines);

        if ($this->config->sendInvoices()) {
            $invoices = $this->orderFetcher->getPaidInvoicesByOrder($order);
            $documents = [];

            //in magento it's possible for 1 order to have multiple invoices.
            foreach ($invoices as $invoice) {
                $invoiceDocuments = $this->getDistriMediaDocumentsfromInvoice($invoice);
                array_merge($documents, $invoiceDocuments);
            }

            $distriMediaOrder->setAdditionalDocuments($documents);
        }

        return $distriMediaOrder;
    }

    /**
     * I am responsible for creating a distriMediaCustomer from a Magento order
     * This function is public so that it can be interceptable by a pluign.
     * @param MagentoOrder $order
     * @return DistriMediaCustomer
     */
    public function getDistriMediaCustomerFromMagentoOrder(MagentoOrder $order, \Magento\Sales\Api\Data\OrderAddressInterface $billingAddress = null): DistriMediaCustomer
    {
        $distriMediaCustomer = new DistriMediaCustomer();
        $distriMediaCustomer->setEmail($order->getCustomerEmail());

        if ($billingAddress === null) {
            //We set the billing information on the customer
            $billingAddress = $order->getBillingAddress();
        }

        $streetArray = $billingAddress->getStreet() ?: [];

        //When Bpost is used, different data is required on the Customer.
        $shippingMethod = $order->getShippingMethod();
        if ($this->config->useBPostLockersAndPickup() && $this->isBpostShippingMethod($shippingMethod)) {
            $distriMediaCustomer->setName($order->getData(self::BPOST_POINT_OFFICE));
            $distriMediaCustomer->setName2($order->getCustomerFirstname() . " " . $order->getCustomerLastname());
            $bpostAddress = implode([$order->getData(self::BPOST_POINT_STREET), $order->getData(self::BPOST_POINT_NR)]);
            $distriMediaCustomer->setAddress1($bpostAddress);
            $distriMediaCustomer->setPostcode1($order->getData(self::BPOST_POINT_ZIP));
            $distriMediaCustomer->setCity($order->getData(self::BPOST_POINT_CITY));
            $distriMediaCustomer->setCountry("BE");
        } else {
            $distriMediaCustomer->setAddress1(implode(" ", $streetArray));
            $distriMediaCustomer->setName($order->getCustomerFirstname() . " " . $order->getCustomerLastname());
            $distriMediaCustomer->setPostcode1($billingAddress->getPostcode());
            $distriMediaCustomer->setCity($billingAddress->getCity());
            $distriMediaCustomer->setCountry($billingAddress->getCountryId());
        }

        //there is no difference between mobile and telephone in magento2
        $distriMediaCustomer->setTelephone($billingAddress->getTelephone());
        $distriMediaCustomer->setMobile($billingAddress->getTelephone());

        return $distriMediaCustomer;
    }

    /**
     * I am reponsible for checking wether the used shipping methdo is a bPost method
     * @param string $shippingMethod
     * @return bool
     */
    private function isBpostShippingMethod(string $shippingMethod): bool
    {
        $shippingMethod = strtolower($shippingMethod);
        foreach (self::BPOST_SHIPPING_METHODS as $item) {
            if (strpos($shippingMethod, $item) !== false) {
                return true;
            }
        }

        return false;
    }

    private function getDistriMediaOrderItemFromMagentoOrder(MagentoOrder $order): DistriMediaOrderItem
    {
        $distriMediaOrderItem = new DistriMediaOrderItem();

        //we only want the first two letters.
        $locale = $this->config->getLocaleOfStoreId((int) $order->getStoreId());
        $language = substr($locale, 0, 2);
        $distriMediaOrderItem->setLanguage($language);

        $distriMediaOrderItem->setCurrency($order->getOrderCurrencyCode());
        $distriMediaOrderItem->setOrderNumber($order->getIncrementId());

        $shippingMethod = $order->getShippingMethod();
        if ($this->config->useBPostLockersAndPickup() && $this->isBpostShippingMethod($shippingMethod)) {
            $distriMediaOrderItem->setShipmentMethod($this->getShippingMethodFromOrder($order));
        }

        return $distriMediaOrderItem;
    }

    /**
     * I am responsible for getting the shipping method from the order.
     * This is public since it might change for other clients.
     * @param MagentoOrder $order
     * @return string
     */
    public function getShippingMethodFromOrder(MagentoOrder $order): string
    {
        $description = strtolower($order->getShippingDescription());

        return $description;
    }

    /**
     * I am responsible for getting DistriMedia orderlines from a Magento order
     * @param MagentoOrder $order
     * @return DistriMediaOrderLine[]
     */
    private function getDistriMediaOrderLinesFromOrder(MagentoOrder $order): array
    {
        $orderLines = [];

        $items = $order->getItems();

        foreach ($items as $item) {
            $orderLine = new DistriMediaOrderLine();
            $product = new DistriMediaProduct();

            $m2Product = $this->getProductBySku($order, $item->getSku());

            if ($m2Product) {
                //ean code
                $eanCodeAttr = $this->config->getEanCodeAttributeCode();
                $eanCode = $m2Product->getData($eanCodeAttr);
                $product->setEan($eanCode);

                //external ref
                $externalRef = $this->config->getExternalRefAttributeCode();
                $externalRef = $m2Product->getData($externalRef);
                $product->setExternalRef($externalRef);

                $product->setDescription1($m2Product->getData('name') ?: '');
                $orderLine->setPieces((int)$item->getQtyInvoiced());
                $orderLine->setProduct($product);
                $orderLine->setProductId($eanCode);
                $orderLines[] = $orderLine;
            } else {
                throw new \Exception("Could not find product of order item with id {$item->getItemId()}");
            }
        }

        return $orderLines;
    }

    /**
     * I am responsible for building a product collection from the order
     */
    private function buildProductCollectionOfOrder(MagentoOrder $order): void
    {
        $eanCodeAttr = $this->config->getEanCodeAttributeCode();
        $externalRefAttr = $this->config->getExternalRefAttributeCode();

        $pids = [];
        $items = $order->getItems();
        foreach ($items as $item) {
            $pids[] = $item->getProductId();
        }

        $this->_productCollection = $this->productCollectionFactory->create()
            ->addAttributeToSelect($eanCodeAttr)
            ->addAttributeToSelect($externalRefAttr)
            ->addAttributeToSelect('name')
            ->addAttributeToFilter("entity_id", ['in' => $pids]);
    }

    /**
     * @param MagentoOrder $order
     * @param string $sku
     * @return \Magento\Framework\DataObject|null
     */
    public function getProductBySku(MagentoOrder $order, string $sku)
    {
        if ($this->_productCollection === null) {
            $this->buildProductCollectionOfOrder($order);
        }

        return $this->_productCollection->getItemByColumnValue('sku', $sku);
    }

    /**
     * I am responsible for converting a magento pdf invoice to a DistriMediaDocument.
     * The content is base 64 encoded
     * DistriMedia needs 5 identical documents (invoices) per order.
     * @param MagentoOrder $order
     * @return DistriMediaDocument[]
     */
    private function getDistriMediaDocumentsfromInvoice(MagentoInvoice $invoice): array
    {
        $documents = [];

        for ($i = 0; $i < self::NUMBER_OF_INVOICES; $i++) {
            /**
             * @var PdfInvoice $pdfBuilder
             */
            $pdfBuilder = $this->pdfInvoiceFactory->create();
            $pdf = $pdfBuilder->getPdf([$invoice]);
            $data = base64_encode($pdf->render());

            $document = new DistriMediaDocument();
            $document->setBinData($data);
            $document->setFileTag(self::INVOICE_PDF_TITLE);

            $documents[] = $document;
        }

        return $documents;
    }
}

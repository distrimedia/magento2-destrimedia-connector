<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Service;

use DistriMedia\Connector\Model\OrderFetcherInterface;
use DistriMedia\Connector\Model\ConfigInterface;
use DistriMedia\SoapClient\Struct\Carrier;
use DistriMedia\SoapClient\Struct\Order as DistriMediaOrder;
use DistriMedia\SoapClient\Struct\Customer as DistriMediaCustomer;
use DistriMedia\SoapClient\Struct\AdditionalDocument as DistriMediaDocument;
use DistriMedia\SoapClient\Struct\OrderItem;
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

    const CODE_PARCELLOCKER = 'bpostParcellocker';
    const CODE_PICKUPPOINT = 'bpostPickuppoint';

    private $pdfInvoiceFactory;
    private $config;
    private $orderFetcher;

    public function __construct(
        PdfInvoiceFactory $pdfInvoiceFactor,
        ConfigInterface $config,
        OrderFetcherInterface $orderFetcher
    )
    {
        $this->pdfInvoiceFactory = $pdfInvoiceFactor;
        $this->config = $config;
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
    public function getDistriMediaCustomerFromMagentoOrder(MagentoOrder $order, \Magento\Sales\Api\Data\OrderAddressInterface $shippingAddress = null): DistriMediaCustomer
    {
        $distriMediaCustomer = new DistriMediaCustomer();
        $distriMediaCustomer->setEmail($order->getCustomerEmail());

        if ($shippingAddress === null) {
            //We set the billing information on the customer
            $shippingAddress = $order->getShippingAddress();
        }

        $streetArray = $shippingAddress->getStreet() ?: [];

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
            $distriMediaCustomer->setPostcode1($shippingAddress->getPostcode());
            $distriMediaCustomer->setCity($shippingAddress->getCity());
            $distriMediaCustomer->setCountry($shippingAddress->getCountryId());
        }

        //there is no difference between mobile and telephone in magento2
        $distriMediaCustomer->setTelephone($shippingAddress->getTelephone());
        $distriMediaCustomer->setMobile($shippingAddress->getTelephone());

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

            $carrier = $this->getShippingCarrierFromOrder($order);

            if ($carrier) {
                $distriMediaOrderItem->setCarrier($carrier);
            }
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

    public function getShippingCarrierFromOrder($order): ? string
    {
        switch ($order->getShippingMethod()) {
            case self::CODE_PICKUPPOINT:
                return Carrier::CARRIER_BPPUGO;
            case self::CODE_PARCELLOCKER:
                return Carrier::CARRIER_BP247;
        }
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
            $orderItemExtAttrs = $item->getExtensionAttributes();

            //ean code
            $eanCode = $orderItemExtAttrs->getDistriMediaEanCode() ?: '';
            $product->setEan($eanCode);

            //external ref
            $externalRef = $orderItemExtAttrs->getDistriMediaExternalRef() ?: '';
            $product->setExternalRef($externalRef);

            $product->setDescription1($item->getName() ?: '');
            $orderLine->setPieces((int)$item->getQtyInvoiced());
            $orderLine->setProduct($product);
            $orderLine->setProductId($eanCode);
            $orderLines[] = $orderLine;
        }

        return $orderLines;
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

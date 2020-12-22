<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Service;

use DistriMedia\Connector\Model\Config\Source\SendInvoices;
use DistriMedia\Connector\Model\ConfigInterface;
use DistriMedia\Connector\Model\OrderFetcherInterface;
use DistriMedia\SoapClient\Struct\AdditionalDocument as DistriMediaDocument;
use DistriMedia\SoapClient\Struct\Carrier;
use DistriMedia\SoapClient\Struct\Customer as DistriMediaCustomer;
use DistriMedia\SoapClient\Struct\Order as DistriMediaOrder;
use DistriMedia\SoapClient\Struct\OrderItem as DistriMediaOrderItem;
use DistriMedia\SoapClient\Struct\OrderLine as DistriMediaOrderLine;
use DistriMedia\SoapClient\Struct\Product as DistriMediaProduct;
use Magento\Sales\Api\Data\InvoiceInterface as MagentoInvoice;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface as MagentoOrderInterface;
use Magento\Sales\Model\Order as MagentoOrder;
use Magento\Sales\Model\Order\Pdf\Invoice as PdfInvoice;
use Magento\Sales\Model\Order\Pdf\InvoiceFactory as PdfInvoiceFactory;

/**
 * I am responsible for converting Magento orders to DistriMedia orders
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
    const BPOST_POINT_ID = 'bpost_point_id';
    const BPOST_POINT_ZIP = 'bpost_point_zip';

    const BPOST_SHIPPING_METHODS = [
        self::BPOST_SHIPPING_METHOD_PARCEL_LOCKER,
        self::BPOST_SHIPPING_METHOD_PICKUP_POINT,
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
    ) {
        $this->pdfInvoiceFactory = $pdfInvoiceFactor;
        $this->config = $config;
        $this->orderFetcher = $orderFetcher;
    }

    /**
     * I am responsible for converting a complete magento order into a DistriMedia Order
     */
    public function convert(MagentoOrderInterface $order): DistriMediaOrder
    {
        $distriMediaOrder = new DistriMediaOrder();

        $customer = $this->getDistriMediaCustomerFromMagentoOrder($order);
        $distriMediaOrder->setCustomer($customer);

        $orderItem = $this->getDistriMediaOrderItemFromMagentoOrder($order);
        $distriMediaOrder->setOrderItem($orderItem);

        $orderLines = $this->getDistriMediaOrderLinesFromOrder($order);
        $distriMediaOrder->setOrderLines($orderLines);

        $this->addInvoices($distriMediaOrder, $order);

        return $distriMediaOrder;
    }

    private function addInvoices(DistriMediaOrder $distriMediaOrder, MagentoOrderInterface $order)
    {
        $sendInvoicesConfig = $this->config->sendInvoices();
        $sendInvoices = false;

        switch ($sendInvoicesConfig) {
            case SendInvoices::SEND_INVOICES_ALWAYS:
                $sendInvoices = true;
                break;
            case SendInvoices::SEND_INVOICES_ONLY_OUTSIDE_EU:
                $address = $order->getShippingAddress();
                if ($address instanceof \Magento\Sales\Model\Order\Address) {
                    $shippingCountry = $address->getCountryId();
                    $euCountries = $this->config->getEuCountries();

                    //if the country is not in the list of EU countries.
                    if (in_array($shippingCountry, $euCountries) === false) {
                        $sendInvoices = true;
                    }
                }
                break;
            default:
                break;
        }

        if ($sendInvoices === true) {
            $invoices = $this->orderFetcher->getPaidInvoicesByOrder($order);
            $documents = [];

            //in magento it's possible for 1 order to have multiple invoices.
            foreach ($invoices as $invoice) {
                $invoiceDocuments = $this->getDistriMediaDocumentsfromInvoice($invoice);
                $documents = array_merge($documents, $invoiceDocuments);
            }

            $distriMediaOrder->setAdditionalDocuments($documents);
        }
    }

    /**
     * I am responsible for creating a distriMediaCustomer from a Magento order
     * This function is public so that it can be interceptable by a pluign.
     * @param MagentoOrder $order
     * @param OrderAddressInterface|null $shippingAddress
     * @return DistriMediaCustomer
     */
    public function getDistriMediaCustomerFromMagentoOrder(
        MagentoOrder $order,
        OrderAddressInterface $shippingAddress = null
    ): DistriMediaCustomer {
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
            $distriMediaCustomer->setName2($order->getCustomerFirstname() . ' ' . $order->getCustomerLastname());
            $bpostAddress = implode([$order->getData(self::BPOST_POINT_STREET), $order->getData(self::BPOST_POINT_NR)]);
            $distriMediaCustomer->setAddress1($bpostAddress);
            $distriMediaCustomer->setPostcode1($order->getData(self::BPOST_POINT_ZIP));
            $distriMediaCustomer->setCity($order->getData(self::BPOST_POINT_CITY));
            $distriMediaCustomer->setCountry('BE');
            $servicePoint = $order->getData(self::BPOST_POINT_ID);
            $distriMediaCustomer->setServicePoint($servicePoint);
        } else {
            $address = implode(' ', $streetArray);

            $addressArray = str_split($address, 40);

            $distriMediaCustomer->setAddress1($addressArray[0]);

            //overflow of the address
            if (count($addressArray) > 1) {
                $distriMediaCustomer->setAddress2($addressArray[1]);
            }

            $distriMediaCustomer->setName($order->getCustomerFirstname() . ' ' . $order->getCustomerLastname());
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

    private function getDistriMediaOrderItemFromMagentoOrder(MagentoOrderInterface $order): DistriMediaOrderItem
    {
        $distriMediaOrderItem = new DistriMediaOrderItem();

        //we only want the first two letters.
        $locale = $this->config->getLocaleOfStoreId((int) $order->getStoreId());
        $language = substr($locale, 0, 2);
        $distriMediaOrderItem->setLanguage($language);

        $distriMediaOrderItem->setCurrency($order->getOrderCurrencyCode());
        $distriMediaOrderItem->setOrderNumber($order->getIncrementId());
        $distriMediaOrderItem->setReferenceNumber($order->getIncrementId());

        $siteIndiation = $this->config->getSiteIndication() ?: '';

        $distriMediaOrderItem->setSiteIndication($siteIndiation);

        $shippingMethod = $order->getShippingMethod();
        if ($this->config->useBPostLockersAndPickup() && $this->isBpostShippingMethod($shippingMethod)) {
            $carrier = $this->getShippingCarrierFromShippingMethod($shippingMethod);

            //we currently set the carrier in the shipping method as well..
            //Magento uses a shipping method that is too long.
            $distriMediaOrderItem->setShipmentMethod($carrier);

            if ($carrier) {
                $distriMediaOrderItem->setCarrier($carrier);
            }
        }

        if ($this->config->useCancellationDays()) {
            $distriMediaOrderItem->setDaysOfCancellation($this->config->getCancellationDays());
        }

        if ($this->config->useRetentionDays()) {
            $distriMediaOrderItem->setDaysOfRetention($this->config->getRetentionDays());
        }

        return $distriMediaOrderItem;
    }

    /**
     * I am responsible for getting the shipping method from the order.
     * This is public since it might change for other clients.
     */
    public function getShippingMethodFromOrder(MagentoOrderInterface $order): string
    {
        $shippingMethod = '';

        $shippingMethodData = explode('_', $order->getShippingMethod());
        if (!empty($shippingMethodData)) {
            $shippingMethod = reset($shippingMethodData);
        }

        return $shippingMethod;
    }

    public function getShippingCarrierFromShippingMethod(string $shippingMethod): ? string
    {
        $shippingMethod = strtolower($shippingMethod);
        $shippingMethod = explode('_', $shippingMethod);
        $shippingMethod = reset($shippingMethod);

        $result = '';

        switch ($shippingMethod) {
            case self::BPOST_SHIPPING_METHOD_PICKUP_POINT:
                $result = Carrier::CARRIER_BPPUGO;
                break;
            case self::BPOST_SHIPPING_METHOD_PARCEL_LOCKER:
                $result = Carrier::CARRIER_BP247;
                break;
        }

        return $result;
    }

    /**
     * I am responsible for getting DistriMedia orderlines from a Magento order
     * @return DistriMediaOrderLine[]
     */
    private function getDistriMediaOrderLinesFromOrder(MagentoOrderInterface $order): array
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
            $orderLine->setPieces((int) $item->getQtyInvoiced());
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
     * @param MagentoOrderInterface $order
     * @return DistriMediaDocument[]
     */
    private function getDistriMediaDocumentsfromInvoice(MagentoInvoice $invoice): array
    {
        $invoices = [];

        for ($i = 0; $i < self::NUMBER_OF_INVOICES; ++$i) {
            $invoices[] = $invoice;
        }

        /**
         * @var PdfInvoice $pdfBuilder
         */
        $pdfBuilder = $this->pdfInvoiceFactory->create();
        $pdf = $pdfBuilder->getPdf($invoices);
        $data = base64_encode($pdf->render());

        $document = new DistriMediaDocument();
        $document->setBinData($data);
        $document->setFileTag(self::INVOICE_PDF_TITLE);

        $documents = [$document];

        return $documents;
    }
}

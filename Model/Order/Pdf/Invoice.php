<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Model\Order\Pdf;

use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Sales\Model\Order\Pdf\Config;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection;

/**
 * Use built-in fonts in PDFs so that invoices are smaller.
 */
class Invoice extends \Magento\Sales\Model\Order\Pdf\Invoice
{
    const CONFIG_SIGNATURE_PATH = 'distrimedia/settings/signature';

    /**
     * @var Database
     */
    protected $fileStorageDatabase;

    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        Config $pdfConfig,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        Database $fileStorageDatabase,
        array $data = []
    ) {
        $this->fileStorageDatabase = $fileStorageDatabase;

        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $storeManager,
            $localeResolver,
            $data
        );
    }

    protected function _setFontRegular($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA);
        $object->setFont($font, 5);

        return $font;
    }

    protected function _setFontBold($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $object->setFont($font, 5);

        return $font;
    }

    protected function _setFontItalic($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
        $object->setFont($font, 5);

        return $font;
    }

    /**
     * DistriMedia needs some extra columns, so we overwrite the core functionality.
     *
     * @param \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('Products'), 'feed' => 35, 'font_size' => 7];

        $lines[0][] = ['text' => __('SKU'), 'feed' => 170, 'align' => 'right', 'font_size' => 7];

        //Custom DistriMedia
        $lines[0][] = ['text' => __('HScode'), 'feed' => 220, 'align' => 'right', 'font_size' => 7];

        $lines[0][] = ['text' => __('CountryOrigin'), 'feed' => 300, 'align' => 'right', 'font_size' => 7];

        $lines[0][] = ['text' => __('Weight'), 'feed' => 380, 'align' => 'right', 'font_size' => 7];

        $lines[0][] = ['text' => __('Incoterms'), 'feed' => 445, 'align' => 'right', 'font_size' => 7];
        //End Custom DistriMedia

        $lines[0][] = ['text' => __('Qty'), 'feed' => 485, 'align' => 'right', 'font_size' => 7];

        $lines[0][] = ['text' => __('Price'), 'feed' => 320, 'align' => 'right', 'font_size' => 7];

        $lines[0][] = ['text' => __('Tax'), 'feed' => 510, 'align' => 'right', 'font_size' => 7];

        $lines[0][] = ['text' => __('Subtotal'), 'feed' => 555, 'align' => 'right', 'font_size' => 7];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    /**
     * Return PDF document
     *
     * @param array|Collection $invoices
     * @return \Zend_Pdf
     */
    public function getPdf($invoices = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                $this->_localeResolver->emulate($invoice->getStoreId());
                $this->_storeManager->setCurrentStore($invoice->getStoreId());
            }
            $page = $this->newPage();
            $order = $invoice->getOrder();
            /* Add image */
            $this->insertLogo($page, $invoice->getStore());
            /* Add address */
            $this->insertAddress($page, $invoice->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );

            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Invoice # ') . $invoice->getIncrementId());

            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($invoice->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            /* Add totals */
            $this->insertTotals($page, $invoice);
            if ($invoice->getStoreId()) {
                $this->_localeResolver->revert();
            }
            $this->insertFootnote($page);

            /* Add signature */
            $style = new \Zend_Pdf_Style();
            $this->_setFontBold($style, 10);
            $this->insertSignature($page, $invoice->getStore());
        }

        $this->_afterGetPdf();

        return $pdf;
    }

    /**
     * Insert logo to pdf page
     *
     * @param \Zend_Pdf_Page $page
     * @param string|null $store
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Zend_Pdf_Exception
     */
    protected function insertSignature(\Zend_Pdf_Page $page, $store = null)
    {
        $image = $this->_scopeConfig->getValue(
            self::CONFIG_SIGNATURE_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($image) {
            $imagePath = '/distrimedia/settings/signature/' . $image;
            if ($this->fileStorageDatabase->checkDbUsage() &&
                !$this->_mediaDirectory->isFile($imagePath)
            ) {
                $this->fileStorageDatabase->saveFileToFilesystem($imagePath);
            }
            if ($this->_mediaDirectory->isFile($imagePath)) {
                $absPath = $this->_mediaDirectory->getAbsolutePath($imagePath);

                $image = \Zend_Pdf_Image::imageWithPath($absPath);
                $this->y -= 15;

                $top = $this->y;
                //top border of the page
                $widthLimit = 270;
                //half of the page width
                $heightLimit = 270;
                //assuming the image is not a "skyscraper"
                $width = $image->getPixelWidth();
                $height = $image->getPixelHeight();

                //preserving aspect ratio (proportions)
                $ratio = $width / $height;
                if ($ratio > 1 && $width > $widthLimit) {
                    $width = $widthLimit;
                    $height = $width / $ratio;
                } elseif ($ratio < 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $height * $ratio;
                } elseif ($ratio == 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $widthLimit;
                }

                $y1 = $top - $height;
                $y2 = $top;
                $x1 = 25;
                $x2 = $x1 + $width;

                //coordinates after transformation are rounded by Zend
                $page->drawImage($image, $x1, $y1, $x2, $y2);

                $this->y = $y1 - 10;
            }
        }

        return $page;
    }

    /**
     * @param \Zend_Pdf_Page $page
     */
    private function insertFootnote(\Zend_Pdf_Page $page)
    {
        $text = '------
Declaration of origin
The exporter of the products (authorized exporter, custom authorization number) covered by
this document declared that except where otherwise clearly indicated, these products are of EU preferential origing
------';

        $docHeader = $this->getDocHeaderCoordinates();

        $this->y -= 15;

        $this->_setFontRegular($page, 9);

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

        $page->drawText(
            __($text),
            35,
            $this->y,
            'UTF-8'
        );

        $this->y -= 15;

        return $page;
    }
}

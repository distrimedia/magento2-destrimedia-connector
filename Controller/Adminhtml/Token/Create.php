<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace DistriMedia\Connector\Controller\Adminhtml\Token;

use DistriMedia\Connector\Helper\TokenBuilder;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action;

class Create extends Action implements HttpGetActionInterface
{
    const ADMIN_RESOURCE = 'DistriMedia_Connector::settings';

    private $tokenBuilder;

    public function __construct(
        Context $context,
        TokenBuilder $tokenBuilder
    ) {
        $this->tokenBuilder = $tokenBuilder;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->tokenBuilder->createToken();

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('adminhtml/system_config/edit/section/distrimedia');
        return $resultRedirect;
    }
}

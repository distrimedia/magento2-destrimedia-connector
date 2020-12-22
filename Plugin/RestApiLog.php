<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Webapi\Controller\Rest;
use Psr\Log\LoggerInterface;

/**
 * I am responsible for logging all DistriMedia push requests
 * Class RestApiLog
 */
class RestApiLog
{
    private $logger;

    const PUSH_REQUEST_ENDPOINTS = [
        '/rest/V1/distrimedia/stock/change',
        '/rest/V1/distrimedia/order/change',
    ];

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function beforeDispatch(
        Rest $subject,
        RequestInterface $request
    ) {
        foreach (self::PUSH_REQUEST_ENDPOINTS as $endpoint) {
            if (strpos($request->getPathInfo(), $endpoint) !== false) {
                $this->logger->info('SOURCE: ' . $request->getClientIp());
                $this->logger->info('METHOD: ' . $request->getMethod());
                $this->logger->info('PATH: ' . $request->getPathInfo());
                $this->logger->info('CONTENT: ' . $request->getContent() . PHP_EOL);
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Plugin;

use Psr\Log\LoggerInterface;
use Magento\Webapi\Controller\Rest;
use Magento\Framework\App\RequestInterface;

/**
 * I am responsible for logging all DistriMedia push requests
 * Class RestApiLog
 * @package DistriMedia\Connector\Plugin
 */
class RestApiLog
{

    private $logger;

    const PUSH_REQUESTS = [
        '/rest/V1/distrimedia/stock/change',
        '/rest/V1/distrimedia/order/change'
    ];

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function beforeDispatch(
        Rest $subject,
        RequestInterface $request
    )
    {
        if (strpos($request->getPathInfo(), self::PAAZL_URI) !== false) {
            $this->logger->info('SOURCE: ' . $request->getClientIp());
            $this->logger->info('METHOD: ' . $request->getMethod());
            $this->logger->info('PATH: ' . $request->getPathInfo());
            $this->logger->info('CONTENT: ' . $request->getContent() . PHP_EOL);
        }
    }
}

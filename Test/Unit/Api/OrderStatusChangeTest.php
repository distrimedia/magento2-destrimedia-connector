<?php

declare(strict_types=1);

namespace DistriMedia\Connector\Test\Unit\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;

class OrderStatusChangeTest extends WebapiAbstract
{

    /**
     * @covers \DistriMedia\Connector\Model\OrderStatusChangeManagement
     */
    public function testExecute()
    {
        $itemId = 1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/distrimedia/order/change/',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ]
        ];
        $requestData = ['itemId' => $itemId];
        $item = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals('testProduct1', $item['name'], "Item was retrieved unsuccessfully");
    }
}

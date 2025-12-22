<?php

namespace Xsolve\SalesforceClient\Client;

use GuzzleHttp\Psr7\Request;
use Http\Client\Exception\HttpException;
use Http\Client\HttpClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Xsolve\SalesforceClient\Enum\RequestMethod;
use Xsolve\SalesforceClient\Generator\TokenGeneratorInterface;
use Xsolve\SalesforceClient\Request\RequestInterface;
use Xsolve\SalesforceClient\Security\Token\TokenInterface;

class SalesforceClientTest extends TestCase
{
    /**
     * @var MockObject|HttpClient
     */
    protected $httpClient;

    /**
     * @var SalesforceClient
     */
    protected $salesforceClient;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $token = $this->createMock(TokenInterface::class);

        $tokenManager = $this->createMock(TokenGeneratorInterface::class);
        $tokenManager->method('getToken')->willReturn($token);
        $tokenManager->method('regenerateToken')->willReturn($token);

        $this->httpClient = $this->createMock(HttpClient::class);

        $this->salesforceClient = new SalesforceClient($this->httpClient, $tokenManager, 'v00.0');
    }

    public function testExpiredTokenFlow()
    {
        $this->httpClient->expects($this->exactly(2))
            ->method('sendRequest')
            ->willReturnCallback(function () {
                static $count = 0;
                if ($count++ === 0) {
                    throw $this->getExceptionMock(401);
                }
                return $this->createMock(ResponseInterface::class);
            });

        $request = $this->createMock(RequestInterface::class);
        $request->method('getMethod')->willReturn(RequestMethod::GET());
        $response = $this->salesforceClient->doRequest($request);

        $this->assertIsArray($response);
    }

    public function testInvalidToken()
    {
        $this->expectException(\Http\Client\Exception\HttpException::class);
        $this->httpClient->method('sendRequest')->will($this->throwException($this->getExceptionMock(404)));

        $request = $this->createMock(RequestInterface::class);
        $request->method('getMethod')->willReturn(RequestMethod::GET());
        $this->salesforceClient->doRequest($request);
    }

    public function testValidToken()
    {
        $this->httpClient->method('sendRequest')->willReturn($this->createMock(ResponseInterface::class));
        $request = $this->createMock(RequestInterface::class);
        $request->method('getMethod')->willReturn(RequestMethod::GET());
        $response = $this->salesforceClient->doRequest($request);

        $this->assertIsArray($response);
    }

    protected function getExceptionMock(int $code): HttpException
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($code);

        return HttpException::create($this->createMock(Request::class), $response);
    }
}

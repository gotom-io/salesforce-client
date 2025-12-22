<?php

namespace Xsolve\SalesforceClient\Security\Authentication;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Http\Client\HttpClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Xsolve\SalesforceClient\Security\Authentication\Strategy\RegenerateStrategyInterface;
use Xsolve\SalesforceClient\Security\Token\TokenInterface;

class AuthenticatorTest extends TestCase
{
    /**
     * @var Authenticator
     */
    protected $authenticator;

    /**
     * @var MockObject|HttpClient
     */
    protected $client;

    /**
     * @var MockObject|Credentials
     */
    protected $credentials;

    /**
     * @var MockObject|ResponseInterface
     */
    protected $response;

    /**
     * @var MockObject|RegenerateStrategyInterface
     */
    protected $regenerateStrategy;

    /**
     * @var MockObject|TokenInterface
     */
    protected $token;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->client = $this->createMock(HttpClient::class);
        $this->credentials = $this->createMock(Credentials::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->regenerateStrategy = $this->createMock(RegenerateStrategyInterface::class);
        $this->token = $this->createMock(TokenInterface::class);

        $this->client->method('sendRequest')->willReturn($this->response);
        $this->authenticator = new Authenticator($this->client, [$this->regenerateStrategy]);
    }

    public function testEmptyResponse()
    {
        $this->expectException(\Xsolve\SalesforceClient\Security\Authentication\Exception\InvalidAuthenticationResponseException::class);
        $this->authenticator->authenticate($this->credentials);
    }

    public function testBadResponse()
    {
        $this->expectException(\Xsolve\SalesforceClient\Security\Authentication\Exception\InvalidAuthenticationResponseException::class);
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn('["key"]');
        $this->response->method('getBody')->willReturn($stream);

        $this->authenticator->authenticate($this->credentials);
    }

    public function testValidResponse()
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn($this->getValidResponse());
        $this->response->method('getBody')->willReturn($stream);

        $token = $this->authenticator->authenticate($this->credentials);

        $this->assertInstanceOf(TokenInterface::class, $token);

        $this->assertEquals($token->getAccessToken(), 'Token');
        $this->assertEquals($token->getTokenType(), 'Type');
        $this->assertEquals($token->getInstanceUrl(), 'http://example.com/');
        $this->assertEquals($token->getRefreshToken(), '');
    }

    public function testRegenerateStrategyNotFound()
    {
        $this->expectException(\Xsolve\SalesforceClient\Security\Authentication\Exception\UnsupportedCredentialsException::class);
        $this->regenerateStrategy->method('supports')->willReturn(false);

        $this->authenticator->regenerate($this->credentials, $this->token);
    }

    public function testRegenerateExistingStrategy()
    {
        $this->regenerateStrategy->method('supports')->willReturn(true);
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn($this->getValidResponse());
        $this->response->method('getBody')->willReturn($stream);

        $token = $this->authenticator->regenerate($this->credentials, $this->token);

        $this->assertInstanceOf(TokenInterface::class, $token);
    }

    protected function getValidResponse(): string
    {
        return '{"token_type":"Type","access_token":"Token","instance_url":"http:\/\/example.com\/"}';
    }
}

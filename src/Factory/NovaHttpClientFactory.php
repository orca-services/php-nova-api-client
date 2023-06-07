<?php

namespace OrcaServices\NovaApi\Factory;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use InvalidArgumentException;
use OrcaServices\NovaApi\Client\NovaHttpClient;
use OrcaServices\NovaApi\Configuration\NovaApiConfiguration;
use OrcaServices\NovaApi\Method\NovaLoginMethod;
use OrcaServices\NovaApi\Parser\NovaApiErrorParser;
use Psr\Http\Message\ResponseInterface;

/**
 * NOVA HTTP Client Factory.
 */
final class NovaHttpClientFactory
{
    /**
     * @var NovaApiConfiguration Nova settings
     */
    private $configuration;

    /**
     * @var NovaApiErrorParser
     */
    private $novaErrorParser;

    /**
     * @var array
     */
    private $mockedResponses = [];

    /**
     * @var HandlerStack|null
     */
    private $handler;

    /**
     * The constructor.
     *
     * @param NovaApiConfiguration $configuration The settings
     * @param NovaApiErrorParser $novaErrorParser The error parser
     */
    public function __construct(
        NovaApiConfiguration $configuration,
        NovaApiErrorParser $novaErrorParser
    ) {
        $this->configuration = $configuration;
        $this->novaErrorParser = $novaErrorParser;
    }

    /**
     * Create a http client with logged-in session.
     *
     * @return NovaHttpClient The SOAP client
     */
    public function createLoggedInHttpClient(): NovaHttpClient
    {
        // Create a guzzle client for the single sign on server.
        $novaSsoSettings = $this->configuration->getWebServiceSsoClientSettings();
        $ssoHttpClient = $this->createHttpClient($novaSsoSettings);

        $username = $novaSsoSettings['client_id'];
        $password = $novaSsoSettings['client_secret'];

        $loginMethod = new NovaLoginMethod($ssoHttpClient, $this->novaErrorParser);

        $accessToken = $loginMethod->login($username, $password);
        $authorization = sprintf('Bearer %s', $accessToken);

        // Create the soap webservice client
        $webServiceSettings = $this->configuration->getWebServiceClientSettings();
        $webServiceSettings = array_replace_recursive(
            $webServiceSettings,
            [
                'headers' => ['Authorization' => $authorization],
            ]
        );

        return $this->createHttpClient($webServiceSettings);
    }

    /**
     * Create a guzzle client for the API to use.
     *
     * @param array $settings The settings
     *
     * @throws InvalidArgumentException
     *
     * @return NovaHttpClient The http client
     */
    private function createHttpClient(array $settings): NovaHttpClient
    {
        if ($this->mockedResponses || empty($settings['base_uri']) || $settings['base_uri'] === 'http://localhost/') {
            // Use the same mocked handler stack for login and the endpoint client for testing
            $this->handler = $this->handler ?: HandlerStack::create(new MockHandler($this->mockedResponses));
            $settings['base_uri'] = 'http://localhost';
            $settings['handler'] = $this->handler;
        }

        return new NovaHttpClient($settings);
    }

    /**
     * Set mocked responses.
     *
     * @param ResponseInterface[] $responses The responses
     *
     * @return void
     */
    public function setMockedResponses(array $responses)
    {
        $this->mockedResponses = $responses;
    }
}

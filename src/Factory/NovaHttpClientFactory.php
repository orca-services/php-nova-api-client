<?php

namespace OrcaServices\NovaApi\Factory;

use InvalidArgumentException;
use OrcaServices\NovaApi\Client\NovaHttpClient;
use OrcaServices\NovaApi\Configuration\NovaApiConfiguration;
use OrcaServices\NovaApi\Method\NovaLoginMethod;
use OrcaServices\NovaApi\Parser\NovaApiErrorParser;

/**
 * Factory.
 */
class NovaHttpClientFactory
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
     *  Create a guzzle client for the API to use.
     *
     * @param array $settings The settings
     *
     * @throws InvalidArgumentException
     *
     * @return NovaHttpClient The http client
     */
    private function createHttpClient(array $settings): NovaHttpClient
    {
        if (empty($settings['base_uri'])) {
            throw new InvalidArgumentException('The NOVA API base URI is not defined');
        }

        return new NovaHttpClient($settings);
    }
}

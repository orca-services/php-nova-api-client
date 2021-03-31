<?php

namespace OrcaServices\NovaApi\Test\Traits;

use Cake\Chronos\Chronos;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use OrcaServices\NovaApi\Client\NovaApiClient;
use OrcaServices\NovaApi\Configuration\NovaApiConfiguration;
use OrcaServices\NovaApi\Parameter\NovaIdentifierParameter;

/**
 * Trait.
 */
trait NovaClientTestTrait
{
    /**
     * Create instance.
     *
     * @param array $responses The mocked responses
     *
     * @return NovaApiClient The instance
     */
    protected function createNovaApiClient(array $responses): NovaApiClient
    {
        Chronos::setTestNow('2019-09-01 00:00:00');

        $settings = $this->getSettings();

        // To make real http calls, just comment out this line
        $settings = $this->mockNovaGuzzleClient($settings, $responses);

        $this->getContainer()->set(NovaApiConfiguration::class, new NovaApiConfiguration($settings));

        return $this->getContainer()->get(NovaApiClient::class);
    }

    /**
     * Mock NOVA Guzzle client and single sign on (SSO).
     *
     * @param array $settings The nova api settings
     * @param array $responses The mocked responses
     *
     * @return array
     */
    protected function mockNovaGuzzleClient(array $settings, array $responses): array
    {
        // Append the login as first response
        $loginResponse = new Response();
        $loginResponse->getBody()->write(
            (string)file_get_contents(__DIR__ . '/../Ressources/Response/Client/LoginResponse.json')
        );

        array_unshift($responses, $loginResponse);

        $settings['default']['handler'] = HandlerStack::create(new MockHandler($responses));

        return $settings;
    }

    /**
     * Returns the default settings.
     *
     * @return array
     */
    protected function getSettings(): array
    {
        $filename = file_exists(__DIR__ . '/../config.php')
            ? '/../config.php'
            : '/../config.php.dist';

        return include __DIR__ . $filename;
    }

    /**
     * Set identifier.
     *
     * @param NovaIdentifierParameter $parameter The params
     *
     * @return void
     */
    private function setIdentifier(NovaIdentifierParameter $parameter)
    {
        $settings = $this->getSettings()['client'];
        $parameter->correlationId = $settings['correlation_id'];
        $parameter->serviceAgent = $settings['service_agent'];
        $parameter->channelCode = $settings['channel_code'];
        $parameter->pointOfSale = $settings['point_of_sale'];
        $parameter->distributionPoint = $settings['distribution_point'];
        $parameter->saleDeviceId = $settings['sale_device_id'];
    }

    /**
     * Create a mocked response queue.
     *
     * @param array $files The source files
     *
     * @return Response[] The responses
     */
    protected function createResponses(array $files): array
    {
        $responses = [];

        foreach ($files as $file) {
            // Create a mocked response queue
            $response = new Response();
            $response->getBody()->write((string)file_get_contents($file));
            $responses[] = $response;
        }

        return $responses;
    }
}

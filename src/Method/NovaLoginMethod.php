<?php

namespace OrcaServices\NovaApi\Method;

use Exception;
use GuzzleHttp\Client;
use OrcaServices\NovaApi\Exception\NovaApiUnauthorizedException;
use OrcaServices\NovaApi\Parser\NovaApiErrorParser;
use UnexpectedValueException;

/**
 * SOAP method.
 */
final class NovaLoginMethod implements NovaMethod
{
    /**
     * @var NovaApiErrorParser
     */
    private $novaApiErrorParser;

    /**
     * @var Client
     */
    private $client;

    /**
     * NovaLoginMethod constructor.
     *
     * @param Client $client The client
     * @param NovaApiErrorParser $novaApiErrorParser The error handler
     */
    public function __construct(Client $client, NovaApiErrorParser $novaApiErrorParser)
    {
        $this->client = $client;
        $this->novaApiErrorParser = $novaApiErrorParser;
    }

    /**
     * Request an authentication token.
     *
     * The authentication token must be transferred with every webservice request in
     * the HTTP header (Cookie: SAML-Ticket=<authentication token>).
     *
     * https://confluence-ext.sbb.ch/display/NOVAUG/Authentication+and+Authorization+via+SAML
     *
     * @param string $clientId The WSG credential username
     * @param string $clientSecret The WSG credential password
     *
     * @throws NovaApiUnauthorizedException
     * @throws UnexpectedValueException
     *
     * @return string the authentication The authentication token returned by the login call
     */
    public function login(string $clientId, string $clientSecret): string
    {
        $options = [
            'body' => http_build_query(
                [
                    'grant_type' => 'client_credentials',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                ]
            ),
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ];

        try {
            $response = $this->client->request(
                'POST',
                '/auth/realms/SBB_Public/protocol/openid-connect/token',
                $options
            );

            $body = (string)$response->getBody();

            if (strpos($body, '{') === false) {
                throw new UnexpectedValueException(
                    'Oauth2 authentication failed. Invalid json response. Access token not found.'
                );
            }

            $result = json_decode($body, true);

            return (string)$result['access_token'];
        } catch (Exception $ex) {
            $message = $this->novaApiErrorParser->getExceptionMessage($ex);

            if ($ex->getCode() === 401) {
                throw new NovaApiUnauthorizedException($message, $ex->getCode(), $ex);
            }

            throw new UnexpectedValueException($message, $ex->getCode(), $ex);
        }
    }
}

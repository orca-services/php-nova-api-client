<?php

namespace OrcaServices\NovaApi\Test\Response\Client;

use GuzzleHttp\Psr7\Response;

/**
 * Response.
 */
final class LoginTestResponse extends Response
{
    /**
     * The constructor.
     */
    public function __construct()
    {
        $body = (string)file_get_contents(__DIR__ . '/../../Resources/Response/Client/LoginResponse.json');

        parent::__construct(200, [], $body);
    }
}

<?php

namespace OrcaServices\NovaApi\Test\Response\Client;

use GuzzleHttp\Psr7\Response;

/**
 * Response.
 */
final class SearchPartnerTestResponse extends Response
{
    /**
     * The constructor.
     */
    public function __construct()
    {
        $body = (string)file_get_contents(__DIR__ . '/../../Resources/Response/Client/SearchPartnerResponse.xml');

        parent::__construct(200, ['Content-Type' => 'text/xml'], $body);
    }
}

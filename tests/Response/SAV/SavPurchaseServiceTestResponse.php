<?php

namespace OrcaServices\NovaApi\Test\Response\SAV;

use GuzzleHttp\Psr7\Response;

/**
 * Response.
 */
final class SavPurchaseServiceTestResponse extends Response
{
    /**
     * The constructor.
     */
    public function __construct()
    {
        $body = (string)file_get_contents(__DIR__ . '/../../Resources/Response/SAV/PurchaseServiceResponse.xml');

        parent::__construct(200, ['Content-Type' => 'text/xml'], $body);
    }
}

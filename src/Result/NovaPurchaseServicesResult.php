<?php

namespace OrcaServices\NovaApi\Result;

/**
 * Data.
 */
final class NovaPurchaseServicesResult
{
    use NovaMessageTrait;

    /**
     * Services.
     *
     * @var NovaServiceItem[]
     */
    public $services = [];
}

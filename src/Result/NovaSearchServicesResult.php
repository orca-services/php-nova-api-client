<?php

namespace OrcaServices\NovaApi\Result;

/**
 * NovaSearchServicesResult.
 */
final class NovaSearchServicesResult
{
    use NovaMessageTrait;

    /**
     * Services.
     *
     * @var NovaServiceResult[]
     */
    public $services = [];
}

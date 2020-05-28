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

    /**
     * Add service.
     *
     * @param NovaServiceResult $service The nova service
     *
     * @return void
     */
    public function addService(NovaServiceResult $service)
    {
        $this->services[] = $service;
    }
}

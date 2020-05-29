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
    private $services = [];

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

    /**
     * Get services.
     *
     * @return array|NovaServiceResult[] The items
     */
    public function getServices(): array
    {
        return $this->services;
    }
}

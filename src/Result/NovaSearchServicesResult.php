<?php

namespace OrcaServices\NovaApi\Result;

final class NovaSearchServicesResult
{
    use NovaMessageTrait;

    /**
     * Services
     *
     * @var NovaService[]
     */
    public $services = [];

    /**
     * Add service.
     *
     * @param NovaService $service The nova service
     *
     * @return void
     */
    public function addService(NovaService $service)
    {
        $this->services[] = $service;
    }
}

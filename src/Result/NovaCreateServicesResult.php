<?php

namespace OrcaServices\NovaApi\Result;

/**
 * Data.
 */
final class NovaCreateServicesResult
{
    use NovaMessageTrait;

    /**
     * Services.
     *
     * @var NovaServiceItem[]
     */
    public $services = [];

    /**
     * Add service.
     *
     * @param NovaServiceItem $novaService The nova service
     *
     * @return void
     */
    public function addService(NovaServiceItem $novaService)
    {
        $this->services[] = $novaService;
    }
}

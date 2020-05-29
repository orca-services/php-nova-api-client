<?php

namespace OrcaServices\NovaApi\Result;

/**
 * Data.
 */
final class NovaCreateReceiptsResult
{
    use NovaMessageTrait;

    /**
     * Services.
     *
     * @var NovaServiceItem[]
     */
    private $services = [];

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

    /**
     * Get services.
     *
     * @return array|NovaServiceItem[] The items
     */
    public function getServices(): array
    {
        return $this->services;
    }
}

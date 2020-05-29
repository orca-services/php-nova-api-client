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
     * Get services.
     *
     * @return array|NovaServiceItem[] The items
     */
    public function getServices(): array
    {
        return $this->services;
    }
}

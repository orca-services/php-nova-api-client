<?php

namespace OrcaServices\NovaApi\Result;

use Cake\Chronos\Chronos;

/**
 * NovaServiceResult
 */
final class NovaServiceResult
{
    /**
     * Tkid
     *
     * @var string
     */
    public $tkId;

    /**
     * Valid start date
     *
     * @var Chronos
     */
    public $validFrom;

    /**
     * Valid end date
     *
     * @var Chronos
     */
    public $validTo;

    /**
     * Product number
     *
     * @var string
     */
    public $productNumber;

    /**
     * Restricted to zones
     *
     * @var string[]
     */
    public $zones = [];

    /**
     * Add zones.
     *
     * @param string $zone The zone
     *
     * @return void
     */
    public function addZone(string $zone)
    {
        $this->zones[] = $zone;
    }
}

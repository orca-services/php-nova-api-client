<?php

namespace OrcaServices\NovaApi\Result;

use Cake\Chronos\Chronos;

final class NovaService
{
    /**
     * @var string
     */
    public $tkId;

    /**
     * @var Chronos
     */
    public $validFrom;

    /**
     * @var Chronos
     */
    public $validTo;

    /**
     * @var string
     */
    public $productNumber;

    /**
     * Zones
     *
     * @var string[]
     */
    public $zones = [];
}

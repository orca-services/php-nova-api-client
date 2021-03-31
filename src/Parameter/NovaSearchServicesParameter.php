<?php

namespace OrcaServices\NovaApi\Parameter;

use Cake\Chronos\Chronos;

/**
 * NovaSearchServicesParameter.
 */
class NovaSearchServicesParameter extends NovaIdentifierParameter
{
    /**
     * NOVA / SBB customer ID (uuid).
     *
     * @var string
     */
    public $tkId;

    /**
     * Start date of the use period.
     *
     * @var Chronos|null
     */
    public $periodOfUseStart;

    /**
     * End date of the use period.
     *
     * @var Chronos|null
     */
    public $periodOfUseEnd;

    /**
     * The leistungsId.
     *
     * @var string|null
     */
    public $serviceId;
}

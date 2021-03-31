<?php

namespace OrcaServices\NovaApi\Parameter;

/**
 * Data.
 */
final class NovaSavCreateOffersParameter extends NovaIdentifierParameter
{
    /**
     * NOVA service ID.
     *
     * @var string
     */
    public $serviceId = '';

    /**
     * NOVA service ID.
     *
     * See: NovaSavReasonType.
     *
     * @var string
     */
    public $reason = '';
}

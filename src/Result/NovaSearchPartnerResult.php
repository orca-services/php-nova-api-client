<?php

namespace OrcaServices\NovaApi\Result;

/**
 * Data.
 */
final class NovaSearchPartnerResult
{
    use NovaMessageTrait;

    /**
     * Partners.
     *
     * @var NovaPartner[]
     */
    public $partners = [];
}

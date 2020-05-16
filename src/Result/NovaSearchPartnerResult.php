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

    /**
     * Add partner.
     *
     * @param NovaPartner $novaPartner The nova partner (customer)
     *
     * @return void
     */
    public function addPartner(NovaPartner $novaPartner)
    {
        $this->partners[] = $novaPartner;
    }
}

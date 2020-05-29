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
    private $partners = [];

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

    /**
     * Get list of partners.
     *
     * @return NovaPartner[] The list of partners
     */
    public function getPartners()
    {
        return $this->partners;
    }
}

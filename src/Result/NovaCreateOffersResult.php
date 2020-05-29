<?php

namespace OrcaServices\NovaApi\Result;

/**
 * Data.
 */
final class NovaCreateOffersResult
{
    use NovaMessageTrait;

    /**
     * Offers.
     *
     * @var NovaOffer[]
     */
    private $offers = [];

    /**
     * Set value.
     *
     * @param NovaOffer $offer The nova offer
     *
     * @return void
     */
    public function addOffer(NovaOffer $offer)
    {
        $this->offers[] = $offer;
    }

    /**
     * Get a list of offers.
     *
     * @return array|NovaOffer[] The offers
     */
    public function getOffers(): array
    {
        return $this->offers;
    }
}

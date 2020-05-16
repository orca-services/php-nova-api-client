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
    public $offers = [];

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
}

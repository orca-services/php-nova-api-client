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
}

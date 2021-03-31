<?php

namespace OrcaServices\NovaApi\Result;

/**
 * Data.
 */
final class NovaSavCreateOffersResult
{
    use NovaMessageTrait;

    /**
     * Offers.
     *
     * @var NovaSavOffer[]
     */
    public $offers = [];
}

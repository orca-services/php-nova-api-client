<?php

namespace OrcaServices\NovaApi\Result;

use Cake\Chronos\Chronos;

/**
 * Data.
 */
final class NovaOffer
{
    /**
     * @var string|null UUID
     */
    public $novaOfferId;

    /**
     * @var string|null Value
     */
    public $price;

    /**
     * @var string|null Value
     */
    public $currency;

    /**
     * @var string|null Value
     */
    public $productNumber;

    /**
     * @var string|null Value
     */
    public $title;

    /**
     * @var Chronos Value
     */
    public $validFrom;

    /**
     * @var Chronos Value
     */
    public $validTo;

    /**
     * @var string|null Value
     */
    public $carrierMedium;

    /**
     * @var string|null KLASSE_1 or KLASSE_2
     */
    public $travelClass;
}

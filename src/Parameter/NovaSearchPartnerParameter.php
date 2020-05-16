<?php

namespace OrcaServices\NovaApi\Parameter;

/**
 * Data.
 */
final class NovaSearchPartnerParameter extends NovaIdentifierParameter
{
    /**
     * NOVA / SBB customer ID (uuid).
     *
     * @var string|null
     */
    public $tkId;

    /**
     * SBB card number, Grundkartennummer (e.g. GAQ577).
     *
     * @var string|null
     */
    public $cardNumber;

    /**
     * NOVA CKM (customer number).
     *
     * @var string|null
     */
    public $ckm;
}

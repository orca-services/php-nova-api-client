<?php

namespace OrcaServices\NovaApi\Parameter;

use Cake\Chronos\Chronos;

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

    /**
     * Customer last name
     *
     * @var string|null
     */
    public $lastName;

    /**
     * Customer first name
     *
     * @var string|null
     */
    public $firstName;

    /**
     * Customer e-mail address
     *
     * @var string|null
     */
    public $mail;

    /**
     * Customer country
     *
     * @var string|null
     */
    public $country;

    /**
     * Customer city
     *
     * @var string|null
     */
    public $city;

    /**
     * Customer postal code
     *
     * @var string|null
     */
    public $postalCode;

    /**
     * Customer street and number
     *
     * @var string|null
     */
    public $street;

    /**
     * Customer date of birth
     *
     * @var Chronos|null
     */
    public $dateOfBirth;
}

<?php

namespace OrcaServices\NovaApi\Result;

use Cake\Chronos\Chronos;

/**
 * Data.
 */
final class NovaPartner
{
    /**
     * @var string Value
     */
    public $tkId;

    /**
     * @var string|null Value
     */
    public $ckm;

    /**
     * @var string|null Value
     */
    public $cardNumber;

    /**
     * @var string|null Value
     */
    public $country;

    /**
     * @var string|null Value
     */
    public $city;

    /**
     * @var string|null Value
     */
    public $postalCode;

    /**
     * @var string|null Value
     */
    public $additional;

    /**
     * @var string|null Value
     */
    public $street;

    /**
     * @var string|null Value
     */
    public $poBox;

    /**
     * @var string|null Value
     */
    public $phoneNumber;

    /**
     * @var string|null Value
     */
    public $mobileNumber;

    /**
     * @var string|null Value
     */
    public $email;

    /**
     * @var string|null Value
     */
    public $firstName;

    /**
     * @var string|null Value
     */
    public $lastName;

    /**
     * @var string|null Value
     */
    public $title;

    /**
     * @var Chronos|null Value
     */
    public $dateOfBirth;

    /**
     * @var int|null Value
     */
    public $genderTypeId;

    /**
     * @var int
     */
    public $deceased = 0;

    /**
     * @var Chronos|null Value
     */
    public $changedAt;
}

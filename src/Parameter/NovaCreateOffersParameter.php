<?php

namespace OrcaServices\NovaApi\Parameter;

use Cake\Chronos\Chronos;

/**
 * Data.
 */
final class NovaCreateOffersParameter extends NovaIdentifierParameter
{
    /**
     * NOVA / SBB customer ID (uuid).
     *
     * @var string
     */
    public $tkId;

    /**
     * @var int|null Value
     */
    public $novaProductNumber;

    /**
     * @var Chronos|null Value
     */
    public $dateOfBirth;

    /**
     * @var int|null Value
     */
    public $genderTypeId;

    /**
     * @var int|null Value
     */
    public $travelClass;

    /**
     * @var Chronos Value
     */
    public $validFrom;

    /**
     * @var string Fix TNW = 460
     */
    public $tariffOwner = '460';
}

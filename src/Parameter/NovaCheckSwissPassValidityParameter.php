<?php

namespace OrcaServices\NovaApi\Parameter;

/**
 * Data.
 */
final class NovaCheckSwissPassValidityParameter extends NovaIdentifierParameter
{
    /**
     * NOVA / SBB customer ID (uuid).
     *
     * @var string
     */
    public $tkId;
}

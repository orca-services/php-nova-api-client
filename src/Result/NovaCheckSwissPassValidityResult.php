<?php

namespace OrcaServices\NovaApi\Result;

/**
 * Data.
 */
final class NovaCheckSwissPassValidityResult
{
    use NovaMessageTrait;

    /**
     * Result.
     *
     * SP_OK:
     * No new Swisspass is needed
     * or in other words
     * Customer already has a SwissPass or at least a order is in process.
     *
     * SP_NICHT_OK_FOTO_OK:
     * A new Swisspass is needed, there already is a valid photo available
     * or in other words
     * Customer does not have a SwissPass but there is a valid photo available.
     *
     * SP_NICHT_OK_FOTO_NICHT_OK:
     * A new Swisspass is needed; a new photo needs to be provided
     * or in other words
     * Customer does not have a SwissPass and no photo is available.
     *
     * The string can be empty ''
     *
     * @var string
     */
    public $result = '';

    /**
     * Status.
     *
     * 'OK' or 'NOK'
     *
     * @var string
     */
    public $status = '';
}

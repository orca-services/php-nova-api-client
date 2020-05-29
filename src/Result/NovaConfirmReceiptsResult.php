<?php

namespace OrcaServices\NovaApi\Result;

/**
 * Data.
 */
final class NovaConfirmReceiptsResult
{
    use NovaMessageTrait;

    /**
     * Services.
     *
     * @var NovaServiceItem[]
     */
    public $services = [];
}

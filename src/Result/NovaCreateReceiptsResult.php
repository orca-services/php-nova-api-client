<?php

namespace OrcaServices\NovaApi\Result;

/**
 * Data.
 */
final class NovaCreateReceiptsResult
{
    use NovaMessageTrait;

    /**
     * Services.
     *
     * @var NovaServiceItem[]
     */
    public $services = [];
}

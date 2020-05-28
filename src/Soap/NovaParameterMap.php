<?php

namespace OrcaServices\NovaApi\Soap;

/**
 * NovaParameterMap
 */
class NovaParameterMap
{
    /**
     * Parameter map
     *
     * @var array
     */
    public $map;

    /**
     * NovaParameterMap constructor.
     *
     * @param array $map Parameter map
     */
    public function __construct(array $map = [])
    {
        $this->map = $map;
    }
}

<?php

namespace OrcaServices\NovaApi\Test\Traits;

use DI\Container;
use DI\ContainerBuilder;

/**
 * Container Trait.
 */
trait ContainerTestTrait
{
    /** @var Container */
    protected $container;

    /**
     * Create a container instance.
     *
     * @return void
     */
    protected function createContainer()
    {
        $this->container = (new ContainerBuilder())->build();
    }
}

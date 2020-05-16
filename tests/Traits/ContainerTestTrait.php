<?php

namespace OrcaServices\NovaApi\Test\Traits;

use DI\Container;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use UnexpectedValueException;

/**
 * Container Trait.
 */
trait ContainerTestTrait
{
    /** @var ContainerInterface|Container|null */
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

    /**
     * Unset the container instance.
     *
     * @return void
     */
    protected function clearContainer()
    {
        $this->container = null;
    }

    /**
     * Get container.
     *
     * @throws UnexpectedValueException
     *
     * @return ContainerInterface|Container The container
     */
    protected function getContainer(): ContainerInterface
    {
        if ($this->container === null) {
            throw new UnexpectedValueException('Container must be initialized');
        }

        return $this->container;
    }
}

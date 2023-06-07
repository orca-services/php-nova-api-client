<?php

namespace OrcaServices\NovaApi\Test\Traits;

use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use UnexpectedValueException;

/**
 * Unit test.
 */
trait UnitTestTrait
{
    use ContainerTestTrait;

    /** {@inheritdoc} */
    public static function setUpBeforeClass(): void
    {
        date_default_timezone_set('Europe/Zurich');
    }

    /** {@inheritdoc} */
    protected function setUp(): void
    {
        $this->createContainer();
    }

    /**
     * Add mock to container.
     *
     * @param string $class The class or interface
     *
     * @throws UnexpectedValueException
     *
     * @return MockObject The mock
     */
    protected function registerMock(string $class): MockObject
    {
        $mock = $this->createMockObject($class);
        $this->container->set($class, $this->createMockObject($class));

        return $mock;
    }

    /**
     * Mocking an interface.
     *
     * @param string $class The interface / class name
     *
     * @throws InvalidArgumentException
     *
     * @return MockObject The mock
     */
    protected function createMockObject(string $class): MockObject
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException(sprintf('Class not found: %s', $class));
        }

        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}

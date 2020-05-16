<?php

namespace OrcaServices\NovaApi\Test\Traits;

use DI\Container;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use UnexpectedValueException;

/**
 * Unit test.
 */
trait UnitTestTrait
{
    use ContainerTestTrait;

    /** {@inheritdoc} */
    protected function setUp()
    {
        $this->createContainer();
    }

    /** {@inheritdoc} */
    protected function tearDown()
    {
        $this->clearContainer();
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
        $container = $this->getContainer();

        if ($container instanceof Container) {
            $mock = $this->createMockObject($class);

            $container->set($class, $this->createMockObject($class));

            return $mock;
        }

        throw new UnexpectedValueException(sprintf('The class could not be mocked: %s', $class));
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

    /**
     * Create a mocked class method.
     *
     * @param array|callable $method The class and method
     *
     * @return InvocationMocker The mocked method
     */
    protected function mockMethod($method): InvocationMocker
    {
        /** @var MockObject $mock */
        $mock = $this->getContainer()->get((string)$method[0]);

        return $mock->method((string)$method[1]);
    }
}

<?php

namespace Dhii\Invocation\UnitTest;

use ArrayObject;
use Dhii\Invocation\CreateReflectionForCallableCapableTrait as TestSubject;
use ReflectionFunction;
use ReflectionMethod;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class CreateReflectionForCallableCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Invocation\CreateReflectionForCallableCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock.
     *
     * @return MockObject The new instance.
     */
    public function createInstance($methods = [])
    {
        is_array($methods) && $methods = $this->mergeValues($methods, [
            '__',
        ]);

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
            ->setMethods($methods)
            ->getMockForTrait();

        $mock->method('__')
                ->will($this->returnArgument(0));

        return $mock;
    }

    /**
     * Merges the values of two arrays.
     *
     * The resulting product will be a numeric array where the values of both inputs are present, without duplicates.
     *
     * @since [*next-version*]
     *
     * @param array $destination The base array.
     * @param array $source      The array with more keys.
     *
     * @return array The array which contains unique values
     */
    public function mergeValues($destination, $source)
    {
        return array_keys(array_merge(array_flip($destination), array_flip($source)));
    }

    /**
     * Creates a mock that both extends a class and implements interfaces.
     *
     * This is particularly useful for cases where the mock is based on an
     * internal class, such as in the case with exceptions. Helps to avoid
     * writing hard-coded stubs.
     *
     * @since [*next-version*]
     *
     * @param string   $className      Name of the class for the mock to extend.
     * @param string[] $interfaceNames Names of the interfaces for the mock to implement.
     *
     * @return MockBuilder The builder for a mock of an object that extends and implements
     *                     the specified class and interfaces.
     */
    public function mockClassAndInterfaces($className, $interfaceNames = [])
    {
        $paddingClassName = uniqid($className);
        $definition = vsprintf('abstract class %1$s extends %2$s implements %3$s {}', [
            $paddingClassName,
            $className,
            implode(', ', $interfaceNames),
        ]);
        eval($definition);

        return $this->getMockBuilder($paddingClassName);
    }

    /**
     * Creates a new exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return RootException|MockObject The new exception.
     */
    public function createException($message = '')
    {
        $mock = $this->getMockBuilder('Exception')
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInternalType(
            'object',
            $subject,
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests that `_createReflectionForCallable()` works as expected when given a function name.
     *
     * @since [*next-version*]
     */
    public function testCreateReflectionForCallableFunctionName()
    {
        $callable = 'array_map';
        $reflection = new ReflectionFunction($callable);
        $subject = $this->createInstance(['_createReflectionFunction']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeCallable')
            ->with($callable)
            ->will($this->returnArgument(0));
        $subject->expects($this->exactly(1))
            ->method('_createReflectionFunction')
            ->with($callable)
            ->will($this->returnValue($reflection));

        $result = $_subject->_createReflectionForCallable($callable);
        $this->assertSame($reflection, $reflection, 'Wrong reflection created');
    }

    /**
     * Tests that `_createReflectionForCallable()` works as expected when given a closure.
     *
     * @since [*next-version*]
     */
    public function testCreateReflectionForCallableClosure()
    {
        $callable = function () {};
        $reflection = new ReflectionFunction($callable);
        $subject = $this->createInstance(['_createReflectionFunction']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeCallable')
            ->with($callable)
            ->will($this->returnArgument(0));
        $subject->expects($this->exactly(1))
            ->method('_createReflectionFunction')
            ->with($callable)
            ->will($this->returnValue($reflection));

        $result = $_subject->_createReflectionForCallable($callable);
        $this->assertSame($reflection, $result, 'Wrong reflection created');
    }

    /**
     * Tests that `_createReflectionForCallable()` works as expected when given an array where class is a string.
     *
     * @since [*next-version*]
     */
    public function testCreateReflectionForCallableArrayStatic()
    {
        $class = 'ArrayObject';
        $method = 'getIterator';
        $callable = [$class, $method];
        $reflection = new ReflectionMethod($class, $method);
        $subject = $this->createInstance(['_createReflectionFunction']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeCallable')
            ->with($callable)
            ->will($this->returnArgument(0));
        $subject->expects($this->exactly(1))
            ->method('_createReflectionMethod')
            ->with($class, $method)
            ->will($this->returnValue($reflection));

        $result = $_subject->_createReflectionForCallable($callable);
        $this->assertSame($reflection, $result, 'Wrong reflection created');
    }

    /**
     * Tests that `_createReflectionForCallable()` works as expected when given an array where class is an object.
     *
     * @since [*next-version*]
     */
    public function testCreateReflectionForCallableArrayInstance()
    {
        $class = 'ArrayObject';
        $object = new $class();
        $method = 'getIterator';
        $callable = [$object, $method];
        $reflection = new ReflectionMethod($class, $method);
        $subject = $this->createInstance(['_createReflectionFunction']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeCallable')
            ->with($callable)
            ->will($this->returnArgument(0));
        $subject->expects($this->exactly(1))
            ->method('_createReflectionMethod')
            ->with($class, $method)
            ->will($this->returnValue($reflection));

        $result = $_subject->_createReflectionForCallable($callable);
        $this->assertSame($reflection, $result, 'Wrong reflection created');
    }
}

<?php

namespace Dhii\Invocation\UnitTest;

use Dhii\Invocation\NormalizeCallableCapableTrait as TestSubject;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class NormalizeCallableCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Invocation\NormalizeCallableCapableTrait';

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
     * Creates a new exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return InvalidArgumentException|MockObject The new exception.
     */
    public function createInvalidArgumentException($message = '')
    {
        $mock = $this->getMockBuilder('InvalidArgumentException')
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new Stringable object.
     *
     * @since [*next-version*]
     *
     * @param string $string The string that the object will represent.
     *
     * @return MockObject|Stringable The new Stringable.
     */
    public function createStringable($string = '')
    {
        $mock = $this->getMockBuilder('Dhii\Util\String\StringableInterface')
            ->setMethods(['__toString'])
            ->getMock();

        $mock->method('__toString')
            ->will($this->returnValue($string));

        return $mock;
    }

    /**
     * Creates a new invocable stringable.
     *
     * @since [*next-version*]
     *
     * @return MockObject
     */
    public function createInvocableStringable($string = '')
    {
        $mock = $this->getMockBuilder('MyInvocable')
            ->setmethods(['__invoke', '__toString'])
            ->getMock();

        $mock->method('__toString')
            ->will($this->returnValue($string));

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
     * Tests that `_normalizeCallable()` works as expected when given a closure.
     *
     * @since [*next-version*]
     */
    public function testNormalizeCallableClosure()
    {
        $callable = function () {};
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $result = $_subject->_normalizeCallable($callable);
        $this->assertSame($callable, $result, 'Normalization result wrong');
    }

    /**
     * Tests that `_normalizeCallable()` works as expected when given a function name string.
     *
     * @since [*next-version*]
     */
    public function testNormalizeCallableFunctionNameString()
    {
        $callable = 'array_map';
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeString')
            ->with($callable)
            ->will($this->returnArgument(0));

        $result = $_subject->_normalizeCallable($callable);
        $this->assertSame($callable, $result, 'Normalization result wrong');
    }

    /**
     * Tests that `_normalizeCallable()` works as expected when given a function name stringable object.
     *
     * @since [*next-version*]
     */
    public function testNormalizeCallableFunctionNameObject()
    {
        $functionName = 'array_map';
        $callable = $this->createStringable('array_map');
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeString')
            ->with($callable)
            ->will($this->returnValue($functionName));

        $result = $_subject->_normalizeCallable($callable);
        $this->assertSame($functionName, $result, 'Normalization result wrong');
    }

    /**
     * Tests that `_normalizeCallable()` works as expected when given a stringable callable.
     *
     * @since [*next-version*]
     */
    public function testNormalizeCallableStringableInvocable()
    {
        $functionName = 'array_map';
        $callable = $this->createInvocableStringable('array_map');
        $methodCallable = [$callable, '__invoke'];
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeMethodCallable')
            ->with($callable)
            ->will($this->returnValue($methodCallable));

        $result = $_subject->_normalizeCallable($callable);
        $this->assertSame($methodCallable, $result, 'Normalization result wrong');
    }

    /**
     * Tests that `_normalizeCallable()` works as expected when given an array.
     *
     * @since [*next-version*]
     */
    public function testNormalizeCallableArray()
    {
        $callable = [new \ArrayObject([]), 'getIterator'];
        $exception = $this->createInvalidArgumentException('Not a stringable');
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeString')
            ->with($callable)
            ->will($this->throwException($exception));
        $subject->expects($this->exactly(1))
            ->method('_normalizeMethodCallable')
            ->with($callable)
            ->will($this->returnValue($callable));

        $result = $_subject->_normalizeCallable($callable);
        $this->assertSame($callable, $result, 'Normalization result wrong');
    }
}

<?php

namespace Dhii\Invocation\UnitTest;

use Dhii\Invocation\InvokeCallbackCapableTrait as TestSubject;
use Xpmock\TestCase;
use Exception as RootException;
use InvalidArgumentException;
use OutOfRangeException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class InvokeCallbackCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Invocation\InvokeCallbackCapableTrait';

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
        $methods = $this->mergeValues($methods, [
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
     * @param string $className      Name of the class for the mock to extend.
     * @param string $interfaceNames Names of the interfaces for the mock to implement.
     *
     * @return object The object that extends and implements the specified class and interfaces.
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

        return $this->getMockForAbstractClass($paddingClassName);
    }

    /**
     * Creates a new exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return RootException The new exception.
     */
    public function createException($message = '')
    {
        $mock = $this->getMockBuilder('Exception')
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new Invalid Argument exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return InvalidArgumentException The new exception.
     */
    public function createInvalidArgumentException($message = '')
    {
        $mock = $this->getMockBuilder('InvalidArgumentException')
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new Out Of Range exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return OutOfRangeException The new exception.
     */
    public function createOutOfRangeException($message = '')
    {
        $mock = $this->getMockBuilder('OutOfRangeException')
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Generates an array of random length containing random integers.
     *
     * @since [*next-version*]
     *
     * @param int $minAmount The minimal amount of elements in the array.
     * @param int $maxAmount The maximal amount of elements in the array.
     * @param int $min       The minimal integer.
     * @param int $max       The maximal integer.
     *
     * @return int[] The random array.
     */
    public function createRandomIntArray($minAmount, $maxAmount, $min, $max)
    {
        $amount = rand($minAmount, $maxAmount);
        $array = [];
        for ($i = 0; $i < $amount; ++$i) {
            $array[] = rand($min, $max);
        }

        return $array;
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
     * Tests whether `_invokeCallback()` works as expected.
     *
     * @since [*next-version*]
     */
    public function testInvokeCallback()
    {
        $args = $this->createRandomIntArray(1, 3, 1, 99);
        $callback = function () { return func_get_args(); };
        $subject = $this->createInstance([
            '_getCallback',
            '_invokeCallable',
            '_normalizeIterable',
        ]);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_getCallback')
            ->will($this->returnValue($callback));
        $subject->expects($this->exactly(1))
            ->method('_invokeCallable')
            ->with($callback, $args)
            ->will($this->returnCallback(function ($callback, $args) {
                return call_user_func_array($callback, $args);
            }));
        $subject->expects($this->exactly(1))
            ->method('_normalizeIterable')
            ->with($args)
            ->will($this->returnArgument(0));

        $result = $_subject->_invokeCallback($args);
        $this->assertEquals($args, $result, 'Result of invocation is wrong');
    }

    /**
     * Tests that `_invokeCallback()` fails correctly when the args list is invalid.
     *
     * @since [*next-version*]
     */
    public function testInvokeCallbackFailureInvalidArgs()
    {
        $args = uniqid('args');
        $exception = $this->createInvalidArgumentException('Invalid args list');
        $subject = $this->createInstance(['_createInvalidArgumentException', '_normalizeIterable']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeIterable')
            ->with($args)
            ->will($this->throwException($exception));

        $this->setExpectedException('InvalidArgumentException');
        $_subject->_invokeCallback($args);
    }

    /**
     * Tests that `_invokeCallback()` fails correctly when the normalized args list passed to `_invokeCallable()` is invalid.
     *
     * @since [*next-version*]
     */
    public function testInvokeCallbackFailureInvokeCallableInvalidArgs()
    {
        $cb = function () {};
        $args = uniqid('list-that-cannot-be-normalized-to-array');
        $innerException = $this->createInvalidArgumentException('Invalid args list');
        $exception = $this->createOutOfRangeException('Could not normalize args list to array');
        $subject = $this->createInstance(['_createInvalidArgumentException', '_normalizeIterable']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_getCallback')
            ->will($this->returnValue($cb));
        $subject->expects($this->exactly(1))
            ->method('_invokeCallable')
            ->with($cb, $args)
            ->will($this->throwException($innerException));
        $subject->expects($this->exactly(1))
            ->method('_normalizeIterable')
            ->with($args)
            ->will($this->returnArgument(0));
        $subject->expects($this->exactly(1))
            ->method('_createOutOfRangeException')
            ->with(
                $this->isType('string'),
                null,
                $innerException,
                $cb
            )
            ->will($this->returnValue($exception));

        $this->setExpectedException('OutOfRangeException');
        $_subject->_invokeCallback($args);
    }

    /**
     * Tests that `_invokeCallback()` fails correctly if `_invokeCallable()` throws `InvalidArgumentException`.
     *
     * @since [*next-version*]
     */
    public function testInvokeCallbackFailureInvalidArgumentException()
    {
        $callback = function () {};
        $exception = $this->createInvalidArgumentException(uniqid('message'));
        $subject = $this->createInstance([
            '_getCallback',
            '_invokeCallable',
            '_createOutOfRangeException',
        ]);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
                ->method('_getCallback')
                ->will($this->returnValue($callback));
        $subject->expects($this->exactly(1))
                ->method('_invokeCallable')
                ->will($this->throwException($exception));
        $subject->expects($this->exactly(1))
                ->method('_createOutOfRangeException')
                ->with(
                    $this->isType('string'),
                    $this->anything(),
                    $exception,
                    $callback
                )
                ->will($this->returnCallback(function ($message) {
                    return $this->createOutOfRangeException($message);
                }));

        $this->setExpectedException('OutOfRangeException');
        $_subject->_invokeCallback();
    }
}

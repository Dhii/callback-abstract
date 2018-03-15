<?php

namespace Dhii\Invocation\FuncTest;

use ArrayObject;
use Dhii\Exception\InternalExceptionInterface;
use Dhii\Invocation\Exception\InvocationExceptionInterface;
use Dhii\Invocation\InvokeCallableCapableTrait as TestSubject;
use Dhii\Util\String\StringableInterface as Stringable;
use Dhii\Validation\Exception\ValidationFailedExceptionInterface;
use InvalidArgumentException;
use OutOfRangeException;
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
class InvokeCallableCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Invocation\InvokeCallableCapableTrait';

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
            '_createInvalidArgumentException',
            '_createOutOfRangeException',
            '_createValidationFailedException',
            '_createInvocationException',
            '_createInternalException',
            '_normalizeArray',
            '_normalizeString',
            '_createReflectionFunction',
            '_createReflectionMethod',
            '_countIterable',
        ]);

        $mock = $this->mockTraits([
                static::TEST_SUBJECT_CLASSNAME,
                'Dhii\Invocation\CreateReflectionForCallableCapableTrait',
                'Dhii\Invocation\ValidateParamsCapableTrait',
                'Dhii\Invocation\NormalizeCallableCapableTrait',
                'Dhii\Invocation\NormalizeMethodCallableCapableTrait',
            ])
            ->setMethods($methods)
            ->getMock();

        $mock->method('__')
            ->will($this->returnArgument(0));
        $mock->method('_createInvalidArgumentException')
            ->will($this->returnCallback(function ($message, $code = null, $inner = null, $argument = null) {
                return $this->createInvalidArgumentException($message, $code, $inner, $argument);
            }));
        $mock->method('_createOutOfRangeException')
            ->will($this->returnCallback(function ($message) {
                return $this->createOutOfRangeException($message);
            }));
        $mock->method('_createValidationFailedException')
            ->will($this->returnCallback(function ($message, $code = null, $inner = null, $validator = null, $subject = null) {
                return $this->createValidationFailedException($message, $code, $inner, $validator, $subject);
            }));
        $mock->method('_createInvocationException')
            ->will($this->returnCallback(function ($message, $code = null, $inner = null, $callable = null, $args = null) {
                return $this->createInvocationException($message, $code, $inner, $callable, $args);
            }));
        $mock->method('_createInternalException')
            ->will($this->returnCallback(function ($message, $code = null, $inner = null) {
                return $this->createInternalException($message, $code, $inner);
            }));
        $mock->method('_normalizeArray')
            ->will($this->returnCallback(function ($iterable) {
                return is_array($iterable)
                    ? $iterable
                    : iterator_to_array($iterable);
            }));
        $mock->method('_normalizeString')
            ->will($this->returnCallback(function ($stringable) {
                if (!is_string($stringable) && !($stringable instanceof Stringable)) {
                    throw $this->createInvalidArgumentException('Not stringable');
                }

                return (string) $stringable;
            }));
        $mock->method('_createReflectionFunction')
            ->will($this->returnCallback(function ($function) {
                return new ReflectionFunction($function);
            }));
        $mock->method('_createReflectionMethod')
            ->will($this->returnCallback(function ($class, $method) {
                return new ReflectionMethod($class, $method);
            }));
        $mock->method('_countIterable')
            ->will($this->returnCallback(function ($iterable) {
                return is_array($iterable)
                    ? count($iterable)
                    : iterator_count($iterable);
            }));

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
     * Creates a mock that uses traits.
     *
     * This is particularly useful for testing integration between multiple traits.
     *
     * @since [*next-version*]
     *
     * @param string[] $traitNames Names of the traits for the mock to use.
     *
     * @return MockBuilder The builder for a mock of an object that uses the traits.
     */
    public function mockTraits($traitNames = [])
    {
        $paddingClassName = uniqid('Traits');
        $definition = vsprintf('abstract class %1$s {%2$s}', [
            $paddingClassName,
            implode(
                ' ',
                array_map(
                    function ($v) {
                        return vsprintf('use %1$s;', [$v]);
                    },
                    $traitNames)),
        ]);
        var_dump($definition);
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
     * Creates a new Out Of Range exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return OutOfRangeException|MockObject The new exception.
     */
    public function createOutOfRangeException($message = '')
    {
        $mock = $this->getMockBuilder('OutOfRangeException')
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
     * Creates a new Invocation exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return RootException|InvocationExceptionInterface|MockObject The new exception.
     */
    public function createInvocationException($message = '')
    {
        $mock = $this->mockClassAndInterfaces('Exception', ['Dhii\Invocation\Exception\InvocationExceptionInterface'])
            ->setConstructorArgs([$message])
            ->setMethods(['getCallable', 'getArgs'])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new Internal exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return RootException|InternalExceptionInterface|MockObject The new exception.
     */
    public function createInternalException($message = '')
    {
        $mock = $this->mockClassAndInterfaces('Exception', ['Dhii\Exception\InternalExceptionInterface'])
            ->setConstructorArgs([$message])
            ->setMethods([])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new Validation Failed exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return RootException|ValidationFailedExceptionInterface|MockObject The new exception.
     */
    public function createValidationFailedException($message = '')
    {
        $mock = $this->mockClassAndInterfaces('Exception', ['Dhii\Validation\Exception\ValidationFailedExceptionInterface'])
            ->setConstructorArgs([$message])
            ->setMethods(['getValidator', 'getSubject', 'getValidationErrors'])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new generic object to mock.
     *
     * @since [*next-version*]
     *
     * @param array|null $methods
     *
     * @return MockObject
     */
    public function createObject($methods = [])
    {
        is_array($methods) && $methods = $this->mergeValues($methods, [
        ]);

        $mock = $this->getMockBuilder('MyObject')
            ->setMethods($methods)
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
        $subject = $this->createInstance(['_getArgsListErrors']);

        $this->assertInternalType(
            'object',
            $subject,
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests that `_invokeCallable()` works as expected when given a function.
     *
     * @since [*next-version*]
     */
    public function testInvokeCallableClosure()
    {
        $fn = function ($strings) {
            $length = 0;
            foreach ($strings as $_string) {
                if (!empty($_string)) {
                    $length += strlen($_string);
                }
            }

            return $length;
        };
        $callable = function ($arg0, $arg1 = null) use ($fn) {
            return $fn([$arg0, $arg1]);
        };
        $arg0 = uniqid('arg0');
        $args = [$arg0];
        $length = $fn($args);
        $errors = [];
        $subject = $this->createInstance(['_getArgsListErrors']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_getArgsListErrors')
            ->with($args, $this->anything())
            ->will($this->returnValue($errors));

        $result = $_subject->_invokeCallable($callable, $args);
        $this->assertEquals($length, $result, 'Wrong invocation result');
    }

    /**
     * Tests that `_invokeCallable()` works as expected when given a function name.
     *
     * @since [*next-version*]
     */
    public function testInvokeCallableFunctionName()
    {
        $items = array_fill(0, rand(1, 16), uniqid('element'));
        $count = count($items);
        $callable = 'count';
        $args = [$items];
        $errors = [];
        $subject = $this->createInstance(['_getArgsListErrors']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_getArgsListErrors')
            ->with($args, $this->anything())
            ->will($this->returnValue($errors));

        $result = $_subject->_invokeCallable($callable, $args);
        $this->assertEquals($count, $result, 'Wrong invocation result');
    }

    /**
     * Tests that `_invokeCallable()` fails as expected when the callable cannot be invoked.
     *
     * @since [*next-version*]
     */
    public function testInvokeCallableFunctionNameFailureInvocation()
    {
        $callable = 'count';
        $args = [];
        $errors = ['Arg missing'];
        $subject = $this->createInstance(['_getArgsListErrors']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_getArgsListErrors')
            ->with($args, $this->anything())
            ->will($this->returnValue($errors));

        $this->setExpectedException('Dhii\Invocation\Exception\InvocationExceptionInterface');
        $_subject->_invokeCallable($callable, $args);
    }

    /**
     * Tests that `_invokeCallable()` works as expected when given an instance method.
     *
     * @since [*next-version*]
     */
    public function testInvokeCallableMethodInstance()
    {
        $items = array_fill(0, rand(1, 16), uniqid('element'));
        $count = count($items);
        $object = new ArrayObject($items);
        $callable = [$object, 'count'];
        $args = [];
        $errors = [];
        $subject = $this->createInstance(['_getArgsListErrors']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_getArgsListErrors')
            ->with($args, $this->anything())
            ->will($this->returnValue($errors));

        $result = $_subject->_invokeCallable($callable, $args);
        $this->assertEquals($count, $result, 'Wrong invocation result');
    }

    /**
     * Tests that `_invokeCallable()` works as expected when given a static method.
     *
     * @since [*next-version*]
     */
    public function testInvokeCallableMethodStatic()
    {
        $items = array_fill(0, rand(1, 16), uniqid('element'));
        $callable = 'SplFixedArray::fromArray';
        $args = [$items];
        $errors = [];
        $subject = $this->createInstance(['_getArgsListErrors']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_getArgsListErrors')
            ->with($args, $this->anything())
            ->will($this->returnValue($errors));

        $result = $_subject->_invokeCallable($callable, $args);
        $this->assertInstanceOf('SplFixedArray', $result, 'Wrong invocation result');
    }
}

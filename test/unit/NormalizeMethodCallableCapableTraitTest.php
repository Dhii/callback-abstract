<?php

namespace Dhii\Invocation\UnitTest;

use Dhii\Invocation\NormalizeMethodCallableCapableTrait as TestSubject;
use InvalidArgumentException;
use stdClass;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class NormalizeMethodCallableCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Invocation\NormalizeMethodCallableCapableTrait';

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
     * @param string $className      Name of the class for the mock to extend.
     * @param string $interfaceNames Names of the interfaces for the mock to implement.
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
     * @return MockObject|RootException The new exception.
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
     * @return MockObject|InvalidArgumentException The new exception.
     */
    public function createInvalidArgumentException($message = '')
    {
        $mock = $this->getMockBuilder('InvalidArgumentException')
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
     * Test that `_normalizeMethodCallable()` works as expected when given a string.
     *
     * @since [*next-version*]
     */
    public function testNormalizeMethodCallableString()
    {
        $class = uniqid('class'); // Invalid target
        $method = uniqid('method');
        $callableArray = [$class, $method];
        $callable = implode('::', $callableArray);
        $subject = $this->createInstance(['_normalizeString', '_normalizeArray']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(2))
            ->method('_normalizeString')
            ->withConsecutive(
                [$callable], // When handling original argument
                [$method] // When handling second part
            )
            ->will($this->returnArgument(0));
        $subject->expects($this->exactly(1))
            ->method('_normalizeArray')
            ->with([$class, $method])
            ->will($this->returnValue($callableArray));

        $result = $_subject->_normalizeMethodCallable($callable);
        $this->assertEquals($callableArray, $result, 'Result of normalization is wrong');
    }

    /**
     * Test that `_normalizeMethodCallable()` works as expected when given an array.
     *
     * @since [*next-version*]
     */
    public function testNormalizeMethodCallableArray()
    {
        $class = uniqid('class'); // Invalid target
        $method = uniqid('method');
        $callableArray = [$class, $method];
        $callable = $callableArray;
        $exception = $this->createInvalidArgumentException('Not a stringable');
        $subject = $this->createInstance(['_normalizeString', '_normalizeArray']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(2))
            ->method('_normalizeString')
            ->withConsecutive(
                [$callable], // When handling original argument
                [$method] // When handling second part
            )
            // Quite possibly, this cannot be done in a more elegant way
            // https://github.com/sebastianbergmann/phpunit/issues/674#issuecomment-369642418
            ->will($this->returnCallback(function ($value) use ($exception, $callable, $method) {
                if ($value === $callable) {
                    throw $exception;
                }

                if ($value === $method) {
                    return $method;
                }

                return;
            }));
        $subject->expects($this->exactly(1))
            ->method('_normalizeArray')
            ->with([$class, $method])
            ->will($this->returnArgument(0));

        $result = $_subject->_normalizeMethodCallable($callable);
        $this->assertEquals($callableArray, $result, 'Result of normalization is wrong');
    }

    /**
     * Test that `_normalizeMethodCallable()` fails as expected when given an object that could not be normalized into an array.
     *
     * @since [*next-version*]
     */
    public function testNormalizeMethodCallableFailureInvalidArgument()
    {
        $callable = new stdClass();
        $stringableException = $this->createInvalidArgumentException('Not a stringable');
        $exception = $this->createInvalidArgumentException('Not a list');
        $subject = $this->createInstance(['_normalizeString', '_normalizeArray']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeString')
            ->with($callable)
            ->will($this->throwException($stringableException));
        $subject->expects($this->exactly(1))
            ->method('_normalizeArray')
            ->with($callable)
            ->will($this->throwException($exception));

        $this->setExpectedException('InvalidArgumentException');
        $_subject->_normalizeMethodCallable($callable);
    }

    /**
     * Test that `_normalizeMethodCallable()` fails as expected when given an array with too few parts.
     *
     * @since [*next-version*]
     */
    public function testNormalizeMethodCallableFailureTooFewParts()
    {
        $callable = [uniqid('class')];
        $stringableException = $this->createInvalidArgumentException('Not a stringable');
        $exception = $this->createInvalidArgumentException('Too few parts');
        $subject = $this->createInstance(['_normalizeString', '_normalizeArray', '_createOutOfRangeException']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeString')
            ->with($callable)
            ->will($this->throwException($stringableException));
        $subject->expects($this->exactly(1))
            ->method('_normalizeArray')
            ->with($callable)
            ->will($this->returnArgument(0));
        $subject->expects($this->exactly(1))
            ->method('_createOutOfRangeException')
            ->with(
                $this->isType('string'),
                null,
                null,
                $callable
            )
            ->will($this->returnValue($exception));

        $this->setExpectedException('InvalidArgumentException');
        $_subject->_normalizeMethodCallable($callable);
    }

    /**
     * Test that `_normalizeMethodCallable()` fails as expected when given an array where the first item is not a valid object or string.
     *
     * @since [*next-version*]
     */
    public function testNormalizeMethodCallableFailureFirstArgumentInvalid()
    {
        $class = []; // Invalid target
        $method = uniqid('method');
        $callable = [$class, $method];
        $stringableException = $this->createInvalidArgumentException('Not a stringable');
        $exception = $this->createInvalidArgumentException('First part invalid');
        $subject = $this->createInstance(['_normalizeString', '_normalizeArray']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeString')
            ->with($callable)
            ->will($this->throwException($stringableException));
        $subject->expects($this->exactly(1))
            ->method('_normalizeArray')
            ->with($callable)
            ->will($this->returnArgument(0));
        $subject->expects($this->exactly(1))
            ->method('_createOutOfRangeException')
            ->with(
                $this->isType('string'),
                null,
                null,
                $callable
            )
            ->will($this->returnValue($exception));

        $this->setExpectedException('InvalidArgumentException');
        $_subject->_normalizeMethodCallable($callable);
    }

    /**
     * Test that `_normalizeMethodCallable()` fails as expected when given an array where the second item is not valid.
     *
     * @since [*next-version*]
     */
    public function testNormalizeMethodCallableFailureSecondArgumentInvalid()
    {
        $class = uniqid('class');
        $method = []; // Invalid method
        $callable = [$class, $method];
        $stringableException = $this->createInvalidArgumentException('Not a stringable');
        $normalizationException = $this->createInvalidArgumentException('Method not stringable');
        $exception = $this->createInvalidArgumentException('Second part invalid');
        $subject = $this->createInstance(['_normalizeString', '_normalizeArray']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(2))
            ->method('_normalizeString')
            ->withConsecutive(
                [$callable], // When handling original argument
                [$method] // When handling second part
            )
            // Quite possibly, this cannot be done in a more elegant way
            // https://github.com/sebastianbergmann/phpunit/issues/674#issuecomment-369642418
            ->will($this->returnCallback(function ($value) use ($stringableException, $normalizationException, $callable, $method) {
                if ($value === $callable) {
                    throw $stringableException;
                }

                if ($value === $method) {
                    throw $normalizationException;
                }

                return;
            }));
        $subject->expects($this->exactly(1))
            ->method('_normalizeArray')
            ->with($callable)
            ->will($this->returnArgument(0));
        $subject->expects($this->exactly(1))
            ->method('_createOutOfRangeException')
            ->with(
                $this->isType('string'),
                null,
                $normalizationException,
                $callable
            )
            ->will($this->returnValue($exception));

        $this->setExpectedException('InvalidArgumentException');
        $_subject->_normalizeMethodCallable($callable);
    }
}

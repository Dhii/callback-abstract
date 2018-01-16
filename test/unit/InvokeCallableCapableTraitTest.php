<?php

namespace Dhii\Invocation\UnitTest;

use Dhii\Invocation\Exception\InvocationExceptionInterface;
use Traversable;
use Xpmock\TestCase;
use Dhii\Invocation\InvokeCallableCapableTrait as TestSubject;
use InvalidArgumentException;
use Exception as RootException;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class InvokeCallableCapableTraitTest extends TestCase
{
    /**
     * The name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Invocation\InvokeCallableCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @return object
     */
    public function createInstance()
    {
        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                ->setMethods([
                    '__',
                    '_createInvalidArgumentException',
                    '_createInvocationException',
                ])
                ->getMockForTrait();
        $mock->method('__')
                ->will($this->returnArgument(0));
        $mock->method('_createInvalidArgumentException')
                ->will($this->returnCallback(function ($message) {
                    return new InvalidArgumentException($message);
                }));
        $mock->method('_createInvocationException')
            ->will($this->returnCallback(function ($message = '', $code = 0, $inner = null, $callable = null, $args = null) {
                $e = $this->createInvocationException($message, $code, $inner, $callable, $args);

                return $e;
            }));

        $mock->method('_normalizeArray')
            ->will($this->returnCallback(function ($list) {
                return $list instanceof Traversable
                    ? iterator_to_array($list)
                    : $list;
            }));

        return $mock;
    }

    /**
     * Creates a mock that both extends a class and implements interfaces.
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

        return $this->mock($paddingClassName);
    }

    /**
     * Creates a new Invocation exception.
     *
     * @since [*next-version*]
     *
     * @param string $message  The error message, if any.
     * @param int    $code     The error code, if any.
     * @param null   $inner    The inner exception, if any.
     * @param null   $callable The callable that caused the problem, if any.
     * @param null   $args     The args that the callable was invoked with, if any.
     *
     * @return InvocationExceptionInterface The new exception.
     */
    public function createInvocationException($message = '', $code = 0, $inner = null, $callable = null, $args = null)
    {
        return $this->mockClassAndInterfaces('Exception', ['Dhii\Invocation\Exception\InvocationExceptionInterface'])
            ->getCallable($callable)
            ->getArgs($args)
            ->new($message, $code, $inner);
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
     * Tests whether invoking a callable works as expected.
     *
     * @since [*next-version*]
     */
    public function testInvokeCallable()
    {
        $args = ['Apple', 'Banana'];
        $callable = function () {
            return func_get_args();
        };

        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $result = $_subject->_invokeCallable($callable, $args);

        $this->assertEquals($args, $result, 'Invocation produced a wrong result');
    }

    /**
     * Tests whether invoking a callable failure as expected when attempting to invoke something that is not callable.
     *
     * @since [*next-version*]
     */
    public function testInvokeCallableFailureNotCallable()
    {
        $args = ['Apple', 'Banana'];
        $callable = new \stdClass();

        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $this->setExpectedException('InvalidArgumentException');
        $result = $_subject->_invokeCallable($callable, $args);

        $this->assertEquals($args, $result, 'Invocation produced a wrong result');
    }

    /**
     * Tests whether invoking a callable fails as expected if the callable throws.
     *
     * @since [*next-version*]
     */
    public function testInvokeCallableInvocationException()
    {
        $args = ['Apple', 'Banana'];
        $innerMessage = uniqid('inner-message');
        $exception = new RootException($innerMessage);
        $callable = function () use ($exception) {
            throw $exception;
        };

        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_createInvocationException')
            ->with(
                $this->isType('string'),
                $this->anything(),
                $exception,
                $callable,
                $args
            );

        $this->setExpectedException('Dhii\Invocation\Exception\InvocationExceptionInterface');
        $_subject->_invokeCallable($callable, $args);
    }
}

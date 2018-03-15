<?php

namespace Dhii\Invocation\UnitTest;

use Dhii\Invocation\Exception\InvocationExceptionInterface;
use ReflectionFunction;
use ReflectionMethod;
use Xpmock\TestCase;
use Dhii\Invocation\InvokeCallableCapableTrait as TestSubject;
use InvalidArgumentException;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

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
     * @return MockObject
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
     * Creates a new reflection function.
     *
     * @since [*next-version*]
     *
     * @param callable   $function The function to mock.
     * @param array|null $methods  The names of methods to mock.
     *
     * @return MockObject|ReflectionFunction The new reflection function.
     */
    public function createReflectionFunction($function, $methods = [])
    {
        is_array($methods) && $methods = $this->mergeValues($methods, [
        ]);

        $mock = $this->getMockBuilder('ReflectionFunction')
            ->setMethods($methods)
            ->setConstructorArgs([$function])
            ->enableProxyingToOriginalMethods()
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new reflection method.
     *
     * @since [*next-version*]
     *
     * @param object|string The class or object to reflect the method of.
     * @param string     $method  The method of the class to reflect.
     * @param array|null $methods The names of methods to mock.
     *
     * @return MockObject|ReflectionMethod The new reflection function.
     */
    public function createReflectionMethod($class, $method, $methods = [])
    {
        is_array($methods) && $methods = $this->mergeValues($methods, [
        ]);

        $mock = $this->getMockBuilder('ReflectionMethod')
            ->setMethods($methods)
            ->setConstructorArgs([$class, $method])
            ->enableProxyingToOriginalMethods()
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
    public function testInvokeCallableFunction()
    {
        $function = 'strlen';
        $arg = uniqid('string');
        $length = strlen($arg);
        $args = [$arg];
        $reflection = $this->createReflectionFunction($function, ['invokeArgs']);
        $params = $reflection->getParameters();
        $subject = $this->createInstance(['_normalizeArray', '_createReflectionForCallable', '_validateParams']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeArray')
            ->with($args)
            ->will($this->returnArgument(0));
        $subject->expects($this->exactly(1))
            ->method('_createReflectionForCallable')
            ->with($function)
            ->will($this->returnValue($reflection));
        $subject->expects($this->exactly(1))
            ->method('_validateParams')
            ->with($args, $params);

        $reflection->expects($this->exactly(1))
            ->method('invokeArgs')
            ->with($args)
            ->will($this->returnValue($length));

        $result = $_subject->_invokeCallable($function, $args);
        $this->assertEquals($length, $result, 'Invocation produced a wrong result');
    }

    /**
     * Tests whether invoking a callable works as expected.
     *
     * @since [*next-version*]
     */
    public function testInvokeCallableMethod()
    {
        $method = 'getStringLength';
        $object = $this->createObject([$method]);
        $class = get_class($object);
        $arg = uniqid('string');
        $length = strlen($arg);
        $args = [$arg];
        $callable = [$object, $method];
        $reflection = $this->createReflectionMethod($object, $method, ['invokeArgs']);
        $params = $reflection->getParameters();
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeArray')
            ->with($args)
            ->will($this->returnArgument(0));
        $subject->expects($this->exactly(1))
            ->method('_createReflectionForCallable')
            ->with($callable)
            ->will($this->returnValue($reflection));
        $subject->expects($this->exactly(1))
            ->method('_validateParams')
            ->with($args, $params);

        $object->expects($this->exactly(1))
            ->method($method)
            ->with($arg)
            ->will($this->returnValue($length));

        $reflection->expects($this->exactly(1))
            ->method('invokeArgs')
            ->with($object, $args)
            ->will($this->returnValue($length));

        $result = $_subject->_invokeCallable($callable, $args);
        $this->assertEquals($length, $result, 'Invocation produced a wrong result');
    }

    /*
     * Tests whether invoking a callable failure as expected when attempting to invoke something that is not callable.
     *
     * @since [*next-version*]
     */
//    public function testInvokeCallableFailureNotCallable()
//    {
//        $args = ['Apple', 'Banana'];
//        $callable = new \stdClass();
//
//        $subject = $this->createInstance();
//        $_subject = $this->reflect($subject);
//
//        $this->setExpectedException('InvalidArgumentException');
//        $result = $_subject->_invokeCallable($callable, $args);
//
//        $this->assertEquals($args, $result, 'Invocation produced a wrong result');
//    }

    /*
     * Tests whether invoking a callable fails as expected if the callable throws.
     *
     * @since [*next-version*]
     */
//    public function testInvokeCallableInvocationException()
//    {
//        $args = ['Apple', 'Banana'];
//        $innerMessage = uniqid('inner-message');
//        $exception = new RootException($innerMessage);
//        $callable = function () use ($exception) {
//            throw $exception;
//        };
//
//        $subject = $this->createInstance();
//        $_subject = $this->reflect($subject);
//
//        $subject->expects($this->exactly(1))
//            ->method('_createInvocationException')
//            ->with(
//                $this->isType('string'),
//                $this->anything(),
//                $exception,
//                $callable,
//                $args
//            );
//
//        $this->setExpectedException('Dhii\Invocation\Exception\InvocationExceptionInterface');
//        $_subject->_invokeCallable($callable, $args);
//    }
}

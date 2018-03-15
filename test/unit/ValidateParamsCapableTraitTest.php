<?php

namespace Dhii\Invocation\UnitTest;

use Dhii\Invocation\ValidateParamsCapableTrait as TestSubject;
use Dhii\Validation\Exception\ValidationFailedExceptionInterface;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class ValidateParamsCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Invocation\ValidateParamsCapableTrait';

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
     * Tests that `_validateParams()` works as expected when validation passes.
     *
     * @since [*next-version*]
     */
    public function testValidateParamsValid()
    {
        $params = [uniqid('param')];
        $spec = [uniqid('criterion')];
        $errors = [];
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_getArgsListErrors')
            ->with($params, $spec)
            ->will($this->returnValue($errors));
        $subject->expects($this->exactly(1))
            ->method('_countIterable')
            ->with($errors)
            ->will($this->returnValue(count($errors)));

        $_subject->_validateParams($params, $spec);
    }

    /**
     * Tests that `_validateParams()` works as expected when validation fails.
     *
     * @since [*next-version*]
     */
    public function testValidateParamsInvalid()
    {
        $params = [uniqid('param')];
        $spec = [uniqid('criterion')];
        $errors = [uniqid('reason')];
        $exception = $this->createValidationFailedException('Validation failed');
        $subject = $this->createInstance(['_createValidationFailedException']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_getArgsListErrors')
            ->with($params, $spec)
            ->will($this->returnValue($errors));
        $subject->expects($this->exactly(1))
            ->method('_countIterable')
            ->with($errors)
            ->will($this->returnValue(count($errors)));
        $subject->expects($this->exactly(1))
            ->method('_createValidationFailedException')
            ->with(null, null, null, null, $params, $errors)
            ->will($this->returnValue($exception));

        $this->setExpectedException('Dhii\Validation\Exception\ValidationFailedExceptionInterface');
        $_subject->_validateParams($params, $spec);
    }
}

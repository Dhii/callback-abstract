<?php

namespace Dhii\Invocation\UnitTest;

use ArrayIterator;
use stdClass;
use Traversable;
use Xpmock\TestCase;
use InvalidArgumentException;
use Dhii\Invocation\ArgsAwareTrait as TestSubject;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class ArgsAwareTraitTest extends TestCase
{
    /**
     * The name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Invocation\ArgsAwareTrait';

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
     * Creates a traversable list.
     *
     * @since [*next-version*]
     *
     * @param array $array The array with elements for the traversable.
     *
     * @return Traversable The new Traversable.
     */
    public function createTraversable(array $array)
    {
        return new ArrayIterator($array);
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
     * Tests whether `_setArgs()` works as expected.
     *
     * @since [*next-version*]
     */
    public function testSetArgs()
    {
        $args = ['Apple', 'Banana'];

        $subject = $this->createInstance(['_normalizeIterable']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeIterable')
            ->with($args)
            ->will($this->returnArgument(0));

        $_subject->args = null;
        $_subject->_setArgs($args);
        $result = $_subject->args;

        $this->assertEquals($args, $result, 'Assigned args are wrong');
    }

    /**
     * Tests whether `_getArgs()` works correctly.
     *
     * @since [*next-version*]
     */
    public function testGetArgs()
    {
        $args = [uniqid('key'), uniqid('val')];

        $exception = $this->createInvalidArgumentException('Invalid args list');
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $_subject->args = $args;
        $result = $_subject->_getArgs($args);
        $this->assertEquals($args, $result);
    }

    /**
     * Tests whether `_getArgs()` works correctly when no value was previously set.
     *
     * @since [*next-version*]
     */
    public function testGetArgsDefault()
    {
        $exception = $this->createInvalidArgumentException('Invalid args list');
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $_subject->args = null;
        $result = $_subject->_getArgs();
        $this->assertEquals([], $result, 'A wrong default args list was returned');
    }
}

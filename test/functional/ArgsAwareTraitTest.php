<?php

namespace Dhii\Invocation\FuncTest;

use ArrayIterator;
use Traversable;
use Xpmock\TestCase;
use InvalidArgumentException;
use Dhii\Invocation\ArgsAwareTrait as TestSubject;

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
     * @return object
     */
    public function createInstance()
    {
        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
            ->getMockForTrait();

        $mock->method('__')->will($this->returnArgument(0));
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function($message) {
                return new InvalidArgumentException($message);
            }
        );

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
     * Tests whether setting and retrieving args works correctly.
     *
     * @since [*next-version*]
     */
    public function testSetGetArgs()
    {
        $args = ['Apple', 'Banana'];

        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $_subject->_setArgs($args);
        $result = $_subject->_getArgs();

        $this->assertEquals($args, $result, 'Assigned args are wrong');
    }

    /**
     * Tests whether setting and retrieving args works correctly when they are a Traversable.
     *
     * @since [*next-version*]
     */
    public function testSetGetArgsTraversable()
    {
        $_args = [uniqid('arg1'), uniqid('arg2')];
        $args = $this->createTraversable($_args);

        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $_subject->_setArgs($args);
        $result = $_subject->_getArgs();

        $this->assertEquals($args, $result, 'Retrieved args (traversable) are wrong');
    }

    /**
     * Tests whether setting and retrieving args works correctly.
     *
     * @since [*next-version*]
     */
    public function testSetGetArgsFailure()
    {
        $args = new \stdClass();

        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $this->setExpectedException('InvalidArgumentException');
        $_subject->_setArgs($args);
    }
}

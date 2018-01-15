<?php

namespace Dhii\Invocation\UnitTest;

use Traversable;
use Xpmock\TestCase;
use Dhii\Invocation\InvokeCallableCapableTrait as TestSubject;
use InvalidArgumentException;

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
                ->getMockForTrait();
        $mock->method('__')
                ->will($this->returnArgument(0));
        $mock->method('_createInvalidArgumentException')
                ->will($this->returnCallback(function ($message) {
                    return new InvalidArgumentException($message);
                }));
        $mock->method('_normalizeArray')
            ->will($this->returnCallback(function($list) {
                return $list instanceof Traversable
                    ? iterator_to_array($list)
                    : $list;
            }));

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
}

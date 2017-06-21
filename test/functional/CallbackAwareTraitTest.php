<?php

namespace Dhii\Util\FuncTest;

use Dhii\Util\CallbackAwareTrait;
use Xpmock\TestCase;

/**
 * Tests {@see Dhii\Util\CallbackAwareTrait}.
 *
 * @since [*next-version*]
 */
class CallbackAwareTraitTest extends TestCase
{
    /**
     * The name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\\Util\\CallbackAwareTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @return CallbackAwareTrait
     */
    public function createInstance()
    {
        return $this->getMockForTrait(static::TEST_SUBJECT_CLASSNAME);
    }

    /**
     * Tests the callback getter and setter methods.
     *
     * @since [*next-version*]
     */
    public function testGetSetCallback()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $reflect->_setCallback($callback = function() {});

        $this->assertSame($callback, $reflect->_getCallback());
    }
}

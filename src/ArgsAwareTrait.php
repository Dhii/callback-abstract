<?php

namespace Dhii\Invocation;

use Exception as RootException;
use InvalidArgumentException;
use Traversable;
use Dhii\Util\String\StringableInterface as Stringable;

/**
 * Functionality for args awareness.
 *
 * @since [*next-version*]
 */
trait ArgsAwareTrait
{
    /**
     * A list of argument values.
     *
     * @since [*next-version*]
     *
     * @var array|Traversable
     */
    protected $args;

    /**
     * Retrieves the args associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return array|Traversable The args.
     */
    protected function _getArgs()
    {
        return $this->args;
    }

    /**
     * Assigns a args to this instance.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable $args A list of argument values.
     *
     * @throws InvalidArgumentException If the arguments list is invalid.
     */
    protected function _setArgs($args)
    {
        if (!is_array($args) && !($args instanceof Traversable)) {
            throw $this->_createInvalidArgumentException($this->__('Invalid args list'), null, null, $args);
        }

        $this->args = $args;
    }

    /**
     * Creates a new invalid argument exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message  The error message, if any.
     * @param int|null               $code     The error code, if any.
     * @param RootException|null     $previous The inner exception for chaining, if any.
     * @param mixed|null             $argument The invalid argument, if any.
     *
     * @return InvalidArgumentException The new exception.
     */
    abstract protected function _createInvalidArgumentException(
        $message = null,
        $code = null,
        RootException $previous = null,
        $argument = null
    );

    /**
     * Translates a string, and replaces placeholders.
     *
     * @since [*next-version*]
     * @see   sprintf()
     *
     * @param string $string  The format string to translate.
     * @param array  $args    Placeholder values to replace in the string.
     * @param mixed  $context The context for translation.
     *
     * @return string The translated string.
     */
    abstract protected function __($string, $args = array(), $context = null);
}

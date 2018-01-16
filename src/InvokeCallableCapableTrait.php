<?php

namespace Dhii\Invocation;

use Dhii\Util\String\StringableInterface as Stringable;
use Dhii\Invocation\Exception\InvocationExceptionInterface;
use Exception as RootException;
use InvalidArgumentException;
use Traversable;

/**
 * Functionality for invoking a callable.
 *
 * @since [*next-version*]
 */
trait InvokeCallableCapableTrait
{
    /**
     * Invokes a callable.
     *
     * @since [*next-version*]
     *
     * @param callable          $callable The callable to invoke.
     * @param array|Traversable $args     The arguments to invoke the callable with.
     *
     * @throws InvalidArgumentException If the callable is not callable.
     * @throws InvalidArgumentException if the args are not a valid list.
     * @throws RootException            For errors that happen during invocation.
     *
     * @return mixed The result of the invocation.
     */
    protected function _invokeCallable($callable, $args)
    {
        if (!is_callable($callable)) {
            throw $this->_createInvalidArgumentException($this->__('Callable is not callable'), null, null, $callable);
        }

        $args = $this->_normalizeArray($args);

        try {
            $result = call_user_func_array($callable, $args);
        }
        catch (RootException $e) {
            throw $this->_createInvocationExceptionInterface(
                $this->__('There was an error during invocation'),
                null,
                $e,
                $callable,
                $args
            );
        }

        return $result;
    }

    /**
     * Normalizes a value into an array.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable $value The value to normalize.
     *
     * @throws InvalidArgumentException If value cannot be normalized.
     *
     * @return array The normalized value.
     */
    abstract protected function _normalizeArray($value);

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
    abstract protected function __($string, $args = [], $context = null);

    /**
     * Creates a new Invocation exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message  The error message, if any.
     * @param int|null               $code     The error code, if any.
     * @param RootException|null     $previous The inner exception for chaining, if any.
     * @param callable               $callable The callable that caused the problem, if any.
     * @param Traversable|array      $args     The associated list of arguments, if any.
     *
     * @return InvocationExceptionInterface The new exception.
     */
    abstract protected function _createInvocationExceptionInterface(
        $message = null,
        $code = null,
        RootException $previous = null,
        callable $callable = null,
        $args = null
    );
}

<?php
/**
 * @license MIT
 *
 * Modified by learndash on 18-December-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace StellarWP\Learndash\Illuminate\Support;

/**
 * @mixin \StellarWP\Learndash\Illuminate\Support\Enumerable
 */
class HigherOrderCollectionProxy
{
    /**
     * The collection being operated on.
     *
     * @var \StellarWP\Learndash\Illuminate\Support\Enumerable
     */
    protected $collection;

    /**
     * The method being proxied.
     *
     * @var string
     */
    protected $method;

    /**
     * Create a new proxy instance.
     *
     * @param  \StellarWP\Learndash\Illuminate\Support\Enumerable  $collection
     * @param  string  $method
     * @return void
     */
    public function __construct(Enumerable $collection, $method)
    {
        $this->method = $method;
        $this->collection = $collection;
    }

    /**
     * Proxy accessing an attribute onto the collection items.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->collection->{$this->method}(function ($value) use ($key) {
            return is_array($value) ? $value[$key] : $value->{$key};
        });
    }

    /**
     * Proxy a method call onto the collection items.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->collection->{$this->method}(function ($value) use ($method, $parameters) {
            return $value->{$method}(...$parameters);
        });
    }
}

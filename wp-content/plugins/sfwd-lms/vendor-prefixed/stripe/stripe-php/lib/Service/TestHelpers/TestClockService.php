<?php

// File generated from our OpenAPI spec

namespace StellarWP\Learndash\Stripe\Service\TestHelpers;

/**
 * @phpstan-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 *
 * @license MIT
 * Modified by learndash on 18-December-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
/**
 * @psalm-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 */
class TestClockService extends \StellarWP\Learndash\Stripe\Service\AbstractService
{
    /**
     * Starts advancing a test clock to a specified time in the future. Advancement is
     * done when status changes to <code>Ready</code>.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\StellarWP\Learndash\Stripe\Util\RequestOptions $opts
     *
     * @throws \StellarWP\Learndash\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \StellarWP\Learndash\Stripe\TestHelpers\TestClock
     */
    public function advance($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/test_helpers/test_clocks/%s/advance', $id), $params, $opts);
    }

    /**
     * Returns a list of your test clocks.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\StellarWP\Learndash\Stripe\Util\RequestOptions $opts
     *
     * @throws \StellarWP\Learndash\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \StellarWP\Learndash\Stripe\Collection<\Stripe\TestHelpers\TestClock>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/test_helpers/test_clocks', $params, $opts);
    }

    /**
     * Creates a new test clock that can be attached to new customers and quotes.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\StellarWP\Learndash\Stripe\Util\RequestOptions $opts
     *
     * @throws \StellarWP\Learndash\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \StellarWP\Learndash\Stripe\TestHelpers\TestClock
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/test_helpers/test_clocks', $params, $opts);
    }

    /**
     * Deletes a test clock.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\StellarWP\Learndash\Stripe\Util\RequestOptions $opts
     *
     * @throws \StellarWP\Learndash\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \StellarWP\Learndash\Stripe\TestHelpers\TestClock
     */
    public function delete($id, $params = null, $opts = null)
    {
        return $this->request('delete', $this->buildPath('/v1/test_helpers/test_clocks/%s', $id), $params, $opts);
    }

    /**
     * Retrieves a test clock.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\StellarWP\Learndash\Stripe\Util\RequestOptions $opts
     *
     * @throws \StellarWP\Learndash\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \StellarWP\Learndash\Stripe\TestHelpers\TestClock
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/test_helpers/test_clocks/%s', $id), $params, $opts);
    }
}

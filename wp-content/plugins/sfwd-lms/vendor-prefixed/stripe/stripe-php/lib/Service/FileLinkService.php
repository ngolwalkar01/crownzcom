<?php

// File generated from our OpenAPI spec

namespace StellarWP\Learndash\Stripe\Service;

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
class FileLinkService extends \StellarWP\Learndash\Stripe\Service\AbstractService
{
    /**
     * Returns a list of file links.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\StellarWP\Learndash\Stripe\Util\RequestOptions $opts
     *
     * @throws \StellarWP\Learndash\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \StellarWP\Learndash\Stripe\Collection<\Stripe\FileLink>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/file_links', $params, $opts);
    }

    /**
     * Creates a new file link object.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\StellarWP\Learndash\Stripe\Util\RequestOptions $opts
     *
     * @throws \StellarWP\Learndash\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \StellarWP\Learndash\Stripe\FileLink
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/file_links', $params, $opts);
    }

    /**
     * Retrieves the file link with the given ID.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\StellarWP\Learndash\Stripe\Util\RequestOptions $opts
     *
     * @throws \StellarWP\Learndash\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \StellarWP\Learndash\Stripe\FileLink
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/file_links/%s', $id), $params, $opts);
    }

    /**
     * Updates an existing file link object. Expired links can no longer be updated.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\StellarWP\Learndash\Stripe\Util\RequestOptions $opts
     *
     * @throws \StellarWP\Learndash\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \StellarWP\Learndash\Stripe\FileLink
     */
    public function update($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/file_links/%s', $id), $params, $opts);
    }
}

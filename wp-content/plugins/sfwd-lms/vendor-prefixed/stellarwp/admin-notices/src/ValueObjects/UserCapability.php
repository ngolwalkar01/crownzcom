<?php
/**
 * @license MIT
 *
 * Modified by learndash on 18-December-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

declare(strict_types=1);


namespace StellarWP\Learndash\StellarWP\AdminNotices\ValueObjects;

/**
 * A simple VO to encapsulate a user capability and its parameters.
 *
 * @since 1.0.0
 */
class UserCapability
{
    /**
     * @var string
     */
    private $capability;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @since 1.0.0
     */
    public function __construct(string $capability, array $parameters = [])
    {
        $this->capability = $capability;
        $this->parameters = $parameters;
    }

    /**
     * Checks of the current user passes the given capability.
     *
     * @since 1.0.0
     */
    public function currentUserCan(): bool
    {
        return current_user_can($this->capability, ...$this->parameters);
    }
}
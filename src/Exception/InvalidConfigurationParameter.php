<?php

/**
 * Created by lasicka@logio.cz
 * at 11.10.2021 10:53
 */

declare(strict_types=1);

namespace JSONAPI\Exception;

use Throwable;

/**
 * Class InvalidConfigurationParameter
 *
 * @package JSONAPI\Exception
 */
class InvalidConfigurationParameter extends InvalidArgumentException
{
    /**
     * @var int
     */
    protected $code = 551;

    /**
     * InvalidConfigurationParameter constructor.
     *
     * @param string $parameter
     * @param string $expected
     * @param string $provided
     */
    public function __construct(string $parameter, string $expected, string $provided)
    {
        $message = sprintf(
            "Invalid parameter %s. Expected %s, but provided %s.",
            $parameter,
            $expected,
            $provided
        );
        parent::__construct($message);
    }
}

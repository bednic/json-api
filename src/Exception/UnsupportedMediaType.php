<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.02.2019
 * Time: 19:47
 */

namespace JSONAPI\Exception;

use Exception;
use Throwable;

/**
 * Class UnsupportedMediaType
 *
 * @package JSONAPI\Exception
 */
class UnsupportedMediaType extends Exception
{

    protected $message = "Unsupported Media Type";

    public function __construct(Throwable $previous = null)
    {
        parent::__construct($this->message, 415, $previous);
    }
}

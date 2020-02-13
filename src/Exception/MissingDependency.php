<?php

declare(strict_types=1);

namespace JSONAPI\Exception;

class MissingDependency extends JsonApiException
{
    protected $code = 551;
    protected $message = "Unknown missing dependency";
}

<?php

namespace JSONAPI\Exception\Driver;

class ReserveWordException extends DriverException
{
    protected $code = 14;
    protected $message = "Field names [id, name] are reserved. Please use different field names.";
}

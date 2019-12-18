<?php

namespace JSONAPI\Exception\Driver;

class ReservedWord extends DriverException
{
    protected $code = 536;
    protected $message = "Field names [id, name] are reserved. Please use different field names.";
}

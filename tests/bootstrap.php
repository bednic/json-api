<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

define('RESOURCES', __DIR__ . '/../tests-resources');

$_SERVER["REQUEST_URI"]    = "/getter/uuid?filter=attribute eq 'value'&include=collection&fields[resource]=publicProperty,privateProperty,relations&sort=-publicProperty,privateProperty&page[offset]=10&page[limit]=20";
$_SERVER["HTTP_HOST"]      = "unit.test.org";
$_SERVER["REQUEST_SCHEME"] = "http";
$_SERVER["REQUEST_METHOD"] = "GET";

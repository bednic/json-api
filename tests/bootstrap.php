<?php

use Doctrine\Common\Annotations\AnnotationReader;

require_once __DIR__ . '/../vendor/autoload.php';

putenv("JSON_API_URL=http://unit.test.org");

$_SERVER["REQUEST_URI"] = "/getter/uuid?filter=attribute eq 'value'&include=collection&fields[resource]=publicProperty,privateProperty,relations&sort=-publicProperty,privateProperty&page[offset]=10&page[limit]=20";
$_SERVER["HTTP_HOST"] = "unit.test.org";
$_SERVER["REQUEST_SCHEME"] = "http";
$_SERVER["REQUEST_METHOD"] = "GET";

AnnotationReader::addGlobalIgnoredName('runTestsInSeparateProcesses');

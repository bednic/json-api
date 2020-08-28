<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

define('RESOURCES', __DIR__ . '/../tests-resources');

\Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('coversDefaultClass');
\Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('mixin');

\JSONAPI\Config::$ENDPOINT           = 'http://unit.test.org/api';
\JSONAPI\Config::$INCLUSION_SUPPORT  = true;
\JSONAPI\Config::$PAGINATION_SUPPORT = true;
\JSONAPI\Config::$SORT_SUPPORT       = true;
\JSONAPI\Config::$RELATIONSHIP_DATA  = true;
\JSONAPI\Config::$MAX_INCLUDED_ITEMS = 625;
\JSONAPI\Config::$RELATIONSHIP_LIMIT = 25;

$_SERVER["REQUEST_URI"]    = "/getter/uuid?filter=attribute eq 'value'&include=collection&fields[resource]=publicProperty,privateProperty,relations&sort=-publicProperty,privateProperty&page[offset]=10&page[limit]=20";
$_SERVER["HTTP_HOST"]      = "unit.test.org";
$_SERVER["REQUEST_SCHEME"] = "http";
$_SERVER["REQUEST_METHOD"] = "GET";

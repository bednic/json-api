<?php

use Doctrine\Common\Annotations\AnnotationReader;

require_once __DIR__ . '/../vendor/autoload.php';

putenv("JSON_API_URL=http://unit.test.org/");

$_SERVER["REQUEST_URI"] = "/resource/uuid";
$_SERVER["HTTP_HOST"] = "unit.test.org";
$_SERVER["REQUEST_SCHEME"] = "http";
$_SERVER["REQUEST_METHOD"] = "GET";

$_GET['include'] = 'relations';
$_GET['fields'] = ['resource' => 'publicProperty,privateProperty,relations'];
$_GET['sort'] = '-publicProperty,privateProperty';
$_GET['page'] = [
    'offset' => 10,
    'limit' => 20
];
$_GET['filter'] = 'attribute eq \'value\'';

AnnotationReader::addGlobalIgnoredName('runTestsInSeparateProcesses');

<?php

require_once __DIR__ . '/../vendor/autoload.php';

putenv("API_ENV_URL=http://unit.test.org/");

$_SERVER["REQUEST_URI"] = "/resource/uuid";
$_SERVER["HTTP_HOST"] = "unit.test.org";
$_SERVER["REQUEST_SCHEME"] = "http";

$_GET['include'] = 'relations';
$_GET['fields'] = ['resource' => 'publicProperty,privateProperty,relations'];
$_GET['sort'] = '-publicProperty,privateProperty';
$_GET['page'] = [
    'offset' => 10,
    'limit' => 20
];
$_GET['filter'] = [
    'publicProperty' => [
        '~public',
        '!private-value',
        '>0',
        '<100',
        '[value,public,public-value]'
    ]
];

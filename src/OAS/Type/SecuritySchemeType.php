<?php

//phpcs:ignoreFile
declare(strict_types = 1);

namespace JSONAPI\OAS\Type;

enum SecuritySchemeType: string {
    case API_KEY = 'apiKey';
    case HTTP = 'http';
    case OAUTH2 = 'oauth2';
    case OPEN_ID_CONNECT = 'openIdConnect';
}

<?php
//phpcs:ignoreFile
declare(strict_types = 1);

namespace JSONAPI\OAS\Type;

enum SecuritySchemeScheme: string {
    case BASIC = 'Basic';
    case BEARER = 'Bearer';
    case DIGEST = 'Digest';
    case HOBA = 'HOBA';
    case MUTUAL = 'Mutual';
    case NEGOTIATE = 'Negotiate';
    case OAUTH = 'OAuth';
    case SCRAM_SHA_1 = 'SCRAM-SHA-1';
    case SCRAM_SHA_256 = 'SCRAM-SHA-256';
    case VAPID = 'vapid';
}

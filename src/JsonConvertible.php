<?php

declare(strict_types=1);

namespace JSONAPI;

/**
 * Interface JsonConvertible
 * Marks object as JSON convertible, thus it is possible to serialize it to JSON and then to deserialize it back
 *
 * @package JSONAPI
 * @deprecated use \Tools\JSON\JsonConvertible instead
 * @see https://gitlab.com/bednic/tools/-/blob/master/src/JSON/JsonConvertible.php
 * @version 5.1.1
 */
interface JsonConvertible extends \JsonSerializable, JsonDeserializable
{

}

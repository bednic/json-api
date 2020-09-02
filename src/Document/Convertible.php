<?php

declare(strict_types=1);

namespace JSONAPI\Document;

/**
 * Interface JsonConvertible
 * Marks object as JSON convertible, thus it is possible to serialize it to JSON and then to deserialize it back
 *
 * @package JSONAPI
 */
interface Convertible extends Serializable, Deserializable
{

}

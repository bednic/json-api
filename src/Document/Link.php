<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;

/**
 * Class Link
 *
 * @package JSONAPI\Document
 */
final class Link extends Field implements HasMeta
{
    use MetaExtension;

    public const SELF    = 'self';
    public const RELATED = 'related';
    public const FIRST   = 'first';
    public const LAST    = 'last';
    public const NEXT    = 'next';
    public const PREV    = 'prev';
    public const ABOUT   = 'about';

    /**
     * Link constructor.
     *
     * @param string      $key
     * @param string|null $uri
     * @param Meta|null   $meta
     *
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    public function __construct(string $key, ?string $uri, Meta $meta = null)
    {
        parent::__construct($key);
        $this->setData($uri);
        if ($meta) {
            $this->setMeta($meta);
        }
    }

    /**
     * @param string|null $data
     *
     * @throws ForbiddenDataType
     */
    protected function setData(mixed $data): void
    {
        if (filter_var($data, FILTER_VALIDATE_URL) || is_null($data)) {
            parent::setData($data);
        } else {
            throw new ForbiddenDataType("Data are not valid URL.");
        }
    }

    public function getData(): string | object
    {
        if ($this->meta) {
            return (object)[
                'href' => parent::getData(),
                'meta' => $this->meta
            ];
        } else {
            return parent::getData();
        }
    }
}

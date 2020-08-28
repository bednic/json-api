<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JSONAPI\Helper\MetaTrait;

/**
 * Class Link
 *
 * @package JSONAPI\Document
 */
final class Link extends Field implements HasMeta
{
    use MetaTrait;

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

    public function getData()
    {
        if ($this->meta) {
            return [
                'href' => parent::getData(),
                'meta' => $this->meta
            ];
        } else {
            return parent::getData();
        }
    }

    /**
     * @param string $data
     *
     * @throws ForbiddenDataType
     */
    protected function setData($data): void
    {
        if (filter_var($data, FILTER_VALIDATE_URL) || is_null($data)) {
            parent::setData($data);
        } else {
            throw new ForbiddenDataType("Data are not valid URL.");
        }
    }
}

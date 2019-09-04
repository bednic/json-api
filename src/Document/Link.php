<?php


namespace JSONAPI\Document;

use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JSONAPI\Exception\InvalidArgumentException;
use JSONAPI\MetaTrait;

/**
 * Class Link
 *
 * @package JSONAPI\Document
 */
class Link extends Field implements HasMeta
{
    use MetaTrait;

    /**
     * Link constructor.
     *
     * @param string    $key
     * @param string    $uri
     * @param Meta|null $meta
     *
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    public function __construct(string $key, string $uri, Meta $meta = null)
    {
        parent::__construct($key, $uri);
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
     * @throws InvalidArgumentException
     */
    protected function setData($data): void
    {
        if (!filter_var($data, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Data are not valid URL.");
        }
        parent::setData($data);
    }
}

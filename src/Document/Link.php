<?php


namespace JSONAPI\Document;

use JSONAPI\Exception\DocumentException;
use JSONAPI\Utils\MetaImpl;

/**
 * Class Link
 *
 * @package JSONAPI\Document
 */
class Link extends Field implements HasMeta
{
    use MetaImpl;

    /**
     * Link constructor.
     *
     * @param string    $key
     * @param string    $uri
     * @param Meta|null $meta
     * @throws DocumentException
     */
    public function __construct(string $key, string $uri, Meta $meta = null)
    {
        parent::__construct($key, $uri);
        if ($meta) {
            $this->setMeta($meta);
        }
    }

    /**
     * @param $data
     * @throws DocumentException
     */
    public function setData($data)
    {
        if (!filter_var($data, FILTER_VALIDATE_URL)) {
            throw new DocumentException(
                "Data type is not supported",
                DocumentException::FORBIDDEN_DATA_TYPE
            );
        }
        parent::setData($data);
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
}

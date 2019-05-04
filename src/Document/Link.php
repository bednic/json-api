<?php


namespace JSONAPI\Document;


use Doctrine\Common\Collections\ArrayCollection;
use JSONAPI\Exception\DocumentException;

class Link extends KVStore
{
    /**
     * @var Meta[]|ArrayCollection
     */
    private $metas;

    /**
     * Link constructor.
     *
     * @param string                $key
     * @param string                $uri
     * @param ArrayCollection<Meta> $metas
     * @throws DocumentException
     */
    public function __construct(string $key, string $uri, ArrayCollection $metas = null)
    {
        if (!filter_var($uri, FILTER_VALIDATE_URL)) {
            throw new DocumentException("Attribute value type is not supported",
                DocumentException::FORBIDDEN_VALUE_TYPE);
        }
        parent::__construct($key, $uri);
        $this->metas = $metas;
    }

    public function getValue()
    {
        if ($this->metas) {
            return [
                'href' => parent::getValue(),
                'meta' => $this->metas->toArray()
            ];
        } else {
            return parent::getValue();
        }
    }
}

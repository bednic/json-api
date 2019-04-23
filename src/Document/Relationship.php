<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 11.02.2019
 * Time: 12:46
 */

namespace JSONAPI\Document;

use Doctrine\Common\Collections\ArrayCollection;
use JSONAPI\Exception\EncoderException;

/**
 * Class Relationships
 * @package JSONAPI\Document
 */
class Relationship
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $isCollection = true;
    /**
     * @var ResourceIdentifier|ResourceIdentifier[]|ArrayCollection
     */
    private $data;
    /**
     * @var array|null
     */
    private $links;
    /**
     * @var array|null
     */
    private $meta;

    /**
     * Relationship constructor.
     * @param string $name
     * @param bool   $isCollection
     * @throws EncoderException
     */
    public function __construct(string $name, $isCollection = true)
    {
        if (!preg_match("/[a-zA-Z0-9-_]/", $name)) {
            throw new EncoderException("Attribute name character violation.");
        }
        $this->name = $name;
        $this->isCollection = $isCollection;
        if ($this->isCollection) {
            $this->data = new ArrayCollection();
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return ArrayCollection|ResourceIdentifier|ResourceIdentifier[]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function isCollection(): bool
    {
        return $this->isCollection;
    }
    /**
     * @param ResourceIdentifier|null $resourceIdentifier
     */
    public function addResource(?ResourceIdentifier $resourceIdentifier)
    {
        if ($this->isCollection && !$this->data->contains($resourceIdentifier)) {
            $this->data->add($resourceIdentifier);
        } else {
            $this->data = $resourceIdentifier;
        }
    }

    /**
     * @param array $links
     */
    public function setLinks(array $links)
    {
        $this->links = $links;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        $ret = [
            'data' => $this->isCollection ? $this->data->toArray() : $this->data
        ];
        if ($this->links) {
            $ret['links'] = $this->links;
        }
        if ($this->meta) {
            $ret['meta'] = $this->meta;
        }
        return $ret;
    }


}

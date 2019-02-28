<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 11.02.2019
 * Time: 12:46
 */

namespace JSONAPI\Document;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Relationships
 * @package JSONAPI\Document
 */
class Relationships extends Fields
{
    private $isCollection = true;
    /**
     * @var ResourceIdentifier|ArrayCollection|ResourceIdentifier[]
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
     * Relationships constructor.
     * @param bool                    $isCollection
     * @param ResourceIdentifier|null $resourceIdentifier
     */
    public function __construct($isCollection = true, ResourceIdentifier $resourceIdentifier = null)
    {
        parent::__construct();
        $this->isCollection = $isCollection;
        if ($this->isCollection) {
            $this->data = new ArrayCollection();
        } else {
            $this->data = $resourceIdentifier;
        }


    }

    public function addResource(ResourceIdentifier $resourceIdentifier)
    {
        if ($this->isCollection && !$this->data->contains($resourceIdentifier)) {
            $this->data->add($resourceIdentifier);
        }
    }

    public function setLinks(array $links)
    {
        $this->links = $links;
    }

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

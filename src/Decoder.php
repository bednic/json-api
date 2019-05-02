<?php


namespace JSONAPI;


use JSONAPI\Document\Attribute;
use JSONAPI\Document\Document;
use JSONAPI\Document\Relationship;
use JSONAPI\Document\Resource;
use JSONAPI\Document\ResourceIdentifier;
use Psr\Http\Message\RequestInterface;

class Decoder
{
    private $factory;

    public function __construct(MetadataFactory $factory)
    {
        $this->factory = $factory;
    }
    public function decode(RequestInterface $request): Document
    {
        $document = new Document($this->factory);
        $body = (string)$request->getBody();
        $meta = $request->getHeader('Content-Type');
        if (in_array(Document::MEDIA_TYPE, $meta)) {
            $body = json_decode($body);
            if (is_array($body->data)) {
                $data = [];
                foreach ($body->data as $resourceDto) {
                    $resource = new Resource(new ResourceIdentifier($resourceDto->type, $resourceDto->id));
                    foreach ($resourceDto->attributes as $attribute => $value) {
                        $resource->addAttribute(new Attribute($attribute, $value));
                    }

                    foreach ($resourceDto->relationships as $prop => $value) {
                        $value = $value->data;
                        $relationship = new Relationship($prop, is_array($value));
                        if ($relationship->isCollection()) {
                            foreach ($value as $item) {
                                $relationship->addResource(new ResourceIdentifier($item->type, $item->id));
                            }
                        } else {
                            $relationship->addResource(new ResourceIdentifier($value->type, $value->id));
                        }
                        $resource->addRelationship($relationship);
                    }
                    $data[] = $resource;
                }
                $document->setData($data);
            } else {
                $resource = new Resource(new ResourceIdentifier($body->data->type, $body->data->id));
                foreach ($body->data->attributes as $attribute => $value) {
                    $resource->addAttribute(new Attribute($attribute, $value));
                }
                foreach ($body->data->relationships as $prop => $value) {
                    $value = $value->data;
                    $relationship = new Relationship($prop, is_array($value));
                    if ($relationship->isCollection()) {
                        foreach ($value as $item) {
                            $relationship->addResource(new ResourceIdentifier($item->type, $item->id));
                        }
                    } else {
                        $relationship->addResource(new ResourceIdentifier($value->type, $value->id));
                    }
                    $resource->addRelationship($relationship);
                }
                $document->setData($resource);
            }
        } else {
            throw new UnsupportedMediaType();
        }
        return $document;
    }
}

<?php

/**
 * Created by tomas
 * at 20.03.2021 21:13
 */

declare(strict_types=1);

namespace JSONAPI\Encoding;

use DateTimeInterface;
use JSONAPI\Document\Attribute;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\Fieldset\FieldsetInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class AttributesProcessor
 *
 * @package JSONAPI\Encoding
 */
class AttributesProcessor extends FieldsProcessor implements Processor
{

    private MetadataRepository $repository;
    private LoggerInterface $logger;

    /**
     * AttributesProcessor constructor.
     *
     * @param MetadataRepository     $repository
     * @param LoggerInterface|null   $logger
     * @param FieldsetInterface|null $fieldset
     */
    public function __construct(
        MetadataRepository $repository,
        LoggerInterface $logger = null,
        FieldsetInterface $fieldset = null
    ) {
        parent::__construct($fieldset);
        $this->repository = $repository;
        $this->logger     = $logger ?? new NullLogger();
    }

    public function process(
        ResourceObjectIdentifier | ResourceObject $resource,
        object $object
    ): ResourceObjectIdentifier | ResourceObject {
        if ($resource instanceof ResourceObject) {
            $metadata = $this->repository->getByType($resource->getType());
            foreach ($metadata->getAttributes() as $attribute) {
                if ($this->showField($attribute, $resource)) {
                    $value = $this->getValue($attribute, $object);
                    if ($value instanceof DateTimeInterface) {
                        // ISO 8601
                        $value = $value->format(DATE_ATOM);
                    }
                    $this->logger->debug("Adding attribute {$attribute->name}.");
                    $resource->addAttribute(new Attribute($attribute->name, $value));
                }
            }
        }
        return $resource;
    }
}

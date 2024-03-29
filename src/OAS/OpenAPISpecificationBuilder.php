<?php

declare(strict_types=1);

namespace JSONAPI\OAS;

use Fig\Http\Message\StatusCodeInterface;
use JSONAPI\Configuration;
use JSONAPI\Document\Document;
use JSONAPI\Document\Field;
use JSONAPI\Document\KeyWord;
use JSONAPI\Document\Link;
use JSONAPI\Document\Meta;
use JSONAPI\Exception\Metadata\MetadataNotFound;
use JSONAPI\Exception\OAS\DuplicationEntryException;
use JSONAPI\Exception\OAS\ExclusivityCheckException;
use JSONAPI\Exception\OAS\InvalidArgumentException;
use JSONAPI\Exception\OAS\InvalidFormatException;
use JSONAPI\Exception\OAS\OpenAPIException;
use JSONAPI\Exception\OAS\ReferencedObjectNotExistsException;
use JSONAPI\Metadata\Attribute;
use JSONAPI\Metadata\ClassMetadata;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\Metadata\Relationship;
use JSONAPI\OAS\Type\DataType;
use JSONAPI\OAS\Type\In;
use JSONAPI\OAS\Type\Style;
use Psr\Log\LoggerInterface;

use function Symfony\Component\String\u;

/**
 * Class OpenApiSpecificationBuilder
 *
 * @package JSONAPI\OAS\Factory
 */
final class OpenAPISpecificationBuilder
{
    /**
     * @var MetadataRepository
     */
    private MetadataRepository $metadataRepository;
    /**
     * @var OpenAPISpecification
     */
    private OpenAPISpecification $oas;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    private bool $supportInclusion;
    private bool $supportSort;
    private bool $supportPagination;
    private string $baseUrl;

    /**
     * OpenAPISpecificationBuilder constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->metadataRepository = $configuration->getMetadataRepository();
        $this->logger             = $configuration->getLogger();
        $this->supportInclusion   = $configuration->isSupportInclusion();
        $this->supportSort        = $configuration->isSupportSort();
        $this->supportPagination  = $configuration->isSupportPagination();
        $this->baseUrl            = $configuration->getBaseURL();
    }

    /**
     * @param Info $info
     *
     * @return OpenAPISpecification
     * @throws DuplicationEntryException
     * @throws ExclusivityCheckException
     * @throws InvalidArgumentException
     * @throws InvalidFormatException
     * @throws MetadataNotFound
     * @throws ReferencedObjectNotExistsException
     */
    public function create(Info $info): OpenAPISpecification
    {
        $this->oas = new OpenAPISpecification($info);
        $this->logger->debug("OAS instance created");
        $server = new Server($this->baseUrl);
        $this->logger->debug('Default server added');
        $server->setDescription('API Server');
        $this->oas->addServer($server);

        $this->registerSchemas();
        $this->logger->debug("Schemas registered");
        $this->registerResponses();
        $this->logger->debug("Responses registered");
        $this->registerParameters();
        $this->logger->debug("Parameters registered");
        $this->registerPaths();
        $this->logger->debug("Paths registered");

        return $this->oas;
    }

    /**
     * @throws MetadataNotFound
     * @throws ReferencedObjectNotExistsException
     * @throws InvalidArgumentException
     */
    private function registerSchemas(): void
    {
        // META
        $meta = DataType::object()
                        ->setAdditionalProperties(true)
                        ->setReadOnly(true)
                        ->setDescription('Where specified, a meta member can be used to include non-standard meta-information.');
        $this->oas->getComponents()->addSchema(self::shortName(Meta::class), $meta);

        // LINKS
        $this->oas->getComponents()->addSchema(self::shortName(Link::class), $this->createLink());
        $links = DataType::object()
                         ->setReadOnly(true)
                         ->addProperty('self', $this->oas->getComponents()->createSchemaReference(self::shortName(Link::class)))
                         ->addProperty('related', $this->oas->getComponents()->createSchemaReference(self::shortName(Link::class)))
                         ->setAdditionalProperties($this->oas->getComponents()->createSchemaReference(self::shortName(Link::class)));
        $this->oas->getComponents()->addSchema('links', $links);

        foreach ($this->metadataRepository->getAll() as $classMetadata) {
            $shortName = self::shortName($classMetadata->getClassName());
            $this->oas->getComponents()->addSchema($shortName, $this->createResource($classMetadata));
        }
    }

    private static function shortName(string $className): string
    {
        return u($className)->afterLast('\\')->toString();
    }

    /**
     * @return Schema
     * @throws ReferencedObjectNotExistsException
     */
    private function createLink(): Schema
    {
        return Schema::new()->setOneOf(
            [
                DataType::string()->setFormat('url'),
                DataType::object()
                        ->addProperty('href', DataType::string()->setFormat('url'))
                        ->addProperty(
                            'meta',
                            $this->oas->getComponents()->createSchemaReference(self::shortName(Meta::class))
                        )
            ]
        );
    }

    /**
     * @param ClassMetadata $metadata
     *
     * @return Schema
     * @throws MetadataNotFound
     * @throws ReferencedObjectNotExistsException
     */
    private function createResource(ClassMetadata $metadata): Schema
    {
        $attributes = DataType::object();
        foreach ($metadata->getAttributes() as $attribute) {
            $attributes->addProperty($attribute->name, $this->attributeToSchema($attribute));
        }
        $relationships = DataType::object();
        foreach ($metadata->getRelationships() as $relationship) {
            $relationships->addProperty($relationship->name, $this->relationshipToSchema($relationship));
        }
        $schema = DataType::object()
                          ->addProperty('attributes', $attributes)
                          ->addProperty('relationships', $relationships)
                          ->addProperty('links', $this->oas->getComponents()->createSchemaReference('links'));
        return Schema::new()->setAllOf(
            [
                $this->createResourceIdentifier($metadata),
                $schema
            ]
        );
    }

    /**
     * @param Attribute $attribute
     *
     * @return Schema
     */
    private function attributeToSchema(Attribute $attribute): Schema
    {
        $schema = new Schema();
        $type   = $this->translateType($attribute->type);
        $schema->setType($type);
        if ($type == 'integer') {
            $schema->setFormat(PHP_INT_SIZE === 4 ? 'int32' : 'int64');
        } elseif ($type == 'array') {
            $schema->setItems((new Schema())->setType($this->translateType($attribute->of)));
        } elseif ($type == 'number') {
            $schema->setFormat('float');
        }
        if ($attribute->nullable === true) {
            $schema->setNullable(true);
        }
        return $schema;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function translateType(string $type): string
    {
        return match ($type) {
            'string' => 'string',
            'int'    => 'integer',
            'bool'   => 'boolean',
            'array'  => 'array',
            'float'  => 'number',
            default  => 'object',
        };
    }

    /**
     * @param Relationship $relationship
     *
     * @return Schema
     * @throws ReferencedObjectNotExistsException
     * @throws MetadataNotFound
     */
    private function relationshipToSchema(Relationship $relationship): Schema
    {
        $schema = DataType::object();
        if ($relationship->isCollection) {
            $schema->addProperty(
                'data',
                DataType::array(
                    $this->createResourceIdentifier(
                        $this->metadataRepository->getByClass($relationship->target)
                    )
                )
            );
        } else {
            $schema->addProperty(
                'data',
                $this->createResourceIdentifier($this->metadataRepository->getByClass($relationship->target))
            );
        }
        $schema->addProperty('meta', $this->oas->getComponents()->createSchemaReference(self::shortName(Meta::class)));
        $schema->addProperty('links', $this->oas->getComponents()->createSchemaReference('links'));
        return $schema;
    }

    /**
     * @param ClassMetadata $metadata
     *
     * @return Schema
     * @throws OpenAPIException
     */
    private function createResourceIdentifier(ClassMetadata $metadata): Schema
    {
        return DataType::object()
                       ->addProperty(Field::ID, DataType::string())
                       ->addProperty(Field::TYPE, DataType::string()->setEnum([$metadata->getType()]))
                       ->addProperty('meta', $this->oas->getComponents()->createSchemaReference(self::shortName(Meta::class)))
                       ->setRequired(['id', 'type']);
    }

    /**
     * @throws ReferencedObjectNotExistsException
     */
    private function registerResponses(): void
    {
        $this->oas->getComponents()
                  ->addResponse(
                      (string)StatusCodeInterface::STATUS_ACCEPTED,
                      new Response('The request has been accepted for processing, but the processing has not been completed')
                  )
                  ->addResponse(
                      (string)StatusCodeInterface::STATUS_NO_CONTENT,
                      new Response('The request has been successfully processed, but is not returning any content')
                  )->addResponse(
                      (string)StatusCodeInterface::STATUS_FORBIDDEN,
                      new Response('The request was a legal request, but the server is refusing to respond to it')
                  )->addResponse(
                      (string)StatusCodeInterface::STATUS_NOT_FOUND,
                      new Response('The requested page could not be found but may be available again in the future')
                  )->addResponse(
                      (string)StatusCodeInterface::STATUS_CONFLICT,
                      new Response('The request could not be completed because of a conflict in the request')
                  )->addResponse(
                      (string)StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
                      $this->createErrorResponse()
                  );
    }

    /**
     * @return Response
     * @throws ReferencedObjectNotExistsException
     */
    private function createErrorResponse(): Response
    {
        $document = $this->createEmptyDocument();
        $document->addProperty(
            'errors',
            DataType::array(
                DataType::object()
                        ->addProperty(
                            'id',
                            DataType::string()
                                    ->setDescription('A unique identifier for this particular occurrence of the problem')
                        )
                        ->addProperty(
                            'links',
                            DataType::object()
                                    ->addProperty(
                                        'about',
                                        $this->createLink()
                                             ->setDescription(
                                                 'A link that leads to further details
                            about this particular occurrence of the problem'
                                             )
                                    )
                        )
                        ->addProperty(
                            'status',
                            DataType::string()
                                    ->setDescription(
                                        'The HTTP status code applicable to this problem, expressed as a string value'
                                    )
                        )
                        ->addProperty('code', DataType::string())
                        ->addProperty('title', DataType::string())
                        ->addProperty('detail', DataType::string())
                        ->addProperty(
                            'source',
                            DataType::object()
                                    ->addProperty('pointer', DataType::string()->setFormat('JSON Pointer'))
                                    ->addProperty('parameter', DataType::string())
                        )
                        ->addProperty(
                            'meta',
                            $this->oas->getComponents()->createSchemaReference(self::shortName(Meta::class))
                        )
            )
        );
        $response = new Response('A generic error message, given when no more specific message is suitable');
        $response->addContent(
            Document::MEDIA_TYPE,
            (new MediaType())->setSchema(
                $document
            )
        );
        return $response;
    }

    /**
     * @return Schema
     * @throws ReferencedObjectNotExistsException
     */
    private function createEmptyDocument(): Schema
    {
        $document = DataType::object();
        $document->addProperty('jsonapi', $this->createJsonApiObject());
        $document->addProperty(
            'meta',
            $this->oas->getComponents()->createSchemaReference(self::shortName(Meta::class))
        );
        $document->addProperty('links', $this->oas->getComponents()->createSchemaReference('links'));
        return $document;
    }

    private function createJsonApiObject(): Schema
    {
        return DataType::object()
                       ->addProperty('version', DataType::string()->setEnum([Document::VERSION]))
                       ->addProperty('meta', $this->oas->getComponents()->createSchemaReference(self::shortName(Meta::class)));
    }

    /**
     * @throws InvalidArgumentException
     * @throws ExclusivityCheckException
     * @throws DuplicationEntryException
     */
    private function registerParameters(): void
    {
        // PARAMETERS
        $id = new Parameter('id', In::PATH);
        $id->setDescription('Id of resource');
        $id->setSchema(DataType::string());
        $this->oas->getComponents()->addParameter('id', $id);

        $inclusion = new Parameter('include', In::QUERY);
        $inclusion->setStyle(Style::FORM);
        $inclusion->setExplode(false);
        $inclusion->setDescription(
            'An endpoint MAY also support an **include** request parameter to allow the
        client to customize which related resources should be returned'
        );
        $inclusion->setSchema(DataType::array(DataType::string()));
        $inclusion->setExample(['resource.relationship']);
        $this->oas->getComponents()->addParameter('include', $inclusion);

        $fields = new Parameter('fields', In::QUERY);
        $fields->setStyle(Style::DEEP_OBJECT);
        $fields->setExplode(true);
        $fields->setDescription(
            'A client MAY request that an endpoint return only specific **fields** in the
        response on a per-type basis by including a fields[TYPE] parameter.'
        );
        $fields->setSchema(DataType::object()->setAdditionalProperties(DataType::string()));
        $fields->setExample(['resource' => 'attribute,relationship']);
        $this->oas->getComponents()->addParameter('fields', $fields);

        $sort = new Parameter('sort', In::QUERY);
        $sort->setStyle(Style::FORM);
        $sort->setExplode(false);
        $sort->setDescription(
            'An endpoint MAY support requests to sort the primary data with a **sort** query
        parameter. The value for sort MUST represent sort fields.'
        );
        $sort->setSchema(DataType::array(DataType::string()));
        $sort->setExample(['attributeASC', '-attributeDESC']);
        $this->oas->getComponents()->addParameter('sort', $sort);

        $pagination = new Parameter('page', In::QUERY);
        $pagination->setDescription(
            'The **page** query parameter is reserved for pagination. Servers and clients
        SHOULD use this key for pagination operations.'
        );
        $pagination->setStyle(Style::DEEP_OBJECT);
        $pagination->setExplode(true);
        $pagination->setSchema(DataType::object()->setAdditionalProperties(DataType::string()));
        $pagination->setExample(['page[offset]' => 0, 'page[limit]' => 25]);
        $this->oas->getComponents()->addParameter('pagination', $pagination);

        $filter = new Parameter('filter', In::QUERY);
        $filter->setDescription(
            'The **filter** query parameter is reserved for filtering data. Servers and
        clients SHOULD use this key for filtering operations.'
        );
        $filter->setAllowReserved(true);
        $filter->setSchema(DataType::string());
        $this->oas->getComponents()->addParameter('filter', $filter);
    }

    /**
     * @throws InvalidFormatException
     * @throws MetadataNotFound
     * @throws ReferencedObjectNotExistsException
     * @throws DuplicationEntryException
     */
    private function registerPaths(): void
    {
        foreach ($this->metadataRepository->getAll() as $classMetadata) {
            $shortName = self::shortName($classMetadata->getClassName());

            // COLLECTION
            $collection         = '/' . $classMetadata->getType();
            $collectionPathItem = new PathItem();
            $getOperation       = new Operation();

            $getOperation->addParameter($this->oas->getComponents()->createParameterReference('filter'));
            $getOperation->addParameter($this->oas->getComponents()->createParameterReference('fields'));
            if ($this->supportPagination) {
                $getOperation->addParameter($this->oas->getComponents()->createParameterReference('pagination'));
            }
            if ($this->supportInclusion) {
                $getOperation->addParameter($this->oas->getComponents()->createParameterReference('include'));
            }
            if ($this->supportSort) {
                $getOperation->addParameter($this->oas->getComponents()->createParameterReference('sort'));
            }

            $getOperation->setResponses(
                $this->createReadResponses()->addResponse(
                    (string)StatusCodeInterface::STATUS_OK,
                    $this->createDocumentResponse(
                        DataType::array(
                            $this->oas->getComponents()->createSchemaReference($shortName)
                        )
                    )
                )
            );
            $collectionPathItem->setGet($getOperation);
            if (!$classMetadata->isReadOnly()) {
                // POST
                $postOperation = new Operation();
                $postOperation->setRequestBody($this->createRequestBodyFor($shortName));
                $header = new Header('Location');
                $header->setDescription('New resource endpoint url.');
                $header->setSchema(DataType::string()->setFormat('url'));
                $postOperation->setResponses(
                    $this->createCreateResponses()->addResponse(
                        (string)StatusCodeInterface::STATUS_CREATED,
                        $this->createDocumentResponse($this->oas->getComponents()->createSchemaReference($shortName))
                             ->addHeader('Location', $header)
                    )
                );
                $collectionPathItem->setPost($postOperation);
            }
            $this->oas->getPaths()->addPath($collection, $collectionPathItem);

            //SINGLE
            $single         = $collection . '/{id}';
            $singlePathItem = new PathItem();
            $singlePathItem->addParameter($this->oas->getComponents()->createParameterReference('id'));
            $getOperation = Operation::new()
                                     ->addParameter($this->oas->getComponents()->createParameterReference('fields'))
                                     ->setResponses(
                                         $this->createReadResponses()->addResponse(
                                             (string)StatusCodeInterface::STATUS_OK,
                                             $this->createDocumentResponse($this->oas->getComponents()->createSchemaReference($shortName))
                                         )
                                     );
            if ($this->supportInclusion) {
                $getOperation->addParameter($this->oas->getComponents()->createParameterReference('include'));
            }
            $singlePathItem->setGet($getOperation);
            if (!$classMetadata->isReadOnly()) {
                $singlePathItem->setPatch(
                    Operation::new()->addParameter($this->oas->getComponents()->createParameterReference('fields'))
                             ->setRequestBody($this->createRequestBodyFor($shortName))
                             ->setResponses(
                                 $this->createUpdateResponses()->addResponse(
                                     (string)StatusCodeInterface::STATUS_OK,
                                     $this->createDocumentResponse(
                                         $this->oas->getComponents()->createSchemaReference($shortName)
                                     )
                                 )
                             )
                );
                $singlePathItem->setDelete(
                    Operation::new()->setResponses($this->createDeleteResponses())
                );
            }
            $this->oas->getPaths()->addPath($single, $singlePathItem);

            foreach ($classMetadata->getRelationships() as $relationship) {
                $relationshipPathItem = new PathItem();
                $relationshipPathItem->addParameter($this->oas->getComponents()->createParameterReference('id'));
                $relationshipsUrl = $single . '/relationships/' . $relationship->name;

                $relationPathItem = new PathItem();
                $relationPathItem->addParameter($this->oas->getComponents()->createParameterReference('id'));
                $relationUrl = $single . '/' . $relationship->name;

                $relMetadata = $this->metadataRepository->getByClass($relationship->target);

                if ($relationship->isCollection) { // toMany
                    // GET, POST, PATCH, DELETE
                    $relationshipPathItem->setGet(
                        Operation::new()
                                 ->setResponses(
                                     $this->createReadResponses()->addResponse(
                                         (string)StatusCodeInterface::STATUS_OK,
                                         $this->createDocumentResponse(
                                             DataType::array($this->createResourceIdentifier($relMetadata))
                                         )
                                     )
                                 )
                    );
                    $relationshipPathItem->setPost(
                        Operation::new()
                                 ->setRequestBody($this->createRequestBodyFor(self::shortName($relationship->target)))
                                 ->setResponses(
                                     $this->createCreateResponses()->addResponse(
                                         (string)StatusCodeInterface::STATUS_OK,
                                         $this->createDocumentResponse(
                                             DataType::array(
                                                 $this->createResourceIdentifier(
                                                     $this->metadataRepository->getByClass($relationship->target)
                                                 )
                                             )
                                         )
                                     )
                                 )
                    );
                    $relationshipPathItem->setPatch(
                        Operation::new()
                                 ->setRequestBody($this->createRequestBodyFor(self::shortName($relationship->target)))
                                 ->setResponses(
                                     $this->createCreateResponses()->addResponse(
                                         (string)StatusCodeInterface::STATUS_OK,
                                         $this->createDocumentResponse(
                                             DataType::array(
                                                 $this->createResourceIdentifier(
                                                     $this->metadataRepository->getByClass($relationship->target)
                                                 )
                                             )
                                         )
                                     )
                                 )
                    );
                    $relationshipPathItem->setDelete(
                        Operation::new()->setResponses($this->createDeleteResponses())
                    );

                    $relationPathItem->setGet(
                        Operation::new()->setResponses(
                            $this->createReadResponses()
                                 ->addResponse(
                                     (string)StatusCodeInterface::STATUS_OK,
                                     $this->createDocumentResponse(
                                         DataType::array($this->createResourceIdentifier($relMetadata))
                                     )
                                 )
                        )
                    );
                } else { // toOne
                    //PATCH, GET
                    $relationshipPathItem->setGet(
                        Operation::new()
                                 ->setResponses(
                                     $this->createReadResponses()->addResponse(
                                         (string)StatusCodeInterface::STATUS_OK,
                                         $this->createDocumentResponse(
                                             $this->createResourceIdentifier($relMetadata)
                                         )
                                     )
                                 )
                    );
                    $relationshipPathItem->setPatch(
                        Operation::new()
                                 ->setRequestBody($this->createRequestBodyFor(self::shortName($relationship->target)))
                                 ->setResponses(
                                     $this->createCreateResponses()->addResponse(
                                         (string)StatusCodeInterface::STATUS_OK,
                                         $this->createDocumentResponse(
                                             $this->createResourceIdentifier(
                                                 $this->metadataRepository->getByClass($relationship->target)
                                             )
                                         )
                                     )
                                 )
                    );

                    $relationPathItem->setGet(
                        Operation::new()->setResponses(
                            $this->createReadResponses()->addResponse(
                                (string)StatusCodeInterface::STATUS_OK,
                                $this->createDocumentResponse($this->createResourceIdentifier($relMetadata))
                            )
                        )
                    );
                }
                $this->oas->getPaths()->addPath($relationshipsUrl, $relationshipPathItem);
                $this->oas->getPaths()->addPath($relationUrl, $relationPathItem);
            }
        }
    }

    /**
     * 200, 404, 500
     * @return Responses
     * @throws ReferencedObjectNotExistsException
     */
    private function createReadResponses(): Responses
    {
        $responses = new Responses();
        $responses->addResponse(
            (string)StatusCodeInterface::STATUS_NOT_FOUND,
            $this->oas->getComponents()->createResponseReference((string)StatusCodeInterface::STATUS_NOT_FOUND)
        );
        $responses->addResponse(
            (string)StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
            $this->oas->getComponents()
                      ->createResponseReference((string)StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR)
        );
        return $responses;
    }

    /**
     * @param Schema $data
     *
     * @return Response
     * @throws ReferencedObjectNotExistsException
     */
    private function createDocumentResponse(Schema $data): Response
    {
        $document = $this->createEmptyDocument();
        $document->addProperty('data', $data);
        $response = new Response('Returns data of successful request');
        $response->addContent(
            Document::MEDIA_TYPE,
            (new MediaType())->setSchema(
                $document
            )
        );
        return $response;
    }

    /**
     * @param string $shortClassName
     *
     * @return RequestBody
     * @throws ReferencedObjectNotExistsException
     */
    private function createRequestBodyFor(string $shortClassName): RequestBody
    {
        $content = new MediaType();
        $content->setSchema(
            DataType::object()
                    ->addProperty('jsonapi', $this->createJsonApiObject())
                    ->addProperty('data', $this->oas->getComponents()->createSchemaReference($shortClassName))
                    ->setRequired(['data'])
        );
        return new RequestBody(Document::MEDIA_TYPE, $content);
    }

    /**
     * 201,202,204,403,404,409,500
     * @return Responses
     * @throws ReferencedObjectNotExistsException
     */
    private function createCreateResponses(): Responses
    {
        $responses = $this->createReadResponses();
        $responses->addResponse(
            (string)StatusCodeInterface::STATUS_ACCEPTED,
            $this->oas->getComponents()->createResponseReference((string)StatusCodeInterface::STATUS_ACCEPTED)
        );
        $responses->addResponse(
            (string)StatusCodeInterface::STATUS_NO_CONTENT,
            $this->oas->getComponents()->createResponseReference((string)StatusCodeInterface::STATUS_NO_CONTENT)
        );
        $responses->addResponse(
            (string)StatusCodeInterface::STATUS_FORBIDDEN,
            $this->oas->getComponents()->createResponseReference((string)StatusCodeInterface::STATUS_FORBIDDEN)
        );
        $responses->addResponse(
            (string)StatusCodeInterface::STATUS_NOT_FOUND,
            $this->oas->getComponents()->createResponseReference((string)StatusCodeInterface::STATUS_NOT_FOUND)
        );
        $responses->addResponse(
            (string)StatusCodeInterface::STATUS_CONFLICT,
            $this->oas->getComponents()->createResponseReference((string)StatusCodeInterface::STATUS_CONFLICT)
        );
        return $responses;
    }

    /**
     * 200,202,204,403,404,409,500
     * @return Responses
     * @throws ReferencedObjectNotExistsException
     */
    private function createUpdateResponses(): Responses
    {
        return $this->createCreateResponses();
    }

    /**
     * 202,204,200,404,500
     * @return Responses
     * @throws ReferencedObjectNotExistsException
     */
    private function createDeleteResponses(): Responses
    {
        $responses = $this->createReadResponses();
        $responses->addResponse(
            (string)StatusCodeInterface::STATUS_OK,
            $this->createEmptyResponse()
        );
        $responses->addResponse(
            (string)StatusCodeInterface::STATUS_ACCEPTED,
            $this->oas->getComponents()->createResponseReference((string)StatusCodeInterface::STATUS_ACCEPTED)
        );
        $responses->addResponse(
            (string)StatusCodeInterface::STATUS_NO_CONTENT,
            $this->oas->getComponents()->createResponseReference((string)StatusCodeInterface::STATUS_NO_CONTENT)
        );
        return $responses;
    }

    /**
     * @return Response
     * @throws ReferencedObjectNotExistsException
     */
    private function createEmptyResponse(): Response
    {
        $document = $this->createEmptyDocument();
        $response = new Response('Response when request was successful but there is no resource data to return');
        $response->addContent(
            Document::MEDIA_TYPE,
            (new MediaType())->setSchema(
                $document
            )
        );
        return $response;
    }
}

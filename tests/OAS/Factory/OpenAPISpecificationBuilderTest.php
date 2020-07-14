<?php

declare(strict_types=1);

namespace JSONAPI\Test\OAS\Factory;

use Doctrine\Common\Cache\ArrayCache;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\OAS\Contact;
use JSONAPI\OAS\ExternalDocumentation;
use JSONAPI\OAS\Factory\OpenAPISpecificationBuilder;
use JSONAPI\OAS\Info;
use JSONAPI\OAS\License;
use JSONAPI\OAS\OpenAPISpecification;
use PHPUnit\Framework\TestCase;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;
use Swaggest\JsonSchema\Schema;

/**
 * Class OpenAPISpecificationBuilderTest
 *
 * @package JSONAPI\Test\OAS\Factory
 */
class OpenAPISpecificationBuilderTest extends TestCase
{
    /**
     * @var MetadataRepository
     */
    private static MetadataRepository $mr;
    /**
     * @var Schema
     */
    private static $validator;

    public static function setUpBeforeClass(): void
    {
        self::$mr        = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new SimpleCacheAdapter(new ArrayCache()),
            new AnnotationDriver()
        );
        self::$validator = Schema::import(
            json_decode(file_get_contents(RESOURCES . DIRECTORY_SEPARATOR . 'openapi-v3.0.json'))
        );
    }

    public function testCreate()
    {
        $factory = new OpenAPISpecificationBuilder(self::$mr);

        $info = new Info('JSON:API OAS', '1.0.0');
        $info->setDescription('Test specification');
        $info->setContact(
            (new Contact())
                ->setName('Tomas Benedikt')
                ->setEmail('tomas.benedikt@gmail.com')
                ->setUrl('https://gitlab.com/bednic')
        );
        $info->setLicense(
            (new License('MIT'))
                ->setUrl('https://gitlab.com/bednic/json-api/-/blob/5.x/LICENSE')
        );
        $info->setTermsOfService('https://gitlab.com/bednic/json-api/-/blob/5.x/CONTRIBUTING.md');

        $oas = $factory->create($info);
        $oas->setExternalDocs(new ExternalDocumentation('https://gitlab.com/bednic/json-api/-/wikis/home'));

        $json = json_encode($oas);

        $this->assertIsString($json);

        self::$validator->in(json_decode($json));

        $this->assertInstanceOf(OpenAPISpecification::class, $oas);
    }
}

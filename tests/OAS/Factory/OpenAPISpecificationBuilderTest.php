<?php

declare(strict_types=1);

namespace JSONAPI\Test\OAS\Factory;

use JSONAPI\Configuration;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\OAS\Contact;
use JSONAPI\OAS\ExternalDocumentation;
use JSONAPI\OAS\OpenAPISpecificationBuilder;
use JSONAPI\OAS\Info;
use JSONAPI\OAS\License;
use JSONAPI\OAS\OpenAPISpecification;
use PHPUnit\Framework\TestCase;
use Swaggest\JsonSchema\Schema;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

/**
 * Class OpenAPISpecificationBuilderTest
 *
 * @package JSONAPI\Test\OAS\Factory
 */
class OpenAPISpecificationBuilderTest extends TestCase
{
    /**
     * @var Schema
     */
    private static $validator;
    /**
     * @var Configuration configuration
     */
    private static Configuration $configuration;

    public static function setUpBeforeClass(): void
    {
        $mr                  = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new Psr16Cache(new ArrayAdapter()),
            new AnnotationDriver()
        );
        $baseUrl             = 'http://unit.test.org';
        self::$validator     = Schema::import(
            json_decode(file_get_contents(RESOURCES . DIRECTORY_SEPARATOR . 'openapi-v3.0.json'))
        );
        self::$configuration = new Configuration($mr, $baseUrl);
    }

    public function testCreate()
    {
        $factory = new OpenAPISpecificationBuilder(self::$configuration);

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

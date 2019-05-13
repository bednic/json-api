<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.04.2019
 * Time: 12:59
 */

namespace Test\JSONAPI;

use JSONAPI\Exception\FactoryException;
use JSONAPI\Metadata\ClassMetadata;
use JSONAPI\Metadata\MetadataFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class MetadataFactoryTest
 *
 * @package Test\JSONAPI
 */
class MetadataFactoryTest extends TestCase
{

    public function testConstruct()
    {
        $factory = new MetadataFactory(__DIR__ . '/resources/');
        $this->assertInstanceOf(MetadataFactory::class, $factory);
        return $factory;
    }

    public function testExceptionBadPath()
    {
        $this->expectException(FactoryException::class);
        new MetadataFactory('');
    }

    /**
     * @depends testConstruct
     */
    public function testExceptionClassIsNotResource(MetadataFactory $factory)
    {
        $this->expectException(FactoryException::class);
        $factory->getMetadataByClass('NonExistingClass');
    }

    /**
     * @depends testConstruct
     */
    public function testGetMetadataClassByType(MetadataFactory $factory)
    {
        $this->assertInstanceOf(ClassMetadata::class, $factory->getMetadataClassByType('resource'));
    }

    /**
     * @depends testConstruct
     */
    public function testGetClassByType(MetadataFactory $factory)
    {
        $this->assertEquals(ObjectExample::class, $factory->getClassByType('resource'));
    }

    /**
     * @depends testConstruct
     */
    public function testGetMetadataByClass(MetadataFactory $factory)
    {
        $this->assertInstanceOf(ClassMetadata::class, $factory->getMetadataByClass(ObjectExample::class));
    }

    /**
     * @depends testConstruct
     */
    public function testGetAllMetadata(MetadataFactory $factory)
    {
        $this->assertCount(2, $factory->getAllMetadata());
    }
}

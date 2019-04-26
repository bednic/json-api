<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.04.2019
 * Time: 12:59
 */

namespace Test\JSONAPI;

use JSONAPI\ClassMetadata;
use JSONAPI\Exception\FactoryException;
use JSONAPI\MetadataFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class MetadataFactoryTest
 * @package Test\JSONAPI
 */
class MetadataFactoryTest extends TestCase
{

    public function test__construct()
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
     * @depends test__construct
     */
    public function testExceptionClassIsNotResource(MetadataFactory $factory)
    {
        $this->expectException(FactoryException::class);
        $factory->getMetadataByClass('NonExistingClass');
    }

    /**
     * @depends test__construct
     */
    public function testGetMetadataClassByType(MetadataFactory $factory)
    {
        $this->assertInstanceOf(ClassMetadata::class, $factory->getMetadataClassByType('resource'));
    }

    /**
     * @depends test__construct
     */
    public function testGetClassByType(MetadataFactory $factory)
    {
        $this->assertEquals(ObjectExample::class, $factory->getClassByType('resource'));
    }

    /**
     * @depends test__construct
     */
    public function testGetMetadataByClass(MetadataFactory $factory)
    {
        $this->assertInstanceOf(ClassMetadata::class, $factory->getMetadataByClass(ObjectExample::class));
    }

    /**
     * @depends test__construct
     */
    public function testGetAllMetadata(MetadataFactory $factory)
    {
        $this->assertCount(2, $factory->getAllMetadata());
    }
}

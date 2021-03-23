<?php

declare(strict_types=1);

namespace JSONAPI\Test\OAS;

use JSONAPI\OAS\PathItem;
use JSONAPI\OAS\Paths;
use PHPUnit\Framework\TestCase;

class PathsTest extends TestCase
{
    /**
     * @var Paths
     */
    private Paths $paths;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paths = new Paths();

        $this->paths->addPath('/existing', new PathItem());
    }

    public function testExists()
    {
        $this->assertTrue($this->paths->exists('/existing'));

        $this->assertFalse($this->paths->exists('/missing'));
    }

    public function testGetPath()
    {
        $this->assertInstanceOf(PathItem::class, $this->paths->getPath('/existing'));

        $this->assertNull($this->paths->getPath('/missing'));
    }
}

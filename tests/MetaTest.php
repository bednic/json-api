<?php

namespace JSONAPI\Test;

use JSONAPI\Document\Field;
use JSONAPI\Document\Meta;
use PHPUnit\Framework\TestCase;

/**
 * Class MetaTest
 *
 * @package JSONAPI\Test
 */
class MetaTest extends TestCase
{

    public function testConstruct()
    {
        $meta = new Meta([
            'key' => 'value'
        ]);
        $this->assertIsArray($meta->jsonSerialize());
        $this->assertCount(1, $meta->jsonSerialize());
    }

    public function testAddField()
    {
        $meta = new Meta();
        $meta->setProperty('key', 'value');
        $this->assertCount(1, $meta->jsonSerialize());
        $this->assertArrayHasKey('key', $meta->jsonSerialize());
    }
}

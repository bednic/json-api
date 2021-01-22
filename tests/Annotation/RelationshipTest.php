<?php

/**
 * Created by tomas
 * at 22.01.2021 18:56
 */

declare(strict_types=1);

namespace JSONAPI\Test\Annotation;

use JSONAPI\Annotation\Relationship;
use JSONAPI\Test\Resources\Valid\GettersExample;
use PHPUnit\Framework\TestCase;

class RelationshipTest extends TestCase
{

    public function testConstruct()
    {
        $rel = new Relationship(GettersExample::class, 'relationship', 'property', 'getter', 'setter', true);
        $this->assertEquals(GettersExample::class, $rel->target);
        $this->assertEquals('relationship', $rel->name);
        $this->assertEquals('property', $rel->property);
        $this->assertEquals('getter', $rel->getter);
        $this->assertEquals('setter', $rel->setter);
        $this->assertTrue($rel->isCollection);
    }
}

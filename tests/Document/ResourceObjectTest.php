<?php

declare(strict_types=1);

namespace JSONAPI\Test\Document;

use JSONAPI\Document\Attribute;
use JSONAPI\Document\Id;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\Type;
use JSONAPI\Exception\Document\AlreadyInUse;
use PHPUnit\Framework\TestCase;

class ResourceObjectTest extends TestCase
{

    public function testAlreadyInUse()
    {
        $this->expectException(AlreadyInUse::class);
        $o = new ResourceObject(new Type('type'), new Id('id'));
        $o->addAttribute(new Attribute('type', 'data'));
    }
}

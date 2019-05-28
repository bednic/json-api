<?php

namespace JSONAPI\Test;

use JSONAPI\Document\Link;
use JSONAPI\Document\Meta;
use JSONAPI\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
    public function testCreateLink()
    {
        $link = new Link('uri', 'http://unit.test.org');
        $this->assertEquals('uri', $link->getKey());
        $this->assertEquals('http://unit.test.org', $link->getData());
    }

    public function testBadLink()
    {
        $this->expectException(InvalidArgumentException::class);
        new Link('uri', 'bad url');
    }

    public function testLinkWithMeta()
    {
        $link = new Link('url', 'http://unit.test.org', new Meta(['key' => 'value']));
        $this->assertArrayHasKey('href', $link->getData());
        $this->assertArrayHasKey('meta', $link->getData());
    }

}

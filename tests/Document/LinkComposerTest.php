<?php
/**
 * Created by tomas
 * 18.05.2022 22:14
 */

declare(strict_types=1);

namespace JSONAPI\Test\Document;

use JSONAPI\Document\Id;
use JSONAPI\Document\LinkComposer;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\Type;
use PHPUnit\Framework\TestCase;

class LinkComposerTest extends TestCase
{
    public function testIssue45()
    {
        $c = new LinkComposer("http://localhost");
        // this is ok
        $c->setResourceLink(new ResourceObject(new Type("products"), new Id("123")));
        // this fails with:
        // ForbiddenDataType: Assigned data to self has forbidden data type Data are not valid URL..
        $c->setResourceLink(new ResourceObject(new Type("products"), new Id("a product")));
        $this->assertTrue(true);
    }
}

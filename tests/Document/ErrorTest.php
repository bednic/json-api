<?php

declare(strict_types=1);

namespace JSONAPI\Test\Document;

use Fig\Http\Message\StatusCodeInterface;
use JSONAPI\Document\Error;
use JSONAPI\Document\Link;
use JSONAPI\Document\Meta;
use JSONAPI\Uri\QueryPartInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class ErrorTest
 *
 * @package JSONAPI\Test\Document
 */
class ErrorTest extends TestCase
{

    public function testSetters()
    {
        $source = Error\Source::parameter(QueryPartInterface::SORT_PART_KEY);
        $error  = new Error();
        $error->setMeta(new Meta(['custom' => 'property']));
        $error->setId('id');
        $error->setStatus(StatusCodeInterface::STATUS_OK);
        $error->setDetail('detail');
        $error->setTitle('title');
        $error->setCode('code');
        $error->setSource($source);
        $error->setLink(new Link(Link::ABOUT, 'http://about.error.com'));
        $json = $error->jsonSerialize();
        $this->assertObjectHasAttribute('id', $json);
        $this->assertEquals('id', $json->id);
        $this->assertObjectHasAttribute('status', $json);
        $this->assertEquals(200, $json->status);
        $this->assertObjectHasAttribute('detail', $json);
        $this->assertEquals('detail', $json->detail);
        $this->assertObjectHasAttribute('title', $json);
        $this->assertEquals('title', $json->title);
        $this->assertObjectHasAttribute('code', $json);
        $this->assertEquals('code', $json->code);
        $this->assertObjectHasAttribute('source', $json);
        $this->assertObjectHasAttribute('meta', $json);
        $this->assertObjectHasAttribute('links', $json);
    }

}

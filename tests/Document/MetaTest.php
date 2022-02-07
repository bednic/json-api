<?php

declare(strict_types=1);

namespace JSONAPI\Test\Document;

use JSONAPI\Document\Meta;
use JSONAPI\Exception\Document\ForbiddenCharacter;
use PHPUnit\Framework\TestCase;

class MetaTest extends TestCase
{
    public function testSetProperty()
    {
        $meta = new Meta();
        $meta->setProperty('null', null);
        $meta->setProperty('bool', false);
        $meta->setProperty('int', 1);
        $meta->setProperty('string', 'asdf');
        $meta->setProperty('double', 1.1);
        $meta->setProperty('array', [1, 'a' => 1]);
        $meta->setProperty('double', (object)['prop' => 'value']);
        $meta->setProperty('s', 'short');
        $this->assertInstanceOf(Meta::class, $meta);
    }

    public function forbiddenCharProvider()
    {
        return [
            [
                [' ' => 'forbidden']
            ],
            [
                ['+' => 'forbidden']
            ],
            [
                [',' => 'forbidden']
            ],
            [
                ['.' => 'forbidden']
            ],
            [
                ['[' => 'forbidden']
            ],
            [
                [']' => 'forbidden']
            ],
            [
                ['!' => 'forbidden']
            ],
            [
                ['"' => 'forbidden']
            ],
            [
                ['#' => 'forbidden']
            ],
            [
                ['$' => 'forbidden']
            ],
            [
                ['%' => 'forbidden']
            ],
            [
                ['&' => 'forbidden']
            ],
            [
                ['\'' => 'forbidden']
            ],
            [
                ['(' => 'forbidden']
            ],
            [
                [')' => 'forbidden']
            ],
            [
                ['*' => 'forbidden']
            ],
            [
                ['/' => 'forbidden']
            ],
            [
                [':' => 'forbidden']
            ],
            [
                [';' => 'forbidden']
            ],
            [
                ['<' => 'forbidden']
            ],
            [
                ['=' => 'forbidden']
            ],
            [
                ['>' => 'forbidden']
            ],
            [
                ['?' => 'forbidden']
            ],
            [
                ['@' => 'forbidden']
            ],
            [
                ['\\' => 'forbidden']
            ],
            [
                ['^' => 'forbidden']
            ],
            [
                ['`' => 'forbidden']
            ],
            [
                ['{' => 'forbidden']
            ],
            [
                ['|' => 'forbidden']
            ],
            [
                ['}' => 'forbidden']
            ],
            [
                ['~' => 'forbidden']
            ]
        ];
    }

    /**
     * @dataProvider forbiddenCharProvider
     */
    public function testForbiddenChar($params)
    {
        $this->expectException(ForbiddenCharacter::class);
        $meta = new Meta($params);
    }

    public function testConstruct()
    {
        $meta = new Meta();
        $this->assertInstanceOf(Meta::class, $meta);
        $meta = new Meta([]);
        $this->assertInstanceOf(Meta::class, $meta);
        $meta = new Meta(['prop' => 'value']);
        $this->assertInstanceOf(Meta::class, $meta);
    }

    public function testIsEmpty()
    {
        $meta = new Meta();
        $this->assertTrue($meta->isEmpty());
        $meta->setProperty('prop', 'value');
        $this->assertFalse($meta->isEmpty());
    }

    public function testJsonSerialize()
    {
        $meta = new Meta();
        $meta->setProperty('null', null);
        $meta->setProperty('bool', false);
        $meta->setProperty('int', 1);
        $meta->setProperty('string', 'asdf');
        $meta->setProperty('double', 1.1);
        $meta->setProperty('array', [1, 'a' => 1]);
        $meta->setProperty('double', (object)['prop' => 'value']);
        $this->assertIsArray($meta->jsonSerialize());
    }
}

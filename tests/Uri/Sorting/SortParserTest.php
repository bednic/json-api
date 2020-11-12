<?php

declare(strict_types=1);

namespace JSONAPI\Test\Uri\Sorting;

use JSONAPI\Exception\Http\MalformedParameter;
use JSONAPI\Uri\Sorting\SortParser;
use PHPUnit\Framework\TestCase;

class SortParserTest extends TestCase
{

    public function testParse()
    {
        $parser = new SortParser();
        $parser->parse('-created,title,-dotted.att');
        $expected = [
            'created'    => SortParser::DESC,
            'title'      => SortParser::ASC,
            'dotted.att' => SortParser::DESC
        ];
        $this->assertEquals($expected, $parser->getOrder());
    }

    public function testEmpty()
    {
        $parser = new SortParser();
        $parser->parse('');
        $expected = [];
        $this->assertEquals($expected, $parser->getOrder());
    }

    public function badDataProvider()
    {
        return [
            ['-qwe-asdf*qwefasdf*+asdfqwef'],
            ['*asdf,asdf,-asdf']
        ];
    }

    /**
     * @dataProvider badDataProvider
     */
    public function testBadRequest($data)
    {
        $this->expectException(MalformedParameter::class);
        $parser = new SortParser();
        $parser->parse($data);
    }
}

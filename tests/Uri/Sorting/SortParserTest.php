<?php

declare(strict_types=1);

namespace JSONAPI\Test\URI\Sorting;

use JSONAPI\Exception\Http\MalformedParameter;
use JSONAPI\URI\Sorting\SortParser;
use PHPUnit\Framework\TestCase;

class SortParserTest extends TestCase
{
    public function goodDataProvider()
    {
        return [
            ['0', ['0' => SortParser::ASC]],
            ['00', ['00' => SortParser::ASC]],
            ['0-0', ['0-0' => SortParser::ASC]],
            ['-asdf', ['asdf' => SortParser::DESC]],
            ['asdf', ['asdf' => SortParser::ASC]],
            ['asdf.asdf', ['asdf.asdf' => SortParser::ASC]],
            ['-asdf.asdf', ['asdf.asdf' => SortParser::DESC]],
            ['asdf-asdf', ['asdf-asdf' => SortParser::ASC]],
            ['-asdf.asdf-asdf', ['asdf.asdf-asdf' => SortParser::DESC]],
            ['-asdf_asdf-asdf.asdf', ['asdf_asdf-asdf.asdf' => SortParser::DESC]]
        ];
    }

    /**
     * @dataProvider goodDataProvider
     */
    public function testParse($data, $result)
    {
        $parser = new SortParser();
        $parser->parse($data);
        $this->assertEquals($result, $parser->getOrder());
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
            ['*adsf'],
            ['--'],
            ['-'],
            ['.'],
            ['-.'],
            ['asdf-'],
            ['asdf.'],
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

<?php

namespace JSONAPI\Test;

use Doctrine\Common\Collections\Criteria;
use JSONAPI\Query\Filter\CriteriaFilterParser;
use PHPUnit\Framework\TestCase;

class CriteriaFilterParserTest extends TestCase
{
    /**
     * @depends test__construct
     */
    public function testParse(CriteriaFilterParser $parser)
    {
        $case1 = 'propA eq 1 and (propB lt 2 or propC gt 3) and 4 neq propD and in(propE,5,6,7)';
        $parser->parse($case1);
        $criteria = $parser->getCondition();
        $expected = Criteria::create()->where(
            Criteria::expr()->andX(
                Criteria::expr()->andX(
                    Criteria::expr()->andX(
                        Criteria::expr()->eq('propA', 1),
                        Criteria::expr()->orX(
                            Criteria::expr()->lt('propB', 2),
                            Criteria::expr()->gt('propC', 3)
                        )
                    ),
                    Criteria::expr()->neq('propD', 4)
                ),
                Criteria::expr()->in('propE', [5, 6, 7])
            )
        );
        $this->assertInstanceOf(Criteria::class, $criteria);
        $this->assertEquals($expected->getWhereExpression(), $criteria->getWhereExpression());
    }

    /**
     * @depends test__construct
     */
    public function testGetCondition(CriteriaFilterParser $parser)
    {
        $this->assertInstanceOf(Criteria::class, $parser->getCondition());
    }

    public function test__construct()
    {
        $instance = new CriteriaFilterParser();
        $this->assertInstanceOf(CriteriaFilterParser::class, $instance);
        return $instance;
    }
}

<?php

/**
 * Created by tomas
 * at 23.01.2021 22:28
 */

declare(strict_types=1);

namespace JSONAPI\Test\Data;

use AssertionError;
use JSONAPI\Data\Collection;
use JSONAPI\Exception\Data\CollectionException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Traversable;

class CollectionTest extends TestCase
{


    public function dummyData()
    {
        $items = [];
        for ($i = 0; $i < 10; $i++) {
            $item = new stdClass();
            $item->id = $i;
            $item->value = "value" . ($i % 2);
            $items[] = $item;
        }
        return [
            [$items]
        ];
    }

    public function testGetIterator()
    {
        $collection = new Collection();
        $this->assertInstanceOf(Traversable::class, $collection->getIterator());
    }

    public function testOffsetSet()
    {
        $collection = new Collection();
        $collection->offsetSet(9, new stdClass());
        $this->assertIsObject($collection[9]);
        $collection->offsetSet('a', new stdClass());
        $this->assertIsObject($collection['a']);
        $collection->offsetSet(null, new stdClass());
        $this->assertIsObject($collection[null]);
    }

    /**
     * @dataProvider dummyData
     */
    public function testSlice($items)
    {
        $collection = new Collection($items);
        $this->assertEquals(5, $collection->slice(0, 5)->count());
        $this->assertEquals(0, $collection->slice(99, 1)->count());
    }

    /**
     * @dataProvider dummyData
     */
    public function testOffsetGet($items)
    {
        $collection = new Collection($items);
        $this->assertEquals(0, $collection[0]->id);
        $this->expectError();
        $collection[10];
    }

    /**
     * @dataProvider dummyData
     */
    public function testValues($items)
    {
        $collection = new Collection($items);
        $this->assertIsArray($collection->values());
        $this->assertCount(10, $collection->values());
    }

    public function testGet()
    {
        $collection = new Collection(['key' => new stdClass()]);
        $this->assertIsObject($collection->get('key'));
        $this->expectError();
        $collection->get('non-existing-key');
    }

    public function testSet()
    {
        $collection = new Collection();
        $collection->set('key', new stdClass());
        $this->assertArrayHasKey('key', $collection);
    }

    /**
     * @dataProvider dummyData
     */
    public function testHas($items)
    {
        $item = new stdClass();
        $item->id = 0;
        $item->value = 'value0';
        $collection = new Collection($items);
        $this->assertTrue($collection->has($item));
    }

    public function testOffsetExists()
    {
        $collection = new Collection(['key' => new stdClass()]);
        $this->assertTrue($collection->offsetExists('key'));
        $this->assertFalse($collection->offsetExists('non-existing'));
    }

    /**
     * @dataProvider dummyData
     */
    public function testFilter($items)
    {
        $collection = new Collection($items);
        $filter = function ($item) {
            return $item->id < 5;
        };
        $this->assertCount(5, $collection->filter($filter));
    }

    public function testUnset()
    {
        $collection = new Collection(['key' => new stdClass()]);
        $this->assertArrayHasKey('key', $collection);
        $collection->unset('key');
        $this->assertArrayNotHasKey('key', $collection);
    }

    public function testPush()
    {
        $collection = new Collection();
        $this->assertCount(0, $collection);
        $collection->push(new stdClass());
        $this->assertCount(1, $collection);
    }

    public function testHasKey()
    {
        $collection = new Collection(['key' => new stdClass()]);
        $this->assertTrue($collection->hasKey('key'));
        $this->assertFalse($collection->hasKey('non-existing-key'));
    }

    public function testOffsetUnset()
    {
        $item = new stdClass();
        $collection = new Collection([1 => $item]);
        $this->assertEquals($item, $collection[1]);
        $collection->offsetUnset(1);
        $this->expectError();
        $collection[1];
    }

    /**
     * @dataProvider dummyData
     */
    public function testToArray($items)
    {
        $collection = new Collection($items);
        $this->assertIsArray($collection->toArray());
    }

    /**
     * @dataProvider dummyData
     */
    public function testCount($items)
    {
        $collection = new Collection($items);
        $this->assertEquals(10, $collection->count());
    }

    public function testConstruct()
    {
        $collection = new Collection();
        $this->assertInstanceOf(Collection::class, $collection);
        $collection = new Collection([]);
        $this->assertInstanceOf(Collection::class, $collection);
    }

    /**
     * @dataProvider dummyData
     */
    public function testReset($items)
    {
        $collection = new Collection($items);
        $this->assertCount(10, $collection);
        $collection->reset();
        $this->assertCount(0, $collection);
    }

    /**
     * @dataProvider dummyData
     */
    public function testOrderBy($items)
    {
        $collection = new Collection($items);
        $collection->orderBy(
            [
                'value' => Collection::SORT_ASC,
                'id'    => Collection::SORT_DESC
            ]
        );
        $objects = $collection->toArray();

        $first = array_shift($objects);
        $this->assertTrue($first->id === 8);
        $this->assertTrue($first->value === 'value0');
        $last = array_pop($objects);
        $this->assertTrue($last->id === 1);
        $this->assertTrue($last->value === 'value1');
    }

    /**
     * @dataProvider dummyData
     */
    public function testSort($items)
    {
        $collection = new Collection([1, 5, 2, 6, 8, 9, 7, 3, 4]);
        $collection->sort();
        $this->assertTrue($collection->values() === [1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $collection = new Collection($items);
        $this->expectException(CollectionException::class);
        $collection->sort();
    }
}

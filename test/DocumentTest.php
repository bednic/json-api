<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.03.2019
 * Time: 15:53
 */

use JSONAPI\Document\Document;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    /**
     * @var Document
     */
    protected $document;

    protected function setUp()
    {
        $this->document = new Document();
    }

    public function testAddLink()
    {
        $this->document->addLink('key','link');
    }

    public function testSetData()
    {

    }

    public function testGetIncludes()
    {

    }

    public function testAddError()
    {

    }

    public function testSetIncludes()
    {

    }

    public function testAddMeta()
    {

    }
}

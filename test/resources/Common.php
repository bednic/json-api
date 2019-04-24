<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.04.2019
 * Time: 12:53
 */

namespace Test\JSONAPI;

use JSONAPI\Annotation as API;

/**
 * Class Common
 * @package Test\JSONAPI
 */
abstract class Common
{

    /**
     * @var string
     */
    protected $id;

    public function __construct(string $id = null)
    {
        $this->id = $id;
    }

    /**
     * @API\Id
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}

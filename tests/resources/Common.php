<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.04.2019
 * Time: 12:53
 */

namespace JSONAPI\Test;

use JSONAPI\Annotation as API;

/**
 * Class Common
 *
 * @package JSONAPI\Test
 */
abstract class Common
{

    /**
     * @var string
     */
    protected $id;

    /**
     * @API\Id
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function __construct(string $id = null)
    {
        if ($id !== null) {
            $this->id = $id;
        }
    }
}

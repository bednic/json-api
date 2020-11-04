<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Sorting;

use JSONAPI\Uri\QueryPartInterface;

/**
 * Interface SortInterface
 *
 * @package JSONAPI\Uri\Sorting
 */
interface SortInterface extends QueryPartInterface
{

    public const ASC = 'ASC';
    public const DESC = 'DESC';

    /**
     * @return array associative array contains field as key and order as value
     * @example [
     *      "fieldA" => "DESC",
     *      "fieldB" => "ASC"
     * ]
     */
    public function getOrder(): array;
}

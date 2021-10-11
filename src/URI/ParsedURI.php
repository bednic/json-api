<?php

/**
 * Created by lasicka@logio.cz
 * at 06.10.2021 13:35
 */

declare(strict_types=1);

namespace JSONAPI\URI;

use JSONAPI\URI\Fieldset\FieldsetInterface;
use JSONAPI\URI\Filtering\FilterInterface;
use JSONAPI\URI\Inclusion\InclusionInterface;
use JSONAPI\URI\Pagination\PaginationInterface;
use JSONAPI\URI\Path\PathInterface;
use JSONAPI\URI\Sorting\SortInterface;

/**
 * Interface ParsedURI
 *
 * @package JSONAPI\URI
 */
interface ParsedURI
{
    /**
     * @return FilterInterface
     */
    public function getFilter(): FilterInterface;

    /**
     * @return PaginationInterface
     */
    public function getPagination(): PaginationInterface;

    /**
     * @return SortInterface
     */
    public function getSort(): SortInterface;

    /**
     * @return FieldsetInterface
     */
    public function getFieldset(): FieldsetInterface;

    /**
     * @return InclusionInterface

     */
    public function getInclusion(): InclusionInterface;

    /**
     * @return PathInterface
     */
    public function getPath(): PathInterface;
}

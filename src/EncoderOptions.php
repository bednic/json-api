<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 16.04.2019
 * Time: 15:45
 */

namespace JSONAPI;


use JSONAPI\Filter\Query;

class EncoderOptions
{
    private $fullLinkage = false;
    private $query = null;

    public function __construct(bool $fullLinkage = false, Query $query = null)
    {
        $this->fullLinkage = $fullLinkage;
        $this->query = $query ? $query : new Query();
    }

    /**
     * @return bool
     */
    public function isFullLinkage(): bool
    {
        return $this->fullLinkage;
    }

    /**
     * @return Query
     */
    public function getQuery(): Query
    {
        return $this->query;
    }

}

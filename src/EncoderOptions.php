<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 16.04.2019
 * Time: 15:45
 */

namespace JSONAPI;


use JSONAPI\Filter\Filter;

class EncoderOptions
{
    private $fullLinkage = false;
    private $filter = null;

    public function __construct(bool $fullLinkage = false, Filter $filter = null)
    {
        $this->fullLinkage = $fullLinkage;
        $this->filter = $filter;
    }

    /**
     * @return bool
     */
    public function isFullLinkage(): bool
    {
        return $this->fullLinkage;
    }

    /**
     * @return Filter|null
     */
    public function getFilter(): ?Filter
    {
        return $this->filter;
    }

}

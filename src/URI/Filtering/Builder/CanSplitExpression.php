<?php

namespace JSONAPI\URI\Filtering\Builder;

use JSONAPI\Data\Collection;

interface CanSplitExpression
{
    public function getFieldsExpressions(): Collection;
}

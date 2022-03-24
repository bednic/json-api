<?php

namespace JSONAPI\URI\Filtering;

use JSONAPI\Data\Collection;

interface CanSplitExpression
{

    public function getFieldsExpressions(): Collection;
}

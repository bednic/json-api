<?php

namespace JSONAPI\URI\Filtering\QData;

use JSONAPI\Data\Collection;
use JSONAPI\URI\Filtering\FilterInterface;

class QuatrodotResult implements FilterInterface
{
    /**
     * @param string|null     $origin
     * @param mixed           $condition
     * @param Collection|null $identifierExpressions
     */
    public function __construct(
        private ?string $origin = null,
        private mixed $condition = null,
        private ?Collection $identifierExpressions = null
    ) {
    }


    /**
     * @inheritDoc
     */
    public function getCondition(): mixed
    {
        return $this->condition;
    }

    /**
     * @param string $identifier
     *
     * @return mixed
     */
    public function getPartialCondition(string $identifier): mixed
    {
        if ($this->identifierExpressions->hasKey($identifier)) {
            return $this->identifierExpressions->get($identifier);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->origin ?? '';
    }
}

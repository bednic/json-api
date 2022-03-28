<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering;

use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\Path\PathInterface;

abstract class Parser
{
    /**
     * @var MetadataRepository
     */
    protected MetadataRepository $repository;
    /**
     * @var PathInterface
     */
    protected PathInterface $path;

    /**
     * @param MetadataRepository $repository
     */
    public function __construct(MetadataRepository $repository)
    {
        $this->repository = $repository;
    }
}

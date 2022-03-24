<?php

/**
 * Created by uzivatel
 * at 02.03.2022 14:30
 */

declare(strict_types=1);

namespace JSONAPI\URI\Filtering\Builder;

use ExpressionBuilder\Ex;
use ExpressionBuilder\Expression\Field;
use JSONAPI\Data\Collection;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\Filtering\UseDottedIdentifier;
use JSONAPI\URI\Path\PathParser;

use function Symfony\Component\String\u;

class DQLExpressionBuilder extends RichExpressionBuilder implements UseDottedIdentifier
{
    private Collection $requiredJoins;
    /**
     * @var MetadataRepository metadataRepository
     */
    private MetadataRepository $metadataRepository;
    /**
     * @var PathParser pathParser
     */
    private PathParser $pathParser;

    /**
     * @param MetadataRepository $metadataRepository
     * @param PathParser         $pathParser
     */
    public function __construct(MetadataRepository $metadataRepository, PathParser $pathParser)
    {
        $this->requiredJoins = new Collection();
        $this->metadataRepository = $metadataRepository;
        $this->pathParser         = $pathParser;
    }

    /**
     * @param string $identifier
     *
     * @return mixed
     * @throws MetadataException
     */
    public function parseIdentifier(string $identifier): Field
    {
        /*
         * |=========================|
         * | CASES                   |
         * |=========================|
         * | field                   |
         * |-------------------------|
         * | embedded.field          |
         * |-------------------------|
         * | relation.field          |
         * |-------------------------|
         * | relation.embedded.field |
         * |-------------------------|
         */
        $metadata = $this->metadataRepository->getByType($this->pathParser->getPrimaryResourceType());
        $field    = [];
        $prefix   = u($metadata->getType())->snake()->toString();
        $parts    = explode('.', $identifier);
        while ($part = array_shift($parts)) {
            if ($metadata->hasRelationship($part)) {
                $relationship                             = $metadata->getRelationship($part);
                $this->requiredJoins[$relationship->name] = $prefix . '.' . $part;
                $prefix                                   = $relationship->name;
                $metadata                                 = $this->metadataRepository->getByClass(
                    $relationship->target
                );
            } elseif ($metadata->hasAttribute($part)) {
                $field[] = $part;
            } else {
                $field[] = $part;
            }
        }
        return Ex::field($prefix . '.' . join('.', $field));
    }

    public function getRequiredJoins(): Collection
    {
        return $this->requiredJoins;
    }
}

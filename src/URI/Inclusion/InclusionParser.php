<?php

declare(strict_types=1);

namespace JSONAPI\URI\Inclusion;

/**
 * Class InclusionParser
 *
 * @package JSONAPI\URI\Inclusion
 */
class InclusionParser implements InclusionInterface
{
    /**
     * @var Inclusion[]
     */
    private array $inclusions = [];

    /**
     * @todo: remove this and replace it with generating URI part by recursive array walk
     * @var string|null
     */
    private ?string $data = null;


    /**
     * @param string|null $data
     *
     * @return InclusionInterface
     */
    public function parse(?string $data): InclusionInterface
    {
        $this->inclusions = [];
        $this->data = $data;
        if ($data && strlen($data) > 0) {
            $t = explode(',', $data);
            foreach ($t as $i) {
                $branch = [];
                self::dot2tree($branch, $i, []);
                foreach ($branch as $rel => $sub) {
                    $this->inclusions[] = $parent = new Inclusion($rel);
                    if ($sub) {
                        $this->makeInclusionTree($parent, $sub);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param array  $arr
     * @param string $path
     * @param array  $value
     */
    private static function dot2tree(array &$arr, string $path, array $value)
    {
        $keys = explode('.', $path);
        foreach ($keys as $key) {
            $arr = &$arr[$key];
        }
        $arr = $value;
    }

    /**
     * @param Inclusion $parent
     * @param array     $branch
     */
    private function makeInclusionTree(Inclusion $parent, array $branch)
    {
        foreach ($branch as $name => $sub) {
            $child = new Inclusion($name);
            $parent->addInclusion($child);
            if ($sub) {
                $this->makeInclusionTree($child, $sub);
            }
        }
    }

    /**
     * @return array
     */
    public function getInclusions(): array
    {
        return $this->inclusions;
    }

    /**
     * @return bool
     */
    public function hasInclusions(): bool
    {
        return count($this->inclusions) > 0;
    }

    /**
     * @param string $relation
     *
     * @return bool
     */
    public function contains(string $relation): bool
    {
        return array_key_exists($relation, $this->inclusions);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->data ? 'include=' . $this->data : '';
    }
}

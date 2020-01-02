<?php

namespace JSONAPI\Uri\Inclusion;

/**
 * Class InclusionParser
 *
 * @package JSONAPI\Uri\Inclusion
 */
class InclusionParser implements InclusionInterface
{
    /**
     * @var Inclusion[]
     */
    private array $inclusions = [];

    /**
     * @todo: remove this and replace it with generating URI part by recursive array walk
     * @var string
     */
    private string $data = '';


    /**
     * @param $data
     *
     * @return InclusionInterface
     */
    public function parse(string $data): InclusionInterface
    {
        $this->inclusions = [];
        $this->data = $data;
        if (strlen($data) > 0) {
            $tree = [];
            $t = explode(',', $data);
            foreach ($t as $i) {
                self::dot2tree($tree, $i, []);
            }
            foreach ($tree as $root => $branch) {
                $this->inclusions[] = $parent = new Inclusion($root);
                if ($branch) {
                    $this->makeInclusionTree($parent, $branch);
                }
            }
        }
        return $this;
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
     * @param $arr
     * @param $path
     * @param $value
     */
    private static function dot2tree(&$arr, $path, $value)
    {
        $keys = explode('.', $path);
        foreach ($keys as $key) {
            $arr = &$arr[$key];
        }
        $arr = $value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return strlen($this->data) ? 'include=' . $this->data : '';
    }
}

<?php

namespace JSONAPI\Uri\Inclusion;

use Doctrine\Common\Collections\ArrayCollection;
use JSONAPI\Exception\InvalidArgumentException;
use JSONAPI\Uri\UriParser;

class InclusionParser implements UriParser
{
    /**
     * @var array
     */
    private $includes = [];
    /**
     * @todo: remove this and replace it with generating URI part by recursive array walk
     * @var string
     */
    private $data = '';


    /**
     * @param $data
     *
     * @throws InvalidArgumentException
     */
    public function parse($data): void
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException('Parameter query must by string.');
        }
        $this->data = $data;
        $this->includes = [];
        $t = explode(',', $data);
        foreach ($t as $i) {
            self::dot2tree($this->includes, $i, []);
        }
    }

    /**
     * @return array
     */
    public function getIncludes(): array
    {
        return $this->includes;
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
    public function __toString()
    {
        return 'include=' . $this->data;
    }

}

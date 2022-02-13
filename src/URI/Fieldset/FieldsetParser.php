<?php

declare(strict_types=1);

namespace JSONAPI\URI\Fieldset;

/**
 * Class FieldsetParser
 *
 * @package JSONAPI\URI\Fieldset
 */
class FieldsetParser implements FieldsetInterface, FieldsetParserInterface
{
    /**
     * @var array<string, array<string>>
     */
    private array $fields = [];

    /**
     * @inheritDoc
     */
    public function parse(?array $data): FieldsetInterface
    {
        $this->fields = [];
        if ($data) {
            foreach ($data as $type => $fields) {
                $this->fields[$type] = array_map(
                    function ($item) {
                        return trim($item);
                    },
                    explode(',', $fields)
                );
            }
        }
        return $this;
    }

    public function showField(string $type, string $fieldName): bool
    {
        if (!isset($this->fields[$type])) {
            return true;
        } elseif (in_array($fieldName, $this->fields[$type])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $str = '';
        foreach ($this->fields as $type => $fields) {
            $str .= (strlen($str) > 0 ? '&' : '') . rawurlencode('fields[' . $type . ']') . '='
                . implode(',', $fields);
        }
        return $str;
    }
}

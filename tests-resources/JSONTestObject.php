<?php

declare(strict_types=1);

namespace JSONAPI\Test\Resources;

use JSONAPI\Document\Convertible;

class JSONTestObject implements Convertible
{

    private string $key = 'value';


    public static function jsonDeserialize($json): self
    {
        $instance = new self();
        if (is_array($json)) {
            $instance->key = $json['key'];
        } else {
            $instance->key = $json->key;
        }
        return $instance;
    }

    public function jsonSerialize(): array
    {
        return ['key' => $this->key];
    }
}

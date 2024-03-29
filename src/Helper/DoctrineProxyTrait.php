<?php

declare(strict_types=1);

namespace JSONAPI\Helper;

/**
 * Trait DoctrineProxyTrait
 *
 * This is known hack to remove proxy prefix from Doctrine Proxy entities.
 * Unfortunately there is no way how to do it right & clean.
 *
 * @package JSONAPI\Helper
 */
trait DoctrineProxyTrait
{
    /**
     * @param string $class
     *
     * @return string
     */
    private static function clearDoctrineProxyPrefix(string $class): string
    {
        $marker = '__CG__';
        if (false === $pos = strrpos($class, '\\' . $marker . '\\')) {
            return $class;
        }
        return substr($class, $pos + strlen($marker) + 2);
    }
}

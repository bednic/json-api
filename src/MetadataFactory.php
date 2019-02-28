<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 14:57
 */

namespace JSONAPI;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Util\ClassUtils;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Driver\IDriver;
use JSONAPI\Exception\ClassMetadataException;
use JSONAPI\Exception\NullException;

class MetadataFactory
{
    /**
     * @var string
     */
    private $path;
    /**
     * @var ArrayCache|Cache|null
     */
    private $cache;
    /**
     * @var AnnotationDriver|IDriver|null
     */
    private $driver;
    /**
     * @var bool
     */
    private $debug;
    /**
     * @var array
     */
    private $typeToClassMap = [];
    /**
     * @var array
     */
    private $metadata = [];

    /**
     * MetadataFactory constructor.
     * @param string       $pathToObjects
     * @param Cache|null   $cache
     * @param IDriver|null $driver
     * @param bool         $debug
     * @throws ClassMetadataException
     * @throws NullException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct(string $pathToObjects, Cache $cache = null, IDriver $driver = null, bool $debug = false)
    {
        if (!is_dir($pathToObjects)) throw new NullException("Path to object is not directory.");
        $this->path = $pathToObjects;
        $this->driver = $driver ?: new AnnotationDriver();
        $this->cache = $cache ?: new ArrayCache();
        $this->debug = $debug;
        // todo: make this call only once!
        $this->createMetadataCache();
    }

    /**
     * @param string $className
     * @return ClassMetadata
     * @throws ClassMetadataException
     * @throws NullException
     */
    public function getMetadataByClass(string $className): ClassMetadata
    {
        $className = ClassUtils::getRealClass($className);
        if ($this->cache->contains($className)) {
            return $this->cache->fetch($className);
        } elseif ($classMetadata = $this->driver->getClassMetadata($className)) {
            $this->cache->save($className, $classMetadata);
            return $classMetadata;
        } else {
            throw new NullException("Metadata for class {$className} does not exists.");
        }
    }

    /**
     * @param string $resourceType
     * @return ClassMetadata | null
     * @throws ClassMetadataException
     * @throws NullException
     */
    public function getMetadataClassByType(string $resourceType): ?ClassMetadata
    {
        return $this->getMetadataByClass($this->typeToClassMap[$resourceType]);
    }

    /**
     * @return array
     */
    public function getAllMetadata()
    {
        return $this->metadata;
    }

    /**
     * @return void
     * @throws ClassMetadataException
     */
    private function createMetadataCache()
    {
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->path));
        $it->rewind();
        while ($it->valid()) {
            /** @var $it \RecursiveDirectoryIterator */
            if (!$it->isDot()) {
                $file = $it->key();
                if (is_file($file)) {
                    require_once $file;
                }

            }
            $it->next();
        }


        foreach (get_declared_classes() as $className) {
            try {
                $classMetadata = $this->getMetadataByClass($className);
                $this->metadata[$className] = $classMetadata;
                $this->typeToClassMap[$classMetadata->getResource()->type] = $className;
            } catch (NullException $e) {
                // not existing class
            }
        }
    }
}

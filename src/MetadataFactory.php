<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 14:57
 */

namespace OpenAPI;

use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use OpenAPI\Driver\AnnotationDriver;
use OpenAPI\Exception\InvalidObjectException;
use OpenAPI\Exception\NullException;

class MetadataFactory
{
    private $cache;
    private $typeToClassMap = [];
    private $path;
    private $metadata = [];
    private $driver;

    /**
     * MetadataFactory constructor.
     * @param MetadataFactoryOptions $options
     * @throws Exception\ClassMetadataException
     * @throws InvalidObjectException
     * @throws NullException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(MetadataFactoryOptions $options)
    {
        $this->path = $options->getPath();
        $this->driver = $options->getDriver() ? $options->getDriver() : new AnnotationDriver();
        $this->cache = $options->getCache() ? $options->getCache() : new ArrayCache();

        $this->createMetadataCache();
    }

    /**
     * @return void
     * @throws Exception\ClassMetadataException
     * @throws InvalidObjectException
     * @throws NullException
     * @throws \ReflectionException
     */
    private function createMetadataCache()
    {
        if (!is_dir($this->path)) {
            throw new NullException("Path is not directory.");
        }
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
            $classMetadata = null;
            try {
                $classMetadata = $this->getMetadataByClass($className);
            } catch (NullException $e) {
                if ($classMetadata = $this->driver->getClassMetadata($className)) {
                    $this->cache->save($className, $classMetadata);
                }
            } finally {
                if ($classMetadata) {
                    $this->metadata[$className] = $classMetadata;
                    $this->typeToClassMap[$classMetadata->getResource()->type] = $className;
                }
            }
        }
    }

    /**
     * @param $className
     * @return ClassMetadata
     * @throws NullException
     */
    public function getMetadataByClass(string $className): ClassMetadata
    {
        if ($this->cache->contains($className)) {
            return $this->cache->fetch($className);
        } else {
            throw new NullException("Metadata for class {$className} not exists");
        }
    }

    /**
     * @param string $resourceType
     * @return ClassMetadata
     * @throws NullException
     */
    public function getMetadataClassByType(string $resourceType): ClassMetadata
    {
        return $this->getMetadataByClass($this->typeToClassMap[$resourceType]);
    }

    public function getAllMetadata()
    {
        return $this->metadata;
    }
}

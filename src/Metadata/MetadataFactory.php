<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 14:57
 */

namespace JSONAPI\Metadata;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Util\ClassUtils;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Exception\DriverException;
use JSONAPI\Exception\FactoryException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

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
     * @var AnnotationDriver|null
     */
    private $driver;
    /**
     * @var LoggerInterface
     */
    private $logger;
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
     *
     * @param string               $pathToObjects
     * @param Cache|null           $cache
     * @param LoggerInterface|null $logger
     * @throws AnnotationException
     * @throws DriverException
     * @throws FactoryException
     */
    public function __construct(string $pathToObjects, Cache $cache = null, LoggerInterface $logger = null)
    {
        if (!is_dir($pathToObjects)) {
            throw new FactoryException("Path to object is not directory.",
                FactoryException::PATH_IS_NOT_VALID);
        }
        $this->driver = new AnnotationDriver($logger);
        $this->path = $pathToObjects;
        $this->cache = $cache ?? new ArrayCache();
        $this->logger = $logger ?? new NullLogger();
        $this->load();
    }

    /**
     * @param string $className
     * @return ClassMetadata
     * @throws DriverException
     * @throws FactoryException
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
            throw new FactoryException("Metadata for class {$className} does not exists.",
                FactoryException::CLASS_IS_NOT_RESOURCE);
        }
    }

    /**
     * @param string $resourceType
     * @return ClassMetadata | null
     * @throws FactoryException
     * @throws DriverException
     */
    public function getMetadataClassByType(string $resourceType): ?ClassMetadata
    {
        return $this->getMetadataByClass($this->getClassByType($resourceType));
    }

    /**
     * @param string $resourceType
     * @return string
     */
    public function getClassByType(string $resourceType): string
    {
        return $this->typeToClassMap[$resourceType];
    }

    /**
     * @return ClassMetadata[]
     */
    public function getAllMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @throws DriverException
     */
    private function createMetadataCache(): void
    {
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->path));
        $it->rewind();
        while ($it->valid()) {
            /** @var $it \RecursiveDirectoryIterator */
            if (!$it->isDot()) {
                $file = $it->key();
                if (is_file($file) && (isset(pathinfo($file)["extension"]) && pathinfo($file)["extension"] === "php")) {
                    require_once $file;
                }

            }
            $it->next();
        }

        foreach (get_declared_classes() as $className) {
            try {
                $this->loadMetadata($className);
            } catch (FactoryException $e) {
                // class is not resource
            }
        }
        $this->cache->save(self::class, array_keys($this->metadata));
    }

    /**
     * @throws DriverException
     * @throws FactoryException
     */
    private function load(): void
    {
        if ($this->cache->contains(self::class)) {
            foreach ($this->cache->fetch(self::class) as $className) {
                $this->loadMetadata($className);
            }
        } else {
            $this->createMetadataCache();
        }
    }

    /**
     * @param string $className
     * @throws FactoryException
     * @throws DriverException
     */
    private function loadMetadata(string $className)
    {
        $classMetadata = $this->getMetadataByClass($className);
        $this->metadata[$className] = $classMetadata;
        $this->typeToClassMap[$classMetadata->getResource()->type] = $className;
    }
}

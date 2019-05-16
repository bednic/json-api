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
use JSONAPI\Exception\Driver\AnnotationMisplace;
use JSONAPI\Exception\Driver\ClassNotExist;
use JSONAPI\Exception\Driver\ClassNotResource;
use JSONAPI\Exception\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class MetadataFactory
 *
 * @package JSONAPI\Metadata
 */
class MetadataFactory
{
    /**
     * @var string
     */
    private $path;
    /**
     * @var Cache|null
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
     * @throws AnnotationMisplace
     * @throws ClassNotExist
     * @throws ClassNotResource
     * @throws InvalidArgumentException
     */
    public function __construct(string $pathToObjects, Cache $cache = null, LoggerInterface $logger = null)
    {
        if (!is_dir($pathToObjects)) {
            throw new InvalidArgumentException("Path to object is not directory.");
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
     * @throws ClassNotResource
     * @throws AnnotationMisplace
     * @throws ClassNotExist
     */
    public function getMetadataByClass(string $className): ClassMetadata
    {
        $className = ClassUtils::getRealClass($className);
        if ($this->cache->contains($className)) {
            return $this->cache->fetch($className);
        } else {
            $classMetadata = $this->driver->getClassMetadata($className);
            $this->cache->save($className, $classMetadata);
            return $classMetadata;
        }
    }

    /**
     * @param string $resourceType
     * @return ClassMetadata
     * @throws AnnotationMisplace
     * @throws ClassNotExist
     * @throws ClassNotResource
     */
    public function getMetadataClassByType(string $resourceType): ClassMetadata
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
     * @throws AnnotationMisplace
     */
    private function createMetadataCache(): void
    {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->path));
        $it->rewind();
        while ($it->valid()) {
            /** @var $it RecursiveDirectoryIterator */
            if (!$it->isDot()) {
                $file = $it->key();
                if (is_file($file)
                    && (
                        isset(pathinfo($file)["extension"])
                        && pathinfo($file)["extension"] === "php"
                    )
                ) {
                    require_once $file;
                }
            }
            $it->next();
        }

        foreach (get_declared_classes() as $className) {
            try {
                $this->loadMetadata($className);
            } catch (ClassNotResource $exception) {
                // ignored
            } catch (ClassNotExist $exception) {
                // ignored
            }
        }
        $this->cache->save(self::class, array_keys($this->metadata));
    }

    /**
     * @throws AnnotationMisplace
     * @throws ClassNotExist
     * @throws ClassNotResource
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
     * @throws AnnotationMisplace
     * @throws ClassNotExist
     * @throws ClassNotResource
     */
    private function loadMetadata(string $className)
    {
        $classMetadata = $this->getMetadataByClass($className);
        $this->metadata[$className] = $classMetadata;
        $this->typeToClassMap[$classMetadata->getResource()->type] = $className;
    }
}

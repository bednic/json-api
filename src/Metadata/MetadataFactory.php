<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 14:57
 */

namespace JSONAPI\Metadata;

use JSONAPI\DoctrineProxyTrait;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Driver\DriverInterface;
use JSONAPI\Exception\Driver\ClassNotExist;
use JSONAPI\Exception\Driver\ClassNotResource;
use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\Exception\InvalidArgumentException;
use JSONAPI\Exception\Metadata\ResourceTypeNotFound;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException as CacheException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class MetadataFactory
 *
 * @package JSONAPI\Metadata
 */
class MetadataFactory
{
    use DoctrineProxyTrait;

    /**
     * @var string
     */
    private string $path;
    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;
    /**
     * @var DriverInterface
     */
    private DriverInterface $driver;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var array
     */
    private array $typeToClassMap = [];
    /**
     * @var array
     */
    private array $metadata = [];

    /**
     * MetadataFactory constructor.
     *
     * @param string               $pathToObjects
     * @param DriverInterface      $driver
     * @param CacheInterface       $cache
     * @param LoggerInterface|null $logger
     *
     * @throws DriverException
     * @throws InvalidArgumentException
     */
    public function __construct(
        string $pathToObjects,
        CacheInterface $cache,
        DriverInterface $driver = null,
        LoggerInterface $logger = null
    ) {
        if (!is_dir($pathToObjects)) {
            throw new InvalidArgumentException("PathInterface to object is not directory.");
        }

        $this->path = $pathToObjects;
        $this->cache = $cache;
        $this->driver = $driver ?? new AnnotationDriver($logger);
        $this->logger = $logger ?? new NullLogger();
        $this->load();
    }

    /**
     * @param string $className
     *
     * @return ClassMetadata
     * @throws DriverException
     * @throws InvalidArgumentException
     */
    public function getMetadataByClass(string $className): ClassMetadata
    {
        $className = self::clearDoctrineProxyPrefix($className);
        try {
            if ($this->cache->has(slashToDot($className))) {
                return $this->cache->get(slashToDot($className));
            } else {
                $classMetadata = $this->driver->getClassMetadata($className);
                $this->cache->set(slashToDot($className), $classMetadata);
                return $classMetadata;
            }
        } catch (CacheException $e) {
            throw new InvalidArgumentException($e->getMessage(), 51, $e);
        }
    }

    /**
     * @param string $resourceType
     *
     * @return ClassMetadata
     * @throws DriverException
     * @throws InvalidArgumentException
     * @throws ResourceTypeNotFound
     */
    public function getMetadataClassByType(string $resourceType): ClassMetadata
    {
        if ($className = $this->typeToClassMap[$resourceType]) {
            return $this->getMetadataByClass($className);
        } else {
            throw new ResourceTypeNotFound($resourceType);
        }
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
     * @throws InvalidArgumentException
     */
    private function load(): void
    {
        try {
            if ($this->cache->has(slashToDot(self::class))) {
                foreach ($this->cache->get(slashToDot(self::class)) as $className) {
                    $this->loadMetadata($className);
                }
            } else {
                $this->createMetadataCache();
            }
        } catch (CacheException $e) {
            throw new InvalidArgumentException($e->getMessage(), 51, $e);
        }
    }

    /**
     * @param string $className
     *
     * @throws DriverException
     * @throws InvalidArgumentException
     */
    private function loadMetadata(string $className)
    {
        $classMetadata = $this->getMetadataByClass($className);
        $this->metadata[$className] = $classMetadata;
        $this->typeToClassMap[$classMetadata->getResource()->type] = $className;
    }


    /**
     * @throws DriverException
     * @throws InvalidArgumentException
     */
    private function createMetadataCache(): void
    {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->path));
        $it->rewind();
        while ($it->valid()) {
            /** @var $it RecursiveDirectoryIterator */
            if (!$it->isDot()) {
                $file = $it->key();
                if (
                    is_file($file)
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
        try {
            $this->cache->set(slashToDot(self::class), array_keys($this->metadata));
        } catch (CacheException $exception) {
            throw new InvalidArgumentException($exception->getMessage(), 51, $exception);
        }
    }
}

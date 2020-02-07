<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 14:57
 */

namespace JSONAPI\Metadata;

use JSONAPI\DoctrineProxyTrait;
use JSONAPI\Driver\Driver;
use JSONAPI\Exception\Driver\ClassNotExist;
use JSONAPI\Exception\Driver\ClassNotResource;
use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\Exception\InvalidArgumentException;
use JSONAPI\Exception\Metadata\MetadataException;
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
     * @var Driver
     */
    private Driver $driver;
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
     * @param CacheInterface       $cache
     * @param Driver               $driver
     * @param LoggerInterface|null $logger
     *
     * @throws CacheException
     * @throws DriverException
     * @throws InvalidArgumentException
     * @throws MetadataException
     */
    private function __construct(
        string $pathToObjects,
        CacheInterface $cache,
        Driver $driver,
        LoggerInterface $logger = null
    ) {
        if (!is_dir($pathToObjects)) {
            throw new InvalidArgumentException("Argument pathToObjects is not directory.");
        }
        $this->path = $pathToObjects;
        $this->cache = $cache;
        $this->driver = $driver;
        $this->logger = $logger ?? new NullLogger();
        $this->load();
    }

    /**
     * @param string $className
     *
     * @return ClassMetadata
     * @throws CacheException
     * @throws DriverException
     * @throws MetadataException
     */
    private function getMetadataByClass(string $className): ClassMetadata
    {
        $className = self::clearDoctrineProxyPrefix($className);
        if ($this->cache->has(slashToDot($className))) {
            return $this->cache->get(slashToDot($className));
        } else {
            $classMetadata = $this->driver->getClassMetadata($className);
            $this->cache->set(slashToDot($className), $classMetadata);
            return $classMetadata;
        }
    }

    /**
     * @return ClassMetadata[]
     */
    private function getAllMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @throws CacheException
     * @throws DriverException
     * @throws MetadataException
     */
    private function load(): void
    {
        if ($this->cache->has(slashToDot(self::class))) {
            foreach ($this->cache->get(slashToDot(self::class)) as $className) {
                $this->loadMetadata($className);
            }
        } else {
            $this->createMetadataCache();
        }
    }

    /**
     * @param string $className
     *
     * @throws CacheException
     * @throws DriverException
     * @throws MetadataException
     */
    private function loadMetadata(string $className)
    {
        $classMetadata = $this->getMetadataByClass($className);
        $this->metadata[$className] = $classMetadata;
        $this->typeToClassMap[$classMetadata->getType()] = $className;
    }

    /**
     * @throws CacheException
     * @throws DriverException
     * @throws MetadataException
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
        $this->cache->set(slashToDot(self::class), array_keys($this->metadata));
    }

    /**
     * @param string               $pathToObjects
     * @param CacheInterface       $cache
     * @param Driver               $driver
     * @param LoggerInterface|null $logger
     *
     * @return MetadataRepository
     * @throws CacheException
     * @throws DriverException
     * @throws InvalidArgumentException
     * @throws MetadataException
     */
    public static function create(
        string $pathToObjects,
        CacheInterface $cache,
        Driver $driver,
        LoggerInterface $logger = null
    ): MetadataRepository {
        $self = new static($pathToObjects, $cache, $driver, $logger);
        $repository = new MetadataRepository();
        foreach ($self->getAllMetadata() as $metadata) {
            $repository->add($metadata);
        }
        return $repository;
    }
}

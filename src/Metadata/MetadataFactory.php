<?php

declare(strict_types=1);

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
     * @var string[]
     */
    private array $paths = [];
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
     * @param array                $paths
     * @param CacheInterface       $cache
     * @param Driver               $driver
     * @param LoggerInterface|null $logger
     *
     * @throws DriverException
     * @throws InvalidArgumentException
     * @throws MetadataException
     */
    private function __construct(
        array $paths,
        CacheInterface $cache,
        Driver $driver,
        LoggerInterface $logger = null
    ) {
        $this->paths = $paths;
        $this->cache = $cache;
        $this->driver = $driver;
        $this->logger = $logger ?? new NullLogger();
        $this->load();
    }

    /**
     * @param string $className
     *
     * @return ClassMetadata
     * @throws DriverException
     * @throws MetadataException
     */
    private function getMetadataByClass(string $className): ClassMetadata
    {
        try {
            $className = self::clearDoctrineProxyPrefix($className);
            if ($this->cache->has(slashToDot($className))) {
                return $this->cache->get(slashToDot($className));
            } else {
                $classMetadata = $this->driver->getClassMetadata($className);
                $this->cache->set(slashToDot($className), $classMetadata);
                return $classMetadata;
            }
        } catch (CacheException $exception) {
            throw new ClassNotResource($className);
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
     * @throws DriverException
     * @throws InvalidArgumentException
     * @throws MetadataException
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
        } catch (CacheException $ignored) {
            // NO SONAR
        }
    }

    /**
     * @param string $className
     *
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
     * @throws DriverException
     * @throws InvalidArgumentException
     * @throws MetadataException
     */
    private function createMetadataCache(): void
    {
        foreach ($this->paths as $path) {
            if (!is_dir($path)) {
                throw new InvalidArgumentException("Path '$path' is not directory.");
            }
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
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
                } catch (ClassNotResource $ignored) {
                    // NO-SONAR
                } catch (ClassNotExist $ignored) {
                    // NO-SONAR
                }
            }
        }
        try {
            $this->cache->set(slashToDot(self::class), array_keys($this->metadata));
        } catch (CacheException $ignored) {
            // NO SONAR
        }
    }

    /**
     * @param array                $paths
     * @param CacheInterface       $cache
     * @param Driver               $driver
     * @param LoggerInterface|null $logger
     *
     * @return MetadataRepository
     * @throws DriverException
     * @throws InvalidArgumentException
     * @throws MetadataException
     */
    public static function create(
        array $paths,
        CacheInterface $cache,
        Driver $driver,
        LoggerInterface $logger = null
    ): MetadataRepository {
        $self = new static($paths, $cache, $driver, $logger);
        $repository = new MetadataRepository();
        foreach ($self->getAllMetadata() as $metadata) {
            $repository->add($metadata);
        }
        return $repository;
    }
}

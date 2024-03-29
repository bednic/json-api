<?php

declare(strict_types=1);

namespace JSONAPI\Metadata;

use Composer\Autoload\ClassMapGenerator;
use JSONAPI\Driver\Driver;
use JSONAPI\Exception\Driver\ClassNotExist;
use JSONAPI\Exception\Driver\ClassNotResource;
use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\Exception\InvalidArgumentException;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\Helper\DoctrineProxyTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException as CacheException;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Class MetadataFactory
 *
 * @package JSONAPI\Metadata
 * @todo    This deserve refactor
 */
class MetadataFactory
{
    use DoctrineProxyTrait;

    /**
     * @var string[]
     */
    private array $paths;
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
    private readonly LoggerInterface $logger;
    /**
     * @var array<ClassMetadata>
     */
    private array $metadata = [];
    /**
     * @var AsciiSlugger
     */
    private AsciiSlugger $slugger;

    /**
     * MetadataFactory constructor.
     *
     * @param string[]             $paths
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
        $this->slugger = new AsciiSlugger();
        $this->load();
    }

    /**
     * @throws DriverException
     * @throws InvalidArgumentException
     * @throws MetadataException
     */
    private function load(): void
    {
        $this->logger->debug("Start loading metadata.");
        $key = $this->slugger->slug(get_class($this))->toString();
        try {
            if ($this->cache->has($key)) {
                foreach ($this->cache->get($key) as $className) {
                    $this->loadMetadata($className);
                }
            } else {
                $this->createMetadataCache();
            }
        } catch (CacheException $ignored) {
            // NO SONAR
        }
        $this->logger->debug("Metadata loaded.");
    }

    /**
     * @param string $className
     * @phpstan-param class-string $className
     *
     * @throws DriverException
     * @throws MetadataException
     */
    private function loadMetadata(string $className): void
    {
        $classMetadata = $this->getMetadataByClass($className);
        $this->metadata[$className] = $classMetadata;
    }

    /**
     * @param string $className
     * @phpstan-param class-string $className
     *
     * @return ClassMetadata
     * @throws DriverException
     * @throws MetadataException
     */
    private function getMetadataByClass(string $className): ClassMetadata
    {
        try {
            $key = $this->slugger->slug($className)->toString();
            $className = self::clearDoctrineProxyPrefix($className);
            if ($this->cache->has($key)) {
                return $this->cache->get($key);
            } else {
                $classMetadata = $this->driver->getClassMetadata($className);
                $this->cache->set($key, $classMetadata);
                return $classMetadata;
            }
        } catch (CacheException $exception) {
            throw new ClassNotResource($className);
        }
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
            $map = ClassMapGenerator::createMap($path);
            foreach ($map as $className => $file) {
                try {
                    $this->loadMetadata($className);
                } catch (ClassNotResource | ClassNotExist) {
                    // NO-SONAR
                }
            }
        }
        try {
            $this->cache->set($this->slugger->slug(get_class($this))->toString(), array_keys($this->metadata));
        } catch (CacheException $e) {
            throw new MetadataException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param array<string>                $paths
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
        $self = new self($paths, $cache, $driver, $logger);
        $repository = new MetadataRepository();
        foreach ($self->getAllMetadata() as $metadata) {
            $repository->add($metadata);
        }
        return $repository;
    }

    /**
     * @return ClassMetadata[]
     */
    private function getAllMetadata(): array
    {
        return $this->metadata;
    }
}

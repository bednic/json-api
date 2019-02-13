<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 05.02.2019
 * Time: 12:42
 */

namespace OpenAPI;


use Doctrine\Common\Cache\Cache;
use OpenAPI\Driver\IDriver;
use OpenAPI\Exception\InvalidOptionArgumentException;

class MetadataFactoryOptions
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
     * @var IDriver|null
     */
    private $driver;
    /**
     * @var bool
     */
    private $debug = false;

    /**
     * MetadataFactoryOptions constructor.
     * @param string $pathToObjects
     * @param Cache|null $cache
     * @param IDriver $driver
     * @param bool $debug
     * @throws InvalidOptionArgumentException
     */
    public function __construct(string $pathToObjects, Cache $cache = null, IDriver $driver = null, bool $debug = false)
    {
        $this->setPath($pathToObjects);
        if ($cache) {
            $this->setCache($cache);
        }
        if ($driver) {
            $this->setDriver($driver);
        }
        $this->setDebug($debug);

    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     * @throws InvalidOptionArgumentException
     */
    public function setPath(string $path): void
    {
        if (!is_dir($path)) {
            throw new InvalidOptionArgumentException("Bash MUST be directory.");
        }
        $this->path = $path;
    }

    /**
     * @return Cache
     */
    public function getCache(): ?Cache
    {
        return $this->cache;
    }

    /**
     * @param Cache $cache
     */
    public function setCache(Cache $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @return IDriver
     */
    public function getDriver(): ?IDriver
    {
        return $this->driver;
    }

    /**
     * @param IDriver $driver
     */
    public function setDriver(IDriver $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }
}

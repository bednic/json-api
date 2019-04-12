<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 05.02.2019
 * Time: 13:14
 */


namespace JSONAPI\Document;


/**
 * Class Error
 * @package JSONAPI\Document
 */
class Error implements \JsonSerializable
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var array
     */
    private $links = [];
    /**
     * @var int
     */
    private $status = 500;
    /**
     * @var string
     */
    private $code;
    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $detail;
    /**
     * @var array
     */
    private $source = [];
    /**
     * @var array
     */
    private $meta;

    /**
     * Error constructor.
     * @param \Throwable $exception
     */
    public function __construct(\Throwable $exception)
    {
        $this->setTitle(get_class($exception));
        $this->setCode($exception->getCode());
        $this->setStatus(500);
        $this->setDetail($exception->getMessage());
        $this->setSource([
            "Line" => "{$exception->getFile()} ({$exception->getLine()})",
            "Pointer" => "/data"
        ]);
        $this->setMeta($exception->getTraceAsString());
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'links' => $this->getLinks(),
            'status' => $this->getStatus(),
            'code' => $this->getCode(),
            'title' => $this->getTitle(),
            'detail' => $this->getDetail(),
            'source' => $this->getSource(),
            'meta' => $this->getMeta()
        ];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param array $links
     */
    public function setLinks(array $links): void
    {
        $this->links = $links;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @param mixed $detail
     */
    public function setDetail($detail): void
    {
        $this->detail = $detail;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source): void
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param mixed $meta
     */
    public function setMeta($meta): void
    {
        $this->meta = $meta;
    }
}

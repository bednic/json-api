<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 05.02.2019
 * Time: 13:14
 */


namespace JSONAPI\Document;


use Throwable;

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
     * @param Throwable $exception
     */
    public function __construct(Throwable $exception)
    {
        $this->setTitle(get_class($exception));
        $this->setCode($exception->getCode());
        $this->setStatus(500);
        $this->setDetail($exception->getMessage());
        $this->setSource([
            "Line" => "{$exception->getFile()} ({$exception->getLine()})",
            "Pointer" => "/data"
        ]);
        $this->setMeta($exception->getTrace());
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @param array $links
     */
    public function setLinks(array $links): void
    {
        $this->links = $links;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @param string $detail
     */
    public function setDetail(string $detail): void
    {
        $this->detail = $detail;
    }

    /**
     * @param array $source
     */
    public function setSource(array $source): void
    {
        $this->source = $source;
    }

    /**
     * @param array $meta
     */
    public function setMeta(array $meta): void
    {
        $this->meta = $meta;
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
            'id' => $this->id,
            'links' => $this->links,
            'status' => $this->status,
            'code' => $this->code,
            'title' => $this->title,
            'detail' => $this->detail,
            'source' => $this->source,
            'meta' => $this->meta
        ];
    }
}

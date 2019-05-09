<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 05.02.2019
 * Time: 13:14
 */


namespace JSONAPI\Document;


use Doctrine\Common\Collections\ArrayCollection;
use JSONAPI\Exception\JsonApiException;
use JSONAPI\Utils\LinksImpl;
use JSONAPI\Utils\MetaImpl;

/**
 * Class Error
 *
 * @package JSONAPI\Document
 */
class Error implements \JsonSerializable, HasLinks, HasMeta
{
    use LinksImpl;
    use MetaImpl;

    /**
     * @var string
     */
    private $id;
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
     * @param JsonApiException $exception
     * @return Error
     */
    public static function fromException(JsonApiException $exception)
    {
        $self = new static();
        $self->setTitle(get_class($exception));
        $self->setCode($exception->getCode());
        $self->setStatus($exception->getStatus());
        $self->setDetail($exception->getMessage());
        $self->setSource([
            "Line" => "{$exception->getFile()} ({$exception->getLine()})"
        ]);
        $self->setMeta(new Meta(["trace", explode("\n", $exception->getTraceAsString())]));
        return $self;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
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
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'links' => $this->links,
            'meta' => $this->meta,
            'status' => $this->status,
            'code' => $this->code,
            'title' => $this->title,
            'detail' => $this->detail,
            'source' => $this->source
        ];
    }
}

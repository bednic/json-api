<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 05.02.2019
 * Time: 13:19
 */

namespace JSONAPI\Query;

use JSONAPI\Exception\Document\BadRequest;
use JSONAPI\Exception\InvalidArgumentException;
use Slim\Psr7\Factory\UriFactory;

/**
 * Class Query
 *
 * @package JSONAPI
 */
class Query
{

    /**
     * @var Filter
     */
    private $filterParser;

    /**
     * @var Pagination
     */
    private $paginationParser;

    /**
     * @var array|null
     */
    private $includes = null;
    /**
     * @var array|null
     */
    private $fields = null;

    /**
     * @var array|null
     */
    private $sort = null;

    /**
     * @var Path
     */
    private $path;

    /**
     * Query constructor.
     *
     * @param Filter|null     $filterParser
     * @param Pagination|null $paginationParser
     */
    public function __construct(Filter $filterParser = null, Pagination $paginationParser = null)
    {
        $this->filterParser = $filterParser ?? new VoidFilterParser();
        $this->paginationParser = $paginationParser ?? new LimitOffsetPaginationParser();

        if (isset($_GET['include'])) {
            $this->parseIncludes($_GET['include']);
        }

        if (isset($_GET['fields'])) {
            $this->parseFields($_GET['fields']);
        }
        if (isset($_GET['sort'])) {
            $this->parseSort($_GET['sort']);
        }
        if (isset($_GET['page'])) {
            $this->parsePage($_GET['page']);
        }

        if (isset($_GET['filter'])) {
            $this->parseFilter($_GET['filter']);
        }
    }

    /**
     * @return array|null
     */
    public function getIncludes(): ?array
    {
        return $this->includes;
    }

    /**
     * @param $resourceType
     *
     * @return array
     */
    public function getFieldsFor($resourceType): array
    {
        return isset($this->fields[$resourceType]) ? $this->fields[$resourceType] : [];
    }

    /**
     * @return array|null
     */
    public function getSort(): ?array
    {
        return $this->sort;
    }

    /**
     * @return Pagination
     */
    public function getPagination(): Pagination
    {
        return $this->paginationParser;
    }

    /**
     * @return Filter
     */
    public function getFilter(): Filter
    {
        return $this->filterParser;
    }

    /**
     * @param string $query
     */
    private function parseIncludes(string $query)
    {
        $this->includes = [];
        $t = explode(",", $query);
        $dot2tree = function (&$arr, $path, $value, $separator = '.') {
            $keys = explode($separator, $path);
            foreach ($keys as $key) {
                $arr = &$arr[$key];
            }

            $arr = $value;
        };
        foreach ($t as $i) {
            $dot2tree($this->includes, $i, []);
        }
    }

    /**
     * @param array $query
     */
    private function parseFields(array $query)
    {
        $this->fields = [];
        foreach ($query as $type => $fields) {
            $this->fields[$type] = array_map(function ($item) {
                return trim($item);
            }, explode(',', $fields));
        }
    }

    /**
     * @param string $query
     */
    private function parseSort(string $query)
    {
        $this->sort = [];
        preg_match_all('/((?P<sort>-?)(?P<field>[a-zA-Z0-9]+))/', $query, $matches);
        foreach ($matches['field'] as $i => $field) {
            $this->sort[$field] = $matches['sort'][$i] ? "DESC" : "ASC";
        }
    }

    /**
     * @param array $pagination
     */
    private function parsePage(array $pagination)
    {
        $this->paginationParser->parse($pagination);
    }

    /**
     * @param $filter
     */
    private function parseFilter($filter)
    {
        $this->filterParser->parse($filter);
    }

    /**
     * @return Path
     * @throws BadRequest
     * @throws InvalidArgumentException
     */
    public function getPath(): Path
    {
        if (!$this->path) {
            $this->parsePath();
        }
        return $this->path;
    }

    /**
     * @return void
     * @throws BadRequest
     * @throws InvalidArgumentException
     */
    private function parsePath(): void
    {
        $uriFactory = new UriFactory();
        $baseUrl = $uriFactory->createUri(LinkProvider::getAPIUrl());
        $uri = $uriFactory->createFromGlobals($_SERVER);
        $url = str_replace($baseUrl->getPath(), '/', $uri->getPath());
        $pattern = '/^\/(?P<resource>[a-zA-Z0-9-_]+)(\/(?P<id>[a-zA-Z0-9-_]+))?'
            . '((\/relationships\/(?P<relationship>[a-zA-Z0-9-_]+))|(\/(?P<related>[a-zA-Z0-9-_]+)))?$/';
        if (preg_match($pattern, $url, $matches)) {
            $this->path = new Path(
                isset($matches['resource']) ? $matches['resource'] : '',
                isset($matches['id']) ? $matches['id'] : null,
                isset($matches['relationship']) ? $matches['relationship'] : null,
                isset($matches['related']) ? $matches['related'] : null,
                $uri->getQuery()
            );
        } else {
            throw new BadRequest("Invalid URL");
        }
    }
}

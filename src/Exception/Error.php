<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 12.03.2019
 * Time: 13:17
 */

namespace JSONAPI\Exception;

/**
 * Class Error
 * @package JSONAPI\Exception
 * Todo: WIP
 */
class Error extends \Exception
{

    protected $id;
    protected $links;
    protected $status;
    protected $code;
    protected $title;
    protected $detail;
    protected $source;
    protected $meta;

    /**
     * Error constructor.
     */
    public function __construct()
    {

    }


}

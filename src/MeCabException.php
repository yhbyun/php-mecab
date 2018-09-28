<?php
/**
 * Created by PhpStorm.
 * User: aoi
 * Date: 2018/09/28
 * Time: 9:48
 */

namespace Youaoi\MeCab;

/**
 * Class MeCabException
 * @package Youaoi\MeCab
 */
class MeCabException extends \Exception
{
    /**
     * @var MeCab
     */
    public $instance;

    public function __construct(MeCab $instance, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->instance = $instance;
        parent::__construct($message, $code, $previous);
    }
}

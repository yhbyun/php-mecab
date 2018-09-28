<?php
/**
 * Created by PhpStorm.
 * User: aoi
 * Date: 2018/09/28
 * Time: 9:47
 */

namespace Youaoi\MeCab;

/**
 * Class MeCabWord
 * @package Youaoi\MeCab
 * @property-read string|null $str
 * @property-read string|null $text テキスト
 * @property-read string|null $speech
 * @property-read string|null $speechInfo
 * @property-read string|null $conjugate
 * @property-read string|null $conjugateType
 * @property-read string|null $original
 * @property-read string|null $reading ヨミガナ
 * @property-read string|null $pronunciation
 * @property-read string|null $sortText ヨミガナ ?? テキスト
 */
class MeCabWord
{
    protected const APPENDS = ['sortText'];

    protected $str;
    protected $text;
    protected $speech;
    protected $speechInfo;
    protected $conjugate;
    protected $conjugateType;
    protected $original;
    protected $reading;
    protected $pronunciation;

    /**
     * @param $text
     */
    function __construct($text)
    {
        $this->str = $text;

        $res = preg_split('/\t/', $text);
        if (count($res) != 2) return;

        $this->text = $res[0];
        $info = explode(',', $res[1]);

        $this->speechInfo = array_fill(0, 3, null);
        foreach ($info as $k => $t) {
            if ($t == '*') {
                continue;
            }
            if ($k == 0) {
                $this->speech = $t;
            } else if ($k <= 3) {
                $this->speechInfo[$k - 1] = $t;
            } else if ($k == 4) {
                $this->conjugate = $t;
            } else if ($k == 5) {
                $this->conjugateType = $t;
            } else if ($k == 6) {
                $this->original = $t;
            } else if ($k == 7) {
                $this->reading = $t;
            } else if ($k == 8) {
                $this->pronunciation = $t;
            }
        }
    }

    protected function sortText()
    {
        return $this->reading ?? $this->text;
    }

    public function __get($name)
    {
        if (in_array($name, self::APPENDS)) {
            return $this->$name();
        }
        return $this->$name;
    }

    public function __isset($name)
    {
        return isset($this->$name) || in_array($name, self::APPENDS);
    }
}

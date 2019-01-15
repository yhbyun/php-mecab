<?php

namespace Youaoi\MeCab;

/**
 * Class MeCab
 *  php mecab.soを必要としない。実行ファイル起動型のMeCabユーティリティ
 * @version 0.9.0
 * @package Youaoi\MeCab
 * @property-read string|null $tmpFile
 * @property-read string|null $command
 * @property-read string|null $dictionary
 * @property-read string|null $dictionaryDir
 */
class MeCab
{
    /** @var string */
    protected $tmpFile = '';
    /** @var string */
    protected $command = '';
    /** @var string|null */
    protected $dictionary;
    /** @var string|null */
    protected $dictionaryDir;

    /** @var string */
    protected static $defaultCommand = "mecab";
    /** @var string|null */
    protected static $defaultDictionaryDir = null;
    /** @var string|null */
    protected static $defaultDictionary = null;
    /** @var string */
    protected static $wordClass = MeCabWord::class;

// BE-DELETED: getAutoDictionaryDir が廃止になったので廃止
//    /** @var string|null */
//    protected static $autoDictionaryDir = null;

    function __construct()
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'mecab-');
        $this->command = static::$defaultCommand;
        $this->dictionary = static::$defaultDictionary;
        $this->dictionaryDir = static::$defaultDictionaryDir;
    }

    /**
     * @param array $options
     * @return bool
     */
    public static function setDefaults(array $options = [])
    {
        if (isset($options['command']))
            static::$defaultCommand = $options['command'];

        if (isset($options['dictionaryDir']))
            static::$defaultDictionaryDir = $options['dictionaryDir'];

        if (isset($options['dictionary']))
            static::$defaultDictionary = $options['dictionary'];

        if (isset($options['wordClass']))
            static::$wordClass = $options['wordClass'];

        return true;
    }

    //<editor-fold desc="static getter/setter">

    /**
     * @return string
     */
    public static function getDefaultCommand()
    {
        return self::$defaultCommand;
    }

    /**
     * @param string $defaultCommand
     */
    public static function setDefaultCommand(string $defaultCommand)
    {
        self::$defaultCommand = $defaultCommand;
    }

    /**
     * @return null|string
     */
    public static function getDefaultDictionaryDir()
    {
        return self::$defaultDictionaryDir;
    }

    /**
     * @param null|string $defaultDictionaryDir
     */
    public static function setDefaultDictionaryDir(string $defaultDictionaryDir = null)
    {
        self::$defaultDictionaryDir = $defaultDictionaryDir;
    }


    /**
     * @return null|string
     */
    public static function getDefaultDictionary()
    {
        return self::$defaultDictionary;
    }

    /**
     * @param null|string $defaultDictionary
     */
    public static function setDefaultDictionary(string $defaultDictionary = null)
    {
        self::$defaultDictionary = $defaultDictionary;
    }

    //</editor-fold>

    //<editor-fold desc="instance setter">
    /**
     * @param string $command
     */
    public function setCommand(string $command)
    {
        $this->command = $command;
    }

    /**
     * @param string|null $dictionaryDir
     */
    public function setDictionaryDir(string $dictionaryDir = null)
    {
        $this->dictionaryDir = $dictionaryDir;
    }

    /**
     * @param string|null $dictionary
     */
    public function setDictionary(string $dictionary = null)
    {
        $this->dictionary = $dictionary;
    }

    //</editor-fold>

// BE-DELETED: --userdic の設定にディレクトリが不要だったので廃止
//    /**
//     * 固有辞書が設定されていれば辞書のパスを返却する。
//     * @return null|string
//     */
//    public function getDictionaryFilePath()
//    {
//        if (!$this->dictionary) return null;
//
//        // フルパス
//        if (strpos($this->dictionary, '/') === 0) return $this->dictionary;
//        if (strpos($this->dictionary, ':\\') === 1) return $this->dictionary;
//
//        $dir = $this->dictionaryDir ?? static::getAutoDictionaryDir();
//        $dir = rtrim($dir, '/\\') . '/';
//        return $dir . $this->dictionary;
//    }

// BE-DELETED: getDictionaryFilePath が廃止になったので廃止
//    /**
//     * mecabに設定済みのDirectoryを読み込む。
//     *  execで mecab-config が利用可能である必要がある。
//     * @return null|string
//     */
//    public static function getAutoDictionaryDir()
//    {
//        if (! self::$autoDictionaryDir) {
//            self::$autoDictionaryDir = self::exec('echo `mecab-config --dicdir`');
//        }
//        return self::$autoDictionaryDir;
//    }

    /**
     * 与えられたテキストを解析しワード配列を返却する。
     * @param string $text
     * @return MeCabWord[]
     * @throws MeCabException
     */
    public function analysis($text)
    {
        if (!file_put_contents($this->tmpFile, $text . PHP_EOL)) {
            throw new MeCabException($this, sprintf('Error write tmp file in %s', $this->tmpFile));
        }
        try {
            $command = [];
            $command[] = $this->command;
            $command[] = $this->tmpFile;
            if ($this->dictionary) {
                $command[] = sprintf('--userdic="%s"', $this->dictionary);
            } elseif ($this->dictionaryDir) {
                $command[] = sprintf('--dicdir="%s"', $this->dictionaryDir);
            }

            $this->exec(implode(' ', $command), $res);
            if ($res && (count($res) > 0)) {
                /** @var MeCabWord[] $words */
                $words = array();
                foreach ($res as $k => $r) {
                    if ($r == 'EOS' && count($res) >= ($k + 1)) {
                        break;
                    }
                    $words[] = new static::$wordClass($r);
                }
                return $words;
            } else {
                throw new MeCabException(sprintf('Error text analysis.'));
            }
        } finally {
            @unlink($this->tmpFile);
        }
    }

    /**
     * @param string $text
     * @return MeCabWord[]|null
     * @throws MeCabException
     */
    public static function parse($text)
    {
        return (new MeCab())->analysis($text);
    }

    /**
     * ヨミガナを取得する
     *  > あの主題1はtitle。 → アノシュダイハ。
     * @param string $text
     * @return string
     * @throws MeCabException
     */
    public static function toReading($text)
    {
        return implode('', array_column((new MeCab())->analysis($text), 'reading'));
    }

    /**
     * Sort用の文字列を返却する。
     *  > あの主題1はtitle。 → アノシュダイ1ハtitle。
     * @param string $text
     * @return string
     * @throws MeCabException
     */
    public static function toSortText($text)
    {
        return implode('', array_column((new MeCab())->analysis($text), 'sortText'));
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __isset($name)
    {
        return isset($this->$name);
    }

    /**
     * @param $command
     * @param $res
     * @return string
     */
    protected function exec($command, &$res)
    {
        return exec($command, $res);
    }
}




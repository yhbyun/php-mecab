<?php

namespace meCab;

use Throwable;

/**
 * Class meCab
 * @package meCab
 * @property-read string|null $tmp_file
 * @property-read string|null $command
 * @property-read string|null $dictionary
 * @property-read string|null $dictionary_dir
 */
class meCab
{
    /** @var string */
    private $tmp_file = '';
    /** @var string */
    private $command = '';
    /** @var string|null */
    private $dictionary;
    /** @var string|null */
    private $dictionary_dir;

    /** @var string */
    static private $default_command = "mecab";
    /** @var string|null */
    static private $default_dictionary_dir = null;
    /** @var string|null */
    static private $default_dictionary = null;
    /** @var string|null */
    static private $auto_dictionary_dir = null;

    function __construct()
    {
        $this->tmp_file = tempnam(sys_get_temp_dir(), 'mecab-');
        $this->command = static::$default_command;
        $this->dictionary = static::$default_dictionary;
        $this->dictionary_dir = static::$default_dictionary_dir;
    }

    /**
     * @param array $options
     * @return bool
     */
    public static function setDefaults(array $options = [])
    {
        if (isset($options['command']))
            static::$default_command = $options['command'];

        if (isset($options['dictionary_dir']))
            static::$default_dictionary_dir = $options['dictionary_dir'];

        if (isset($options['dictionary']))
            static::$default_dictionary = $options['dictionary'];

        return true;
    }

    //<editor-fold desc="static getter/setter">
    /**
     * @return string
     */
    public static function getDefaultCommand()
    {
        return self::$default_command;
    }

    /**
     * @param string $default_command
     */
    public static function setDefaultCommand(string $default_command)
    {
        self::$default_command = $default_command;
    }

    /**
     * @return null|string
     */
    public static function getDefaultDictionaryDir()
    {
        return self::$default_dictionary_dir;
    }

    /**
     * @param null|string $default_dictionary_dir
     */
    public static function setDefaultDictionaryDir(string $default_dictionary_dir = null)
    {
        self::$default_dictionary_dir = $default_dictionary_dir;
    }


    /**
     * @return null|string
     */
    public static function getDefaultDictionary()
    {
        return self::$default_dictionary;
    }

    /**
     * @param null|string $default_dictionary
     */
    public static function setDefaultDictionary(string $default_dictionary = null)
    {
        self::$default_dictionary = $default_dictionary;
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
     * @param string|null $dictionary_dir
     */
    public function setDictionaryDir(string $dictionary_dir = null)
    {
        $this->dictionary_dir = $dictionary_dir;
    }

    /**
     * @param string|null $dictionary
     */
    public function setDictionary(string $dictionary = null)
    {
        $this->dictionary = $dictionary;
    }
    //</editor-fold>

    public function getDictionaryFilePath()
    {
        if (! $this->dictionary) return null;

        // フルパス
        if (strpos($this->dictionary, '/') === 0) return $this->dictionary;
        if (strpos($this->dictionary, ':\\') === 1) return $this->dictionary;

        $dir = rtrim($this->dictionary_dir ?? static::autoDictionaryDir(), '/\\') . '/';
        return $dir . $this->dictionary;
    }

    /**
     * @param $text
     * @return meCabWord[]|null
     * @throws MeCabException
     */
    public function analysis($text)
    {
        if (! file_put_contents($this->tmp_file, $text)) {
            throw new MeCabException($this, sprintf('Error write tmp file in %s', $this->tmp_file));
        }
        try {
            $command = [];
            $command[] = $this->command;
            $command[] = $this->tmp_file;
            if ($path = $this->getDictionaryFilePath()) {
                $command[] = sprintf('--userdic="%s"', $path);
            } elseif ($this->dictionary_dir) {
                $command[] = sprintf('--dicdir="%s"', $this->dictionary_dir);
            }

            $this->exec(implode(' ', $command), $res);
            if ($res && (count($res) > 0)) {
                $words = array();
                foreach ($res as $k => $r) {
                    if ($r == 'EOS' && count($res) >= ($k + 1)) {
                        break;
                    }
                    $words[] = new meCabWord($r);
                }
                return $words;
            } else {
                throw new MeCabException(sprintf('Error text analysis.'));
            }
        } finally {
            @unlink($this->tmp_file);
        }
    }

    /**
     * @param string $text
     * @return meCabWord[]|null
     * @throws MeCabException
     */
    public static function parse($text)
    {
        return (new meCab())->analysis($text);
    }

    /**
     * @param string $text
     * @return string
     * @throws MeCabException
     */
    public static function toReading($text)
    {
        return implode('', array_column((new meCab())->analysis($text), 'reading'));
    }

    /**
     * @param $command
     * @param $res
     * @return string
     */
    private function exec($command, &$res)
    {
        if ($text = exec($command, $res)) {
        }
        return $text;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __isset($name)
    {
        return isset($this->$name);
    }
}

/**
 * Class meCabWord
 * @package meCab
 * @property-read string|null $str
 * @property-read string|null $text
 * @property-read string|null $speech
 * @property-read string|null $speech_info
 * @property-read string|null $conjugate
 * @property-read string|null $conjugate_type
 * @property-read string|null $original
 * @property-read string|null $reading
 * @property-read string|null $pronunciation
 */
class meCabWord
{
    protected $str;
    protected $text;
    protected $speech;
    protected $speech_info;
    protected $conjugate;
    protected $conjugate_type;
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

        $this->speech_info = array_fill(0, 3, null);
        foreach ($info as $k => $t) {
            if ($t == '*') {
                continue;
            }
            if ($k == 0) {
                $this->speech = $t;
            } else if ($k <= 3) {
                $this->speech_info[$k - 1] = $t;
            } else if ($k == 4) {
                $this->conjugate = $t;
            } else if ($k == 5) {
                $this->conjugate_type = $t;
            } else if ($k == 6) {
                $this->original = $t;
            } else if ($k == 7) {
                $this->reading = $t;
            } else if ($k == 8) {
                $this->pronunciation = $t;
            }
        }
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __isset($name)
    {
        return isset($this->$name);
    }
}


class MeCabException extends \Exception
{
    /**
     * @var meCab
     */
    public $instance;

    public function __construct(meCab $instance, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->instance = $instance;
        parent::__construct($message, $code, $previous);
    }
}

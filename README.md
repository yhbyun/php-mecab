# MeCab reading php7 library
オープンソース 形態素解析エンジン [MeCab](http://taku910.github.io/mecab/) を、PHP用のインストール不要なライブラリ化した [ateliee/mecab](https://github.com/ateliee/mecab) を更に使いやすくしました。

また、Laravelなどで組み込みやすいように調整を加えてあります。

## 使い方
事前にMeCabをConsoleで使えるようにしてください。

また、使用する [辞書](https://github.com/neologd/mecab-ipadic-neologd) によって解析結果が変わる可能性があります。

###### composer.json
```
    "require": {
        "youaoi/php-mecab": "dev-master"
    },
```

###### php
```
use Youaoi\MeCab\MeCab;
...

// 設定 ※任意。不要な値は省略できます。

    MeCab::setDefaults([
    
        // PATHが通っていないmecabを起動させる時に設定(default: mecab)
        'command' => '~/.local/bin/mecab',
         
        // 独自の辞書ディレクトリを利用する場合に設定(default: null)
        'dictionaryDir' => '~/.local/mecab-neologd',
        
        // 指定辞書を利用し解析。複数利用時はカンマ区切り(default: null)
        'dictionary' => 'hoge1.dic,hoge2.dic',
        
        // 解析時に生成するMeCabWordのclass名を指定(default: Youaoi\\MeCab\\MeCabWord)
        'wordClass' => User\\MeCab\\MeCabWord::class,
    ]);

...
// シンプルな使い方１

    echo MeCab::toReading('すもももももももものうち');
    // Output: スモモモモモモモモノウチ
    
    echo MeCab::toReading('山田太郎');
    // Output: ヤマダタロウ
    
    echo MeCab::toReading('C-3PO');
    // Output: 
    
    echo MeCab::toReading('出席番号:13番');
    // Output: シュッセキバンゴウバン

// シンプルな使い方２

    echo MeCab::toSortText('すもももももももものうち');
    // Output: スモモモモモモモモノウチ
    
    echo MeCab::toSortText('山田太郎');
    // Output: ヤマダタロウ
    
    echo MeCab::toSortText('C-3PO');
    // Output: C-3PO
    
    echo MeCab::toSortText('出席番号:13番');
    // Output: シュッセキバンゴウ:13バン

// ソート用文字列の作成方法
    $sentence = '仕様書_copy-new(3)';

    $text = MeCab::toSortText($sentence);
    $text = strtoupper(mb_convert_kana($text, 'rnashk'));

    // 漢字範囲など精度が悪いが、精密に判定する必要が無いので、ざっくりと削除
    $text = preg_replace('/[^ｦ-ﾝA-Z0-9一-龠]+/u','' ,$text);
    
    echo $text;
    // Output: ｼﾖｳｼｮCOPYNEW3

... 

// データを解析する

    var_dump($a = MeCab::parse('すもももももももものうち'));
    
    $mecab = new MeCab();
    
    var_dump($b = $mecab->analysis('すもももももももものうち'));
    
    // $a == $b

```

※使用する環境によって、解析結果が変わり、テストケースが通らなくなる場合があります。

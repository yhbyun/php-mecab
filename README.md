# meCab reading php library
オープンソース 形態素解析エンジンmeCab( http://taku910.github.io/mecab/ )を

phpで利用する場合、インストールが大変なのでateliee氏がライブラリ化したもの( https://github.com/ateliee/mecab )を

Laravelなどで組み込みやすいように調整しました。

## 使い方
meCabは別途インストールしている必要があります。

https://github.com/neologd/mecab-ipadic-neologd

```
    "require": {
        "ateliee/mecab": "dev-master"
    },
     "repositories": [
         {
             "type": "vcs",
             "url": "https://github.com/youaoi/mecab.git"
         }
     ]
```

```
use meCab\meCab;
...

$mecab = new meCab();
var_dump($mecab->analysis('すもももももももものうち'));

```

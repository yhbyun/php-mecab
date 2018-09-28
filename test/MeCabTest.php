<?php
/**
 * Created by PhpStorm.
 * User: aoi
 * Date: 2018/09/28
 * Time: 10:18
 */

namespace Youaoi\MeCab;

use PHPUnit\Framework\TestCase;

include_once __DIR__ . '/../src/MeCab.php';
include_once __DIR__ . '/../src/MeCabWord.php';
include_once __DIR__ . '/../src/MeCabException.php';

class MeCabTest extends TestCase
{
    public function testToReading()
    {
        $this->assertEquals('ヤマダタロウ', MeCab::toReading('山田太郎'));
        $this->assertEquals('スモモモモモモモモノウチ', MeCab::toReading('すもももももももものうち'));
    }

    public function testToSortText()
    {
        $this->assertEquals('ヤマダタロウ', MeCab::toSortText('山田太郎'));
        $this->assertEquals('スモモモモモモモモノウチ', MeCab::toReading('すもももももももものうち'));
        $this->assertEquals('C-3PO', MeCab::toSortText('C-3PO'));
        $this->assertEquals('シュッセキバンゴウ:13バン', MeCab::toSortText('出席番号:13番'));
    }

    public function testParse()
    {
        $words = MeCab::parse('出席番号13番');

        $this->assertCount(4, $words);

        $this->assertEquals('出席', $words[0]->text);
        $this->assertEquals('バンゴウ', $words[1]->reading);
        $this->assertEquals('13', $words[2]->text);
        $this->assertEquals('バン', $words[3]->reading);
    }

    public function testAnalysis()
    {
        $mecab = new MeCab();
        $words = $mecab->analysis('出席番号13番');

        $this->assertCount(4, $words);

        $this->assertEquals('出席', $words[0]->text);
        $this->assertEquals('バンゴウ', $words[1]->reading);
        $this->assertEquals('13', $words[2]->text);
        $this->assertEquals('バン', $words[3]->reading);
    }
}

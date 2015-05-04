<?php
namespace tests\units\Typolib;

use atoum;
use Typolib\Strings as _Strings;

require_once __DIR__ . '/../bootstrap.php';

class Strings extends atoum\test
{
    public function getSentencesFromTextDP()
    {
        return [
            ['This is sentence one. Sentence two!', ['This is sentence one.', 'Sentence two!']],
            ['This is sentence one? Sentence two.\'', ['This is sentence one?', 'Sentence two.\'']],
        ];
    }

    /**
     * @dataProvider getSentencesFromTextDP
     */
    public function testSentencesFromText($a, $b)
    {
        $obj = new _Strings();
        $this
            ->array($obj->getSentencesFromText($a))
                ->isEqualTo($b);
    }
}

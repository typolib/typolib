<?php
namespace tests\units\Typolib;

use atoum;
use Typolib\Utils as _Utils;

require_once __DIR__ . '/../bootstrap.php';

class Utils extends atoum\test
{
    public function sanitizeFileNameDP()
    {
        return [
            ['[test]', 'test'],
            ['te st', 'test'],
            ['test../', 'test..'],
            ['st@te', 'stte'],
            ['te_st', 'te_st'],
            ['Test9', 'Test9'],
        ];
    }

    /**
     * @dataProvider sanitizeFileNameDP
     */
    public function testSanitizeFileName($a, $b)
    {
        $this
            ->string(_Utils::sanitizeFileName($a))
                ->isEqualTo($b);
    }
}

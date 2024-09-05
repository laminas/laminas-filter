<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\StripNewlines;
use LaminasTest\Filter\TestAsset\StringableObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StripNewlinesTest extends TestCase
{
    /** @return array<string, array{0: mixed, 1: mixed}> */
    public static function basicDataProvider(): array
    {
        return [
            'Empty String'        => ['', ''],
            'Null'                => [null, null],
            'String without CRLF' => ['string', 'string'],
            'String with CRLF'    => ["Some\nText\r\nHere", 'SomeTextHere'],
            'Escaped CRLF'        => ['\r\n', '\r\n'],
            'Stringable'          => [new StringableObject("Hey\nThere"), 'HeyThere'],
            'Integer'             => [1, '1'],
            'Float'               => [1.23, '1.23'],
            'True'                => [true, '1'],
            'False'               => [false, ''],
            'Array'               => [['a' => "Hey\rThere", 'b' => null], ['a' => 'HeyThere', 'b' => null]],
            'Nested Array'        => [['a' => ["Hey\rThere"]], ['a' => ['HeyThere']]],
        ];
    }

    #[DataProvider('basicDataProvider')]
    public function testBasicBehaviour(mixed $input, mixed $expect): void
    {
        $filter = new StripNewlines();
        self::assertSame($expect, $filter->filter($input));
        self::assertSame($expect, $filter->__invoke($input));
    }
}

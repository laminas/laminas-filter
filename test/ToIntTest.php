<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\ToInt;
use LaminasTest\Filter\TestAsset\StringableObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\TestCase;

use function restore_error_handler;
use function set_error_handler;

use const E_WARNING;
use const PHP_INT_MAX;

final class ToIntTest extends TestCase
{
    /** @return array<string, array{0: mixed, 1: mixed}> */
    public static function basicDataProvider(): array
    {
        $object     = (object) ['foo' => 'bar'];
        $stringable = new StringableObject('Foo');

        return [
            'String'          => ['string', 0],
            'Digits'          => ['123', 123],
            'Float String'    => ['1.23', 1],
            'Float'           => [1.23, 1],
            'Int'             => [123, 123],
            'Array'           => [[123, '123', 'foo'], [123, '123', 'foo']],
            'Object'          => [$object, $object],
            'Stringable'      => [$stringable, $stringable],
            'Null'            => [null, null],
            'Empty String'    => ['', 0],
            'Negative Int'    => [-1, -1],
            'Negative String' => ['-1', -1],
        ];
    }

    #[DataProvider('basicDataProvider')]
    public function testBasicBehaviour(mixed $input, mixed $expect): void
    {
        $filter = new ToInt();
        self::assertSame($expect, $filter->filter($input));
        self::assertSame($expect, $filter->__invoke($input));
    }

    #[RequiresPhp('<= 8.4.99')]
    public function testHugeNumbersAreTruncatedToIntMax(): void
    {
        $huge   = '9223372036854775807999';
        $filter = new ToInt();
        self::assertSame(PHP_INT_MAX, $filter->__invoke($huge));
    }

    #[RequiresPhp('>= 8.5.0')]
    public function testHugeNumbersAreTruncatedToIntMaxOn85WithAWarning(): void
    {
        $called = false;
        $huge   = '9223372036854775807999';

        set_error_handler(static function (int $code, string $message) use (&$called, $huge): bool {
            self::assertSame(E_WARNING, $code);
            self::assertStringContainsString($huge, $message);
            self::assertStringContainsString('is not representable as an int', $message);
            $called = true;

            return true;
        });

        $filter = new ToInt();
        try {
            self::assertSame(PHP_INT_MAX, $filter->__invoke($huge));
            self::assertTrue($called, 'The error handler should have been called');
        } finally {
            restore_error_handler();
        }
    }
}

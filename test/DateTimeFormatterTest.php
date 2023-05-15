<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use DateTime;
use DateTimeInterface;
use Laminas\Filter\DateTimeFormatter;
use Laminas\Filter\Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

use function date_default_timezone_get;
use function date_default_timezone_set;

class DateTimeFormatterTest extends TestCase
{
    private string $defaultTimezone;

    public function setUp(): void
    {
        $this->defaultTimezone = date_default_timezone_get();
    }

    public function tearDown(): void
    {
        date_default_timezone_set($this->defaultTimezone);
    }

    /** @return list<array{0: mixed}> */
    public static function returnUnfilteredDataProvider(): array
    {
        return [
            [null],
            [''],
            [new stdClass()],
            [
                [
                    '1',
                    -1,
                ],
            ],
            [0.53],
        ];
    }

    #[DataProvider('returnUnfilteredDataProvider')]
    public function testReturnUnfiltered(mixed $input): void
    {
        date_default_timezone_set('UTC');

        $filter = new DateTimeFormatter();

        self::assertSame($input, $filter($input));
    }

    public function testFormatterFormatsZero(): void
    {
        date_default_timezone_set('UTC');

        $filter = new DateTimeFormatter();
        $result = $filter->filter(0);
        self::assertSame('1970-01-01T00:00:00+0000', $result);
    }

    public function testDateTimeFormatted(): void
    {
        date_default_timezone_set('UTC');

        $filter = new DateTimeFormatter();
        $result = $filter->filter('2012-01-01');
        self::assertSame('2012-01-01T00:00:00+0000', $result);
    }

    public function testDateTimeFormattedWithAlternateTimezones(): void
    {
        $filter = new DateTimeFormatter();

        date_default_timezone_set('Europe/Paris');

        $resultParis = $filter->filter('2012-01-01');
        self::assertSame('2012-01-01T00:00:00+0100', $resultParis);

        date_default_timezone_set('America/New_York');

        $resultNewYork = $filter->filter('2012-01-01');
        self::assertSame('2012-01-01T00:00:00-0500', $resultNewYork);
    }

    public function testSetFormat(): void
    {
        date_default_timezone_set('UTC');

        $filter = new DateTimeFormatter();
        $filter->setFormat(DateTimeInterface::RFC1036);
        $result = $filter->filter('2012-01-01');
        self::assertSame('Sun, 01 Jan 12 00:00:00 +0000', $result);
    }

    public function testFormatDateTimeFromTimestamp(): void
    {
        date_default_timezone_set('UTC');

        $filter = new DateTimeFormatter();
        $result = $filter->filter(1_359_739_801);
        self::assertSame('2013-02-01T17:30:01+0000', $result);
    }

    public function testAcceptDateTimeValue(): void
    {
        date_default_timezone_set('UTC');

        $filter = new DateTimeFormatter();
        $result = $filter->filter(new DateTime('2012-01-01'));
        self::assertSame('2012-01-01T00:00:00+0000', $result);
    }

    public function testInvalidArgumentExceptionThrownOnInvalidInput(): void
    {
        $filter = new DateTimeFormatter();
        $this->expectException(Exception\InvalidArgumentException::class);
        $filter->filter('2013-31-31');
    }
}

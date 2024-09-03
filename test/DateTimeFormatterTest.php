<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Laminas\Filter\DateTimeFormatter;
use Laminas\Filter\Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

use function date_default_timezone_get;
use function date_default_timezone_set;

class DateTimeFormatterTest extends TestCase
{
    /** @var non-empty-string */
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
            [new stdClass()],
            [
                [
                    '1',
                    -1,
                ],
            ],
            [0.53],
            [true],
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
        self::assertSame('1970-01-01T00:00:00+00:00', $result);
    }

    public function testDateTimeFormatted(): void
    {
        date_default_timezone_set('UTC');

        $filter = new DateTimeFormatter();
        $result = $filter->filter('2012-01-01');
        self::assertSame('2012-01-01T00:00:00+00:00', $result);
    }

    public function testReturnExceptionOnInvalidTimezone(): void
    {
        date_default_timezone_set('UTC');

        self::expectException(Exception\InvalidArgumentException::class);

        new DateTimeFormatter([
            'timezone' => 'Continent/City',
        ]);
    }

    public function testDateTimeFormattedWithAlternateTimezones(): void
    {
        date_default_timezone_set('UTC');

        $filterParis = new DateTimeFormatter([
            'timezone' => 'Europe/Paris',
        ]);

        $resultParis = $filterParis->filter('2012-01-01');
        self::assertSame('2012-01-01T00:00:00+01:00', $resultParis);

        $filterNewYork = new DateTimeFormatter([
            'timezone' => 'America/New_York',
        ]);

        $resultNewYork = $filterNewYork->filter('2012-01-01');
        self::assertSame('2012-01-01T00:00:00-05:00', $resultNewYork);
    }

    /**
     * @throws \Exception
     */
    public function testTimezoneRemainUnchangedOnDateTimeInterfaceInput(): void
    {
        date_default_timezone_set('UTC');

        $filter = new DateTimeFormatter([
            'timezone' => 'UTC',
        ]);

        $datetime = new DateTimeImmutable('2024-01-01 00:00:00', new DateTimeZone('America/New_York'));

        $result = $filter->filter($datetime);

        self::assertSame('2024-01-01T00:00:00-05:00', $result);
    }

    public function testSetFormat(): void
    {
        date_default_timezone_set('UTC');

        $filter = new DateTimeFormatter([
            'format' => DateTimeInterface::RFC1036,
        ]);
        self::assertSame('Sun, 01 Jan 12 00:00:00 +0000', $filter->filter('2012-01-01'));

        $filter = new DateTimeFormatter([
            'format' => 'd-m-Y',
        ]);
        self::assertSame('16-08-2024', $filter->filter('2024-08-16 00:00:00'));

        $filter = new DateTimeFormatter([
            'format' => 'asd Y W',
        ]);

        self::assertSame('am0016 2024 33', $filter->filter('2024-08-16 00:00:00'));
    }

    public function testFormatDateTimeFromTimestamp(): void
    {
        date_default_timezone_set('UTC');

        $filter = new DateTimeFormatter();
        $result = $filter->filter(1_359_739_801);
        self::assertSame('2013-02-01T17:30:01+00:00', $result);
    }

    public function testAcceptDateTimeValue(): void
    {
        date_default_timezone_set('UTC');

        $filter = new DateTimeFormatter();
        $result = $filter->filter(new DateTime('2012-01-01'));
        self::assertSame('2012-01-01T00:00:00+00:00', $result);
    }

    public function testThrowInvalidArgumentOnInvalidInput(): void
    {
        $filter = new DateTimeFormatter();
        self::expectException(Exception\InvalidArgumentException::class);
        $filter->filter('2013-31-31');
    }

    public function testAcceptDateTimeInterface(): void
    {
        date_default_timezone_set('UTC');

        $filter = new DateTimeFormatter();

        self::assertSame(
            '2024-08-09T00:00:00+00:00',
            $filter->filter(new DateTimeImmutable('2024-08-09'))
        );

        self::assertSame(
            '2024-08-09T00:00:00+00:00',
            $filter->filter(new DateTime('2024-08-09'))
        );
    }
}

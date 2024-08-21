<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Laminas\Filter\DateTimeFormatter;
use Laminas\Filter\Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class DateTimeFormatterTest extends TestCase
{
    /** @return list<array{0: mixed}> */
    public static function returnExceptionDataProvider(): array
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
            ['2024-31-31'],
            [true],
            [false],
        ];
    }

    #[DataProvider('returnExceptionDataProvider')]
    public function testReturnExceptionForUnfilteredValues(mixed $input): void
    {
        $filter = new DateTimeFormatter();

        $this->expectException(Exception\InvalidArgumentException::class);

        $filter->filter($input);
    }

    public function testFormatterFormatsZero(): void
    {
        $filter = new DateTimeFormatter();
        $result = $filter->filter(0);
        self::assertSame('1970-01-01T00:00:00+00:00', $result);
    }

    public function testDateTimeFormatted(): void
    {
        $filter = new DateTimeFormatter();
        $result = $filter->filter('2012-01-01');
        self::assertSame('2012-01-01T00:00:00+00:00', $result);
    }

    /**
     * @throws \Exception
     */
    public function testDateTimeFormattedWithAlternateTimezones(): void
    {
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
    public function testSetFormat(): void
    {
        $filter = new DateTimeFormatter([
            'format' => DateTimeInterface::RFC1036,
        ]);

        $resultRfc = $filter->filter('2012-01-01');
        self::assertSame('Sun, 01 Jan 12 00:00:00 +0000', $resultRfc);

        $filter       = new DateTimeFormatter([
            'format' => 'd-m-Y',
        ]);
        $resultCustom = $filter->filter('2024-08-16 00:00:00');
        self::assertSame('16-08-2024', $resultCustom);
    }

    public function testFormatDateTimeFromTimestamp(): void
    {
        $filter = new DateTimeFormatter();
        $result = $filter->filter(1_359_739_801);
        self::assertSame('2013-02-01T17:30:01+00:00', $result);
    }

    public function testAcceptDateTimeValue(): void
    {
        $filter = new DateTimeFormatter();
        $result = $filter->filter(new DateTime('2012-01-01'));
        self::assertSame('2012-01-01T00:00:00+00:00', $result);
    }

    public function testAcceptDateTimeInterface(): void
    {
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

<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use DateTime;
use Laminas\Filter\DateTimeFormatter;
use Laminas\Filter\Exception;
use PHPUnit\Framework\TestCase;
use stdClass;

use function date_default_timezone_get;
use function date_default_timezone_set;

class DateTimeFormatterTest extends TestCase
{
    protected $defaultTimezone;

    public function setUp(): void
    {
        $this->defaultTimezone = date_default_timezone_get();
    }

    public function tearDown(): void
    {
        date_default_timezone_set($this->defaultTimezone);
    }

    public function returnUnfilteredDataProvider()
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

    /**
     * @dataProvider returnUnfilteredDataProvider
     */
    public function testReturnUnfiltered($input): void
    {
        date_default_timezone_set('UTC');

        $filter = new DateTimeFormatter();

        $this->assertSame($input, $filter($input));
    }

    public function testFormatterFormatsZero(): void
    {
        date_default_timezone_set('UTC');

        $filter = new DateTimeFormatter();
        $result = $filter->filter(0);
        $this->assertSame('1970-01-01T00:00:00+0000', $result);
    }

    public function testDateTimeFormatted(): void
    {
        date_default_timezone_set('UTC');

        $filter = new DateTimeFormatter();
        $result = $filter->filter('2012-01-01');
        $this->assertSame('2012-01-01T00:00:00+0000', $result);
    }

    public function testDateTimeFormattedWithAlternateTimezones(): void
    {
        $filter = new DateTimeFormatter();

        date_default_timezone_set('Europe/Paris');

        $resultParis = $filter->filter('2012-01-01');
        $this->assertSame('2012-01-01T00:00:00+0100', $resultParis);

        date_default_timezone_set('America/New_York');

        $resultNewYork = $filter->filter('2012-01-01');
        $this->assertSame('2012-01-01T00:00:00-0500', $resultNewYork);
    }

    public function testSetFormat(): void
    {
        date_default_timezone_set('UTC');

        $filter = new DateTimeFormatter();
        $filter->setFormat(DateTime::RFC1036);
        $result = $filter->filter('2012-01-01');
        $this->assertSame('Sun, 01 Jan 12 00:00:00 +0000', $result);
    }

    public function testFormatDateTimeFromTimestamp(): void
    {
        date_default_timezone_set('UTC');

        $filter = new DateTimeFormatter();
        $result = $filter->filter(1359739801);
        $this->assertSame('2013-02-01T17:30:01+0000', $result);
    }

    public function testAcceptDateTimeValue(): void
    {
        date_default_timezone_set('UTC');

        $filter = new DateTimeFormatter();
        $result = $filter->filter(new DateTime('2012-01-01'));
        $this->assertSame('2012-01-01T00:00:00+0000', $result);
    }

    public function testInvalidArgumentExceptionThrownOnInvalidInput(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);

        $filter = new DateTimeFormatter();
        $result = $filter->filter('2013-31-31');
    }
}

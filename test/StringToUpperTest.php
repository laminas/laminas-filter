<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception;
use Laminas\Filter\StringToUpper as StringToUpperFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

use function mb_internal_encoding;

class StringToUpperTest extends TestCase
{
    private StringToUpperFilter $filter;

    public function setUp(): void
    {
        $this->filter = new StringToUpperFilter();
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasic(): void
    {
        $filter         = $this->filter;
        $valuesExpected = [
            'STRING' => 'STRING',
            'ABC1@3' => 'ABC1@3',
            'A b C'  => 'A B C',
        ];

        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }
    }

    /**
     * Ensures that the filter follows expected behavior with
     * specified encoding
     */
    public function testWithEncoding(): void
    {
        $filter         = $this->filter;
        $valuesExpected = [
            'ü'     => 'Ü',
            'ñ'     => 'Ñ',
            'üñ123' => 'ÜÑ123',
        ];

        $filter->setEncoding('UTF-8');
        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }
    }

    public function testFalseEncoding(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('is not supported');
        $this->filter->setEncoding('aaaaa');
    }

    /**
     * @Laminas-8989
     */
    public function testInitiationWithEncoding(): void
    {
        $valuesExpected = [
            'ü'     => 'Ü',
            'ñ'     => 'Ñ',
            'üñ123' => 'ÜÑ123',
        ];

        $filter = new StringToUpperFilter(['encoding' => 'UTF-8']);
        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }
    }

    /**
     *  @Laminas-9058
     */
    public function testCaseInsensitiveEncoding(): void
    {
        $filter         = $this->filter;
        $valuesExpected = [
            'ü'     => 'Ü',
            'ñ'     => 'Ñ',
            'üñ123' => 'ÜÑ123',
        ];

        $filter->setEncoding('UTF-8');
        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }

        $this->filter->setEncoding('utf-8');
        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }

        $this->filter->setEncoding('UtF-8');
        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }
    }

    /**
     * @group Laminas-9854
     */
    public function testDetectMbInternalEncoding(): void
    {
        self::assertSame(mb_internal_encoding(), $this->filter->getEncoding());
    }

    /** @return list<array{0: mixed}> */
    public function returnUnfilteredDataProvider(): array
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    'lower case written',
                    'This should stay the same',
                ],
            ],
        ];
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     */
    public function testReturnUnfiltered(mixed $input): void
    {
        self::assertSame($input, $this->filter->filter($input));
    }

    /**
     * @group 7147
     */
    public function testFilterUsesGetEncodingMethod(): void
    {
        $filterMock = $this->getMockBuilder(StringToUpperFilter::class)
            ->setMethods(['getEncoding'])
            ->getMock();
        $filterMock->expects($this->once())
            ->method('getEncoding')
            ->with();
        $filterMock->filter('foo');
    }
}

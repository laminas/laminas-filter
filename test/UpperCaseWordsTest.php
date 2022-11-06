<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception;
use Laminas\Filter\UpperCaseWords as UpperCaseWordsFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

use function mb_internal_encoding;

/**
 * @covers \Laminas\Filter\UpperCaseWords
 */
class UpperCaseWordsTest extends TestCase
{
    private UpperCaseWordsFilter $filter;

    public function setUp(): void
    {
        $this->filter = new UpperCaseWordsFilter();
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasic(): void
    {
        $filter         = $this->filter;
        $valuesExpected = [
            'string' => 'String',
            'aBc1@3' => 'Abc1@3',
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
            '√º'      => '√º',
            '√±'      => '√±',
            '√º√±123' => '√º√±123',
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
            '√º'      => '√º',
            '√±'      => '√±',
            '√º√±123' => '√º√±123',
        ];

        $filter = new UpperCaseWordsFilter([
            'encoding' => 'UTF-8',
        ]);
        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }
    }

    /**
     * @Laminas-9058
     */
    public function testCaseInsensitiveEncoding(): void
    {
        $filter         = $this->filter;
        $valuesExpected = [
            '√º'      => '√º',
            '√±'      => '√±',
            '√º√±123' => '√º√±123',
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
            [123],
            [123.456],
            [
                [
                    'Upper CASE and lowerCase Words WRITTEN',
                    'This Should Stay The Same',
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
}

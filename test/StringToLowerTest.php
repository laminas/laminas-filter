<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception;
use Laminas\Filter\StringToLower as StringToLowerFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

use function mb_internal_encoding;

class StringToLowerTest extends TestCase
{
    private StringToLowerFilter $filter;

    public function setUp(): void
    {
        $this->filter = new StringToLowerFilter();
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasic(): void
    {
        $filter         = $this->filter;
        $valuesExpected = [
            'string' => 'string',
            'aBc1@3' => 'abc1@3',
            'A b C'  => 'a b c',
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
            'Ü'     => 'ü',
            'Ñ'     => 'ñ',
            'ÜÑ123' => 'üñ123',
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
            'Ü'     => 'ü',
            'Ñ'     => 'ñ',
            'ÜÑ123' => 'üñ123',
        ];

        $filter = new StringToLowerFilter(['encoding' => 'UTF-8']);
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
            'Ü'     => 'ü',
            'Ñ'     => 'ñ',
            'ÜÑ123' => 'üñ123',
        ];

        try {
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
        } catch (Exception\ExtensionNotLoadedException $e) {
            self::assertContains('mbstring is required', $e->getMessage());
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
                    'UPPER CASE WRITTEN',
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
        $filterMock = $this->getMockBuilder(StringToLowerFilter::class)
            ->setMethods(['getEncoding'])
            ->getMock();
        $filterMock->expects($this->once())
            ->method('getEncoding')
            ->with();
        $filterMock->filter('foo');
    }
}

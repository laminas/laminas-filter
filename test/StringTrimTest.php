<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\StringTrim;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use stdClass;

use function mb_convert_encoding;

class StringTrimTest extends TestCase
{
    private StringTrim $filter;

    public function setUp(): void
    {
        $this->filter = new StringTrim();
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasic(): void
    {
        $valuesExpected = [
            'string' => 'string',
            ' str '  => 'str',
            "\ns\t"  => 's',
        ];
        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $this->filter->filter($input));
        }
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testUtf8(): void
    {
        $value = mb_convert_encoding("\xa0a\xa0", 'UTF-8', 'ISO-8859-1');
        self::assertSame('a', $this->filter->filter($value));
    }

    /**
     * Ensures that getCharList() returns expected default value
     */
    public function testGetCharList(): void
    {
        self::assertSame(null, $this->filter->getCharList());
    }

    /**
     * Ensures that setCharList() follows expected behavior
     */
    public function testSetCharList(): void
    {
        $this->filter->setCharList('&');
        self::assertSame('&', $this->filter->getCharList());
    }

    /**
     * Ensures expected behavior under custom character list
     */
    public function testCharList(): void
    {
        $this->filter->setCharList('&');
        self::assertSame('a&b', $this->filter->__invoke('&&a&b&&'));
    }

    #[Group('Laminas-7183')]
    public function testLaminas7183(): void
    {
        self::assertSame('Зенд', $this->filter->filter('Зенд'));
    }

    #[Group('Laminas-170')]
    public function testLaminas170(): void
    {
        self::assertSame('Расчет', $this->filter->filter('Расчет'));
    }

    #[Group('Laminas-7902')]
    public function testLaminas7902(): void
    {
        self::assertSame('/', $this->filter->filter('/'));
    }

    #[Group('Laminas-10891')]
    public function testLaminas10891(): void
    {
        self::assertSame('Зенд', $this->filter->filter('   Зенд   '));
        self::assertSame('Зенд', $this->filter->filter('Зенд   '));
        self::assertSame('Зенд', $this->filter->filter('   Зенд'));

        $trimCharList = " \t\n\r\x0B・。";
        $filter       = new StringTrim($trimCharList);
        self::assertSame('Зенд', $filter->filter('。  Зенд  。'));
    }

    /** @return list<array{0: mixed}> */
    public static function getNonStringValues(): array
    {
        return [
            [1],
            [1.0],
            [true],
            [false],
            [null],
            [[1, 2, 3]],
            [new stdClass()],
        ];
    }

    #[DataProvider('getNonStringValues')]
    public function testShouldNotFilterNonStringValues(mixed $value): void
    {
        self::assertSame($value, $this->filter->filter($value));
    }

    /**
     * Ensures expected behavior with '0' as character list
     */
    #[Group('6261')]
    public function testEmptyCharList(): void
    {
        $this->filter->setCharList('0');
        self::assertSame('a0b', $this->filter->filter('00a0b00'));

        $this->filter->setCharList('');
        self::assertSame('str', $this->filter->filter(' str '));
    }
}

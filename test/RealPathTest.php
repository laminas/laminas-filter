<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\RealPath as RealPathFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

use function str_contains;

use const DIRECTORY_SEPARATOR;
use const PHP_OS;

class RealPathTest extends TestCase
{
    private RealPathFilter $filter;

    public function setUp(): void
    {
        $this->filter = new RealPathFilter();
    }

    /**
     * Ensures expected behavior for existing file
     */
    public function testFileExists(): void
    {
        $filename = __DIR__ . '/_files/file.1';
        $result   = $this->filter->filter($filename);
        self::assertStringContainsString($filename, $result);
    }

    /**
     * Ensures expected behavior for nonexistent file
     */
    public function testFileNonexistent(): void
    {
        $path = '/path/to/nonexistent';
        if (str_contains(PHP_OS, 'BSD')) {
            self::assertSame($path, $this->filter->filter($path));
        } else {
            self::assertSame(false, $this->filter->filter($path));
        }
    }

    public function testGetAndSetExistsParameter(): void
    {
        self::assertTrue($this->filter->getExists());
        $this->filter->setExists(false);
        self::assertFalse($this->filter->getExists());

        $this->filter->setExists(['unknown']);
        self::assertTrue($this->filter->getExists());
    }

    public function testNonExistentPath(): void
    {
        $filter = $this->filter;
        $filter->setExists(false);

        $path = __DIR__ . DIRECTORY_SEPARATOR . '_files';
        self::assertSame($path, $filter($path));

        $path2 = __DIR__ . DIRECTORY_SEPARATOR . '_files'
               . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '_files';
        self::assertSame($path, $filter($path2));

        $path3 = __DIR__ . DIRECTORY_SEPARATOR . '_files'
               . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.'
               . DIRECTORY_SEPARATOR . '_files';
        self::assertSame($path, $filter($path3));
    }

    /** @return list<array{0: mixed}> */
    public static function returnUnfilteredDataProvider(): array
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    __DIR__ . '/_files/file.1',
                    __DIR__ . '/_files/file.2',
                ],
            ],
        ];
    }

    #[DataProvider('returnUnfilteredDataProvider')]
    public function testReturnUnfiltered(mixed $input): void
    {
        self::assertSame($input, (new RealPathFilter())->filter($input));
    }
}

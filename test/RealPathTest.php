<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\RealPath as RealPathFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

use function dirname;
use function getcwd;
use function str_contains;

use const PHP_OS;

final class RealPathTest extends TestCase
{
    public static function returnExistingFilePathDataProvider(): array
    {
        return [
            [__DIR__ . '/_files/file.1'],
            [__DIR__ . '/_files/../_files/file.1'],
            [__DIR__ . '/_files/././file.1'],
            [__DIR__ . '///_files///file.1'],
        ];
    }

    #[DataProvider('returnExistingFilePathDataProvider')]
    public function testExistingFileReturnsRealPath(string $filePath): void
    {
        $filter = new RealPathFilter();

        $result = $filter->filter($filePath);

        self::assertSame(__DIR__ . '/_files/file.1', $result);
    }

    public function testPathWithNonExistingPartsButRealResolutionIsNotValid(): void
    {
        $filter = new RealPathFilter();

        $path = __DIR__ . '/_files/foo/../bar/../file.1';

        $result = $filter->filter($path);

        self::assertSame($path, $result);
    }

    public function testNonexistentFileReturnsValuePassedToFilter(): void
    {
        $filter = new RealPathFilter();

        $path = '/path/to/nonexistent';
        self::assertSame($path, $filter->filter($path));
    }

    public function testBSDAllowsLastPortionToNotExist(): void
    {
        $filter = new RealPathFilter();

        $path = './nonexistent';

        if (str_contains(PHP_OS, 'BSD')) {
            $cwd = getcwd();
            self::assertIsString($cwd);
            self::assertSame($cwd . '/nonexistent', $filter($path));
        } else {
            self::assertSame($path, $filter($path));
        }
    }

    public static function returnNonExistentPathDataProvider(): array
    {
        $cwd = getcwd();
        self::assertIsString($cwd);

        return [
            ['/nonexistent/absolute/path', '/nonexistent/absolute/path'],
            ['/nonexistent/absolute/extra///slashes', '/nonexistent/absolute/extra/slashes'],
            ['./nonexistent/relative/path', $cwd . '/nonexistent/relative/path'],
            ['./dropped/parts/../../path', $cwd . '/path'],
            ['../relative/from/parent', dirname($cwd) . '/relative/from/parent'],
        ];
    }

    #[DataProvider('returnNonExistentPathDataProvider')]
    public function testNonExistentPathAllowed(string $path, string $expectedPath): void
    {
        $filter = new RealPathFilter(['exists' => false]);

        self::assertSame($expectedPath, $filter($path));
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

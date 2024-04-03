<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\FilterPluginManager;
use Laminas\Filter\ForceUriScheme;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(ForceUriScheme::class)]
class ForceUriSchemeTest extends TestCase
{
    /** @return list<array{0: non-empty-string, 1: mixed, 2: mixed}> */
    public static function filterDataProvider(): array
    {
        return [
            ['https', 'www.example.com/foo', 'www.example.com/foo'],
            ['https', 'www.example.com', 'www.example.com'],
            ['https', 'example.com', 'example.com'],
            ['https', 'http://www.example.com', 'https://www.example.com'],
            ['ftp', 'https://www.example.com', 'ftp://www.example.com'],
            ['foobar5', 'https://www.example.com', 'foobar5://www.example.com'],
            ['https', '//www.example.com', 'https://www.example.com'],
            ['https', 'http://http.example.com', 'https://http.example.com'],
            ['https', '42', '42'],
            ['https', 42, 42],
            ['https', false, false],
            ['https', null, null],
            ['https', (object) [], (object) []],
        ];
    }

    /**
     * @param non-empty-string $scheme
     */
    #[DataProvider('filterDataProvider')]
    public function testBasicFiltering(string $scheme, mixed $input, mixed $expect): void
    {
        $filter = new ForceUriScheme(['scheme' => $scheme]);
        self::assertEquals($expect, $filter->filter($input));
    }

    /**
     * @param non-empty-string $scheme
     */
    #[DataProvider('filterDataProvider')]
    public function testFilterCanBeInvoked(string $scheme, mixed $input, mixed $expect): void
    {
        $filter = new ForceUriScheme(['scheme' => $scheme]);
        self::assertEquals($expect, $filter->__invoke($input));
    }

    /** @return list<array{0: string}> */
    public static function badSchemeProvider(): array
    {
        return [
            [''],
            ['foo://'],
            ['mailto:'],
            ['...'],
        ];
    }

    #[DataProvider('badSchemeProvider')]
    public function testInvalidScheme(string $scheme): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The `scheme` option should be a string consisting only of letters and numbers');

        /** @psalm-suppress ArgumentTypeCoercion */
        new ForceUriScheme(['scheme' => $scheme]);
    }

    public function testThatThePluginManagerWillReturnAnInstance(): void
    {
        $manager = new FilterPluginManager($this->createMock(ContainerInterface::class));
        $filter  = $manager->get(ForceUriScheme::class);
        self::assertInstanceOf(ForceUriScheme::class, $filter);

        self::assertSame('https://example.com', $filter->filter('ftp://example.com'));
    }

    public function testThatThePluginManagerCanBuildWithOptions(): void
    {
        $manager = new FilterPluginManager($this->createMock(ContainerInterface::class));
        $filter  = $manager->build(ForceUriScheme::class, ['scheme' => 'muppets']);
        self::assertInstanceOf(ForceUriScheme::class, $filter);

        self::assertSame('muppets://example.com', $filter->filter('ftp://example.com'));
    }
}

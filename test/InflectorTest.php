<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\FilterPluginManager;
use Laminas\Filter\Inflector;
use Laminas\Filter\StringToLower;
use Laminas\Filter\StringToUpper;
use Laminas\Filter\Word\CamelCaseToDash;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function strtoupper;

use const DIRECTORY_SEPARATOR;

/** @psalm-import-type Options from Inflector */
final class InflectorTest extends TestCase
{
    /** @param Options $options */
    private static function withOptions(array $options): Inflector
    {
        return new Inflector(new FilterPluginManager(new ServiceManager()), $options);
    }

    /** @return array<string, array{0: mixed}> */
    public static function invalidTargets(): array
    {
        return [
            'Empty String' => [''],
            'Null'         => [null],
            'Array'        => [['foo' => 'bar']],
        ];
    }

    #[DataProvider('invalidTargets')]
    public function testTargetOptionMustBeValid(mixed $option): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @psalm-suppress MixedArgumentTypeCoercion - Intentionally invalid argument */
        self::withOptions([
            'target' => $option,
        ]);
    }

    public function testExpectedResultWithValidTargetOption(): void
    {
        $filter = self::withOptions([
            'target' => 'foo/:bar/:baz.:bat',
            'rules'  => [
                ':bar' => [StringToUpper::class],
                ':baz' => [StringToUpper::class],
                'bat'  => 'z',
            ],
        ]);

        self::assertSame('foo/A/B.z', $filter->__invoke([
            'bar' => 'a',
            'baz' => 'b',
        ]));
    }

    /** @return array<string, array{0: string|FilterInterface|callable(mixed):mixed, 1: string, 2: string}> */
    public static function filterTypesProvider(): array
    {
        return [
            'Closure'         => [
                static fn (string $input): string => strtoupper($input),
                'foo',
                'FOO',
            ],
            'Filter FQCN'     => [
                StringToUpper::class,
                'foo',
                'FOO',
            ],
            'Filter Instance' => [
                new StringToUpper(),
                'foo',
                'FOO',
            ],
            'Filter Alias'    => [
                'stringtoupper',
                'foo',
                'FOO',
            ],
        ];
    }

    /** @param string|FilterInterface|callable(mixed):mixed $ruleFilter */
    #[DataProvider('filterTypesProvider')]
    public function testFilterRuleExecutesExpectedFilter(mixed $ruleFilter, string $input, string $expect): void
    {
        $filter = self::withOptions([
            'target' => ':target',
            'rules'  => [
                ':target' => [$ruleFilter],
            ],
        ]);

        self::assertSame($expect, $filter->filter(['target' => $input]));
    }

    public function testStaticRulesBehaveLikeStringReplace(): void
    {
        $filter = self::withOptions([
            'target' => '/:c/:b/:a',
            'rules'  => [
                'a' => 'A',
                'b' => 'B',
                'c' => 'C',
            ],
        ]);

        self::assertSame('/C/B/A', $filter->filter(['foo' => 'bar']));
    }

    public function testStaticRuleReplacementsCanBeOverriddenInFilterValue(): void
    {
        $filter = self::withOptions([
            'target' => '/:c/:b/:a',
            'rules'  => [
                'a' => 'A',
                'b' => 'B',
                'c' => 'C',
            ],
        ]);

        self::assertSame('/z/y/x', $filter->filter([
            'a' => 'x',
            'b' => 'y',
            'c' => 'z',
        ]));
    }

    public function testFilterTransformsStringAccordingToRules(): void
    {
        $filter = self::withOptions([
            'target' => ':controller/:action.:suffix',
            'rules'  => [
                ':controller' => [CamelCaseToDash::class],
                ':action'     => [CamelCaseToDash::class],
                'suffix'      => 'phtml',
            ],
        ]);

        $filtered = $filter([
            'controller' => 'FooBar',
            'action'     => 'bazBat',
        ]);
        self::assertSame('Foo-Bar/baz-Bat.phtml', $filtered);
    }

    public function testInputWithNonStringKeysIsIgnored(): void
    {
        $filter = self::withOptions([
            'target' => ':controller/:action.:suffix',
            'rules'  => [
                ':controller' => [CamelCaseToDash::class],
                ':action'     => [CamelCaseToDash::class],
                'suffix'      => 'phtml',
            ],
        ]);

        $filtered = $filter([
            'controller' => 'FooBar',
            0            => 'bing',
            'action'     => 99,
        ]);
        self::assertSame('Foo-Bar/99.phtml', $filtered);
    }

    public function testTargetReplacementIdentifierWorksWhenInflected(): void
    {
        $filter = self::withOptions([
            'target'                      => '?=##controller/?=##action.?=##suffix',
            'rules'                       => [
                ':controller' => [CamelCaseToDash::class],
                ':action'     => [CamelCaseToDash::class],
                'suffix'      => 'phtml',
            ],
            'targetReplacementIdentifier' => '?=##',
        ]);

        $filtered = $filter->__invoke([
            'controller' => 'FooBar',
            'action'     => 'bazBat',
        ]);

        self::assertSame('Foo-Bar/baz-Bat.phtml', $filtered);
    }

    public function testTargetExceptionThrownWhenTargetSourceNotSatisfied(): void
    {
        $filter = self::withOptions([
            'target'                      => '?=##controller/?=##action.?=##suffix',
            'rules'                       => [
                ':controller' => [CamelCaseToDash::class],
                ':action'     => [CamelCaseToDash::class],
                'suffix'      => 'phtml',
            ],
            'targetReplacementIdentifier' => '?=##',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('perhaps a rule was not satisfied');
        $filter->filter(['controller' => 'FooBar']);
    }

    public function testTargetExceptionsCanBeDisabled(): void
    {
        $filter = self::withOptions([
            'target'                  => ':controller/:action.:suffix',
            'rules'                   => [
                ':controller' => [CamelCaseToDash::class],
                ':action'     => [CamelCaseToDash::class],
                'suffix'      => 'phtml',
            ],
            'throwTargetExceptionsOn' => false,
        ]);

        self::assertSame(
            'Foo-Bar/:action.phtml',
            $filter->filter(['controller' => 'FooBar']),
        );
    }

    public function testTargetExceptionNotThrownOnIdentifierNotFollowedByCharacter(): void
    {
        $filter = self::withOptions([
            'target'                  => 'e:\path\to\:controller\:action.:suffix',
            'rules'                   => [
                ':controller' => [CamelCaseToDash::class, StringToLower::class],
                ':action'     => [CamelCaseToDash::class],
                'suffix'      => 'phtml',
            ],
            'throwTargetExceptionsOn' => true,
        ]);

        self::assertSame(
            'e:\path\to\foo-bar\Moo-Too.phtml',
            $filter->filter(['controller' => 'FooBar', 'action' => 'MooToo']),
        );
    }

    /**
     * Added str_replace('\\', '\\\\', ..) to all processedParts values to disable backreferences
     *
     * @issue Laminas-2538 Laminas_Filter_Inflector::filter() fails with all numeric folder on Windows
     */
    public function testCheckInflectorWithPregBackreferenceLikeParts(): void
    {
        $filter = self::withOptions([
            'target' => ':moduleDir' . DIRECTORY_SEPARATOR . ':controller' . DIRECTORY_SEPARATOR . ':action.:suffix',
            'rules'  => [
                'moduleDir'   => 'C:\htdocs\public\cache\00\01\42\app\modules',
                ':controller' => [CamelCaseToDash::class, StringToLower::class],
                ':action'     => [CamelCaseToDash::class],
                'suffix'      => 'phtml',
            ],
        ]);

        self::assertSame(
            'C:\htdocs\public\cache\00\01\42\app\modules'
            . DIRECTORY_SEPARATOR
            . 'foo-bar'
            . DIRECTORY_SEPARATOR
            . 'Moo-Too.phtml',
            $filter->filter([
                'controller' => 'FooBar',
                'action'     => 'MooToo',
            ]),
        );
    }

    /**
     * @issue Laminas-2964
     */
    public function testNoInflectableTarget(): void
    {
        $inflector = self::withOptions([
            'target' => 'abc',
            'rules'  => [':foo' => []],
        ]);

        self::assertSame($inflector(['any' => 'thing']), 'abc');
    }

    /** @return list<array{0: mixed}> */
    public static function unFilterableInput(): array
    {
        return [
            ['Foo'],
            [1],
            [1.23],
            [true],
            [null],
        ];
    }

    #[DataProvider('unFilterableInput')]
    public function testOnlyArraysCanBeFiltered(mixed $input): void
    {
        $filter = self::withOptions([
            'target' => 'abc',
        ]);

        self::assertSame($input, $filter->filter($input));
    }

    public function testObjectPropertiesAreExtractedAsFilterSubject(): void
    {
        $filter = self::withOptions([
            'target' => '/:controller/:action.:suffix',
            'rules'  => [
                ':controller' => [CamelCaseToDash::class, StringToLower::class],
                ':action'     => [CamelCaseToDash::class, StringToLower::class],
                'suffix'      => 'phtml',
            ],
        ]);

        $value = new class () {
            public string $controller = 'MyController';
            public string $action     = 'SomeAction';
            public string $suffix     = 'php';
        };

        self::assertSame(
            '/my-controller/some-action.php',
            $filter->filter($value),
        );
    }

    public function testThatYouCannotUseAColonInTheTargetByDefault(): void
    {
        $filter = self::withOptions([
            'target' => '::something',
            'rules'  => [
                'something' => 'foo',
            ],
        ]);

        $this->expectException(RuntimeException::class);
        $filter->filter([]);
    }

    public function testThatYouCanUseAColonInTheTargetWhenTheDelimiterIsSet(): void
    {
        $filter = self::withOptions([
            'target'                      => ':?something',
            'targetReplacementIdentifier' => '?',
            'rules'                       => [
                'something' => 'foo',
            ],
        ]);

        self::assertSame(
            ':foo',
            $filter->filter([]),
        );
    }
}

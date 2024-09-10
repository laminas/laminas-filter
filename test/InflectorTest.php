<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use ArrayObject;
use Laminas\Filter\Exception;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\FilterPluginManager;
use Laminas\Filter\Inflector as InflectorFilter;
use Laminas\Filter\StringToLower;
use Laminas\Filter\StringToUpper;
use Laminas\Filter\Word\CamelCaseToDash;
use Laminas\Filter\Word\CamelCaseToUnderscore;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function array_values;
use function count;

use const DIRECTORY_SEPARATOR;

class InflectorTest extends TestCase
{
    private InflectorFilter $inflector;

    public function setUp(): void
    {
        $this->inflector = new InflectorFilter();
    }

    public function testGetPluginManagerReturnsFilterManagerByDefault(): void
    {
        $broker = $this->inflector->getPluginManager();
        self::assertInstanceOf(FilterPluginManager::class, $broker);
    }

    public function testSetPluginManagerAllowsSettingAlternatePluginManager(): void
    {
        $defaultManager = $this->inflector->getPluginManager();
        $manager        = new FilterPluginManager(new ServiceManager());
        $this->inflector->setPluginManager($manager);
        $receivedManager = $this->inflector->getPluginManager();
        self::assertNotSame($defaultManager, $receivedManager);
        self::assertSame($manager, $receivedManager);
    }

    public function testTargetAccessorsWork(): void
    {
        $this->inflector->setTarget('foo/:bar/:baz');
        self::assertSame('foo/:bar/:baz', $this->inflector->getTarget());
    }

    public function testTargetInitiallyNull(): void
    {
        self::assertNull($this->inflector->getTarget());
    }

    public function testPassingTargetToConstructorSetsTarget(): void
    {
        $inflector = new InflectorFilter('foo/:bar/:baz');
        self::assertSame('foo/:bar/:baz', $inflector->getTarget());
    }

    public function testSetTargetByReferenceWorks(): void
    {
        $target = 'foo/:bar/:baz';
        $this->inflector->setTargetReference($target);
        self::assertSame('foo/:bar/:baz', $this->inflector->getTarget());
        /* this variable is used by-ref through `setTargetReference` above */
        $target .= '/:bat';
        self::assertSame('foo/:bar/:baz/:bat', $this->inflector->getTarget());
    }

    public function testSetFilterRuleWithStringRuleCreatesRuleEntryAndFilterObject(): void
    {
        $rules = $this->inflector->getRules();
        self::assertIsArray($rules);
        self::assertSame(0, count($rules));
        $this->inflector->setFilterRule('controller', StringToLower::class);
        $rules = $this->inflector->getRules('controller');
        self::assertIsArray($rules);
        self::assertSame(1, count($rules));
        $filter = $rules[0];
        self::assertInstanceOf(FilterInterface::class, $filter);
    }

    public function testSetFilterRuleWithFilterObjectCreatesRuleEntryWithFilterObject(): void
    {
        $rules = $this->inflector->getRules();
        self::assertIsArray($rules);
        self::assertSame(0, count($rules));
        $filter = new StringToLower();
        $this->inflector->setFilterRule('controller', $filter);
        $rules = $this->inflector->getRules('controller');
        self::assertIsArray($rules);
        self::assertSame(1, count($rules));
        $received = $rules[0];
        self::assertInstanceOf(FilterInterface::class, $received);
        self::assertSame($filter, $received);
    }

    public function testAddFilterRuleAppendsRuleEntries(): void
    {
        $rules = $this->inflector->getRules();
        self::assertIsArray($rules);
        self::assertSame(0, count($rules));
        $this->inflector->setFilterRule('controller', [StringToLower::class, TestAsset\Alpha::class]);
        $rules = $this->inflector->getRules('controller');
        self::assertIsArray($rules);
        self::assertSame(2, count($rules));
        self::assertInstanceOf(FilterInterface::class, $rules[0]);
        self::assertInstanceOf(FilterInterface::class, $rules[1]);
    }

    public function testSetStaticRuleCreatesScalarRuleEntry(): void
    {
        $rules = $this->inflector->getRules();
        self::assertIsArray($rules);
        self::assertSame(0, count($rules));
        $this->inflector->setStaticRule('controller', 'foobar');
        $rules = $this->inflector->getRules('controller');
        /** @psalm-suppress DocblockTypeContradiction */
        self::assertSame('foobar', $rules);
    }

    public function testSetStaticRuleMultipleTimesOverwritesEntry(): void
    {
        $rules = $this->inflector->getRules();
        self::assertIsArray($rules);
        self::assertSame(0, count($rules));
        $this->inflector->setStaticRule('controller', 'foobar');
        $rules = $this->inflector->getRules('controller');
        /** @psalm-suppress DocblockTypeContradiction */
        self::assertSame('foobar', $rules);
        $this->inflector->setStaticRule('controller', 'bazbat');
        $rules = $this->inflector->getRules('controller');
        /** @psalm-suppress DocblockTypeContradiction */
        self::assertSame('bazbat', $rules);
    }

    public function testSetStaticRuleReferenceAllowsUpdatingRuleByReference(): void
    {
        $rule  = 'foobar';
        $rules = $this->inflector->getRules();
        self::assertIsArray($rules);
        self::assertSame(0, count($rules));
        $this->inflector->setStaticRuleReference('controller', $rule);
        $rules = $this->inflector->getRules('controller');
        /** @psalm-suppress DocblockTypeContradiction */
        self::assertSame('foobar', $rules);
        $rule .= '/baz';
        $rules = $this->inflector->getRules('controller');
        /** @psalm-suppress DocblockTypeContradiction */
        self::assertSame('foobar/baz', $rules);
    }

    public function testAddRulesCreatesAppropriateRuleEntries(): void
    {
        $rules = $this->inflector->getRules();
        self::assertIsArray($rules);
        self::assertSame(0, count($rules));
        $this->inflector->addRules([
            ':controller' => [StringToLower::class, TestAsset\Alpha::class],
            'suffix'      => 'phtml',
        ]);
        $rules = $this->inflector->getRules();
        self::assertIsArray($rules);
        self::assertSame(2, count($rules));
        self::assertSame(2, count($rules['controller']));
        self::assertSame('phtml', $rules['suffix']);
    }

    public function testSetRulesCreatesAppropriateRuleEntries(): void
    {
        $this->inflector->setStaticRule('some-rules', 'some-value');
        $rules = $this->inflector->getRules();
        self::assertIsArray($rules);
        self::assertSame(1, count($rules));
        $this->inflector->setRules([
            ':controller' => [StringToLower::class, TestAsset\Alpha::class],
            'suffix'      => 'phtml',
        ]);
        $rules = $this->inflector->getRules();
        self::assertIsArray($rules);
        self::assertSame(2, count($rules));
        self::assertSame(2, count($rules['controller']));
        self::assertSame('phtml', $rules['suffix']);
    }

    public function testGetRule(): void
    {
        $this->inflector->setFilterRule(':controller', [TestAsset\Alpha::class, StringToLower::class]);
        self::assertInstanceOf(StringToLower::class, $this->inflector->getRule('controller', 1));
        self::assertFalse($this->inflector->getRule('controller', 2));
    }

    public function testFilterTransformsStringAccordingToRules(): void
    {
        $this->inflector
            ->setTarget(':controller/:action.:suffix')
            ->addRules([
                ':controller' => [CamelCaseToDash::class],
                ':action'     => [CamelCaseToDash::class],
                'suffix'      => 'phtml',
            ]);

        $filter   = $this->inflector;
        $filtered = $filter([
            'controller' => 'FooBar',
            'action'     => 'bazBat',
        ]);
        self::assertSame('Foo-Bar/baz-Bat.phtml', $filtered);
    }

    public function testTargetReplacementIdentifierAccessorsWork(): void
    {
        self::assertSame(':', $this->inflector->getTargetReplacementIdentifier());
        $this->inflector->setTargetReplacementIdentifier('?=');
        self::assertSame('?=', $this->inflector->getTargetReplacementIdentifier());
    }

    public function testTargetReplacementIdentifierWorksWhenInflected(): void
    {
        $inflector = new InflectorFilter(
            '?=##controller/?=##action.?=##suffix',
            [
                ':controller' => [CamelCaseToDash::class],
                ':action'     => [CamelCaseToDash::class],
                'suffix'      => 'phtml',
            ],
            null,
            '?=##'
        );

        $filtered = $inflector([
            'controller' => 'FooBar',
            'action'     => 'bazBat',
        ]);

        self::assertSame('Foo-Bar/baz-Bat.phtml', $filtered);
    }

    public function testThrowTargetExceptionsAccessorsWork(): void
    {
        self::assertSame(':', $this->inflector->getTargetReplacementIdentifier());
        $this->inflector->setTargetReplacementIdentifier('?=');
        self::assertSame('?=', $this->inflector->getTargetReplacementIdentifier());
    }

    public function testThrowTargetExceptionsOnAccessorsWork(): void
    {
        self::assertTrue($this->inflector->isThrowTargetExceptionsOn());
        $this->inflector->setThrowTargetExceptionsOn(false);
        self::assertFalse($this->inflector->isThrowTargetExceptionsOn());
    }

    public function testTargetExceptionThrownWhenTargetSourceNotSatisfied(): void
    {
        $inflector = new InflectorFilter(
            '?=##controller/?=##action.?=##suffix',
            [
                ':controller' => [CamelCaseToDash::class],
                ':action'     => [CamelCaseToDash::class],
                'suffix'      => 'phtml',
            ],
            true,
            '?=##'
        );

        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('perhaps a rule was not satisfied');
        $filtered = $inflector(['controller' => 'FooBar']);
    }

    public function testTargetExceptionNotThrownOnIdentifierNotFollowedByCharacter(): void
    {
        $inflector = new InflectorFilter(
            'e:\path\to\:controller\:action.:suffix',
            [
                ':controller' => [CamelCaseToDash::class, StringToLower::class],
                ':action'     => [CamelCaseToDash::class],
                'suffix'      => 'phtml',
            ],
            true,
            ':'
        );

        $filtered = $inflector(['controller' => 'FooBar', 'action' => 'MooToo']);
        self::assertSame($filtered, 'e:\path\to\foo-bar\Moo-Too.phtml');
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return [
            'target'                      => '$controller/$action.$suffix',
            'throwTargetExceptionsOn'     => true,
            'targetReplacementIdentifier' => '$',
            'rules'                       => [
                ':controller' => [
                    'rule1' => CamelCaseToUnderscore::class,
                    'rule2' => StringToLower::class,
                ],
                ':action'     => [
                    'rule1' => CamelCaseToDash::class,
                    'rule2' => StringToUpper::class,
                ],
                'suffix'      => 'php',
            ],
        ];
    }

    /**
     * This method returns an ArrayObject instance in place of a
     * Laminas\Config\Config instance; the two are interchangeable, as inflectors
     * consume the more general array or Traversable types.
     */
    public function getConfig(): ArrayObject
    {
        $options = $this->getOptions();

        return new ArrayObject($options);
    }

    // @codingStandardsIgnoreStart
    protected function _testOptions($inflector)
    {
        // @codingStandardsIgnoreEnd
        $options = $this->getOptions();
        $broker  = $inflector->getPluginManager();
        self::assertSame($options['target'], $inflector->getTarget());

        self::assertInstanceOf(FilterPluginManager::class, $broker);
        self::assertTrue($inflector->isThrowTargetExceptionsOn());
        self::assertSame($options['targetReplacementIdentifier'], $inflector->getTargetReplacementIdentifier());

        $rules = $inflector->getRules();
        /** @psalm-suppress MixedArrayAccess */
        foreach (array_values($options['rules'][':controller']) as $key => $rule) {
            $class = $rules['controller'][$key]::class;
            self::assertStringContainsString($rule, $class);
        }
        /** @psalm-suppress MixedArrayAccess */
        foreach (array_values($options['rules'][':action']) as $key => $rule) {
            $class = $rules['action'][$key]::class;
            self::assertStringContainsString($rule, $class);
        }
        /** @psalm-suppress MixedArrayAccess */
        self::assertSame($options['rules']['suffix'], $rules['suffix']);
    }

    public function testSetConfigSetsStateAndRules(): void
    {
        $config    = $this->getConfig();
        $inflector = new InflectorFilter();
        $inflector->setOptions($config);
        $this->_testOptions($inflector);
    }

    /**
     * Added str_replace('\\', '\\\\', ..) to all processedParts values to disable backreferences
     *
     * @issue Laminas-2538 Laminas_Filter_Inflector::filter() fails with all numeric folder on Windows
     */
    public function testCheckInflectorWithPregBackreferenceLikeParts(): void
    {
        $inflector = new InflectorFilter(
            ':moduleDir' . DIRECTORY_SEPARATOR . ':controller' . DIRECTORY_SEPARATOR . ':action.:suffix',
            [
                ':controller' => [CamelCaseToDash::class, StringToLower::class],
                ':action'     => [CamelCaseToDash::class],
                'suffix'      => 'phtml',
            ],
            true,
            ':'
        );

        $inflector->setStaticRule('moduleDir', 'C:\htdocs\public\cache\00\01\42\app\modules');

        $filtered = $inflector([
            'controller' => 'FooBar',
            'action'     => 'MooToo',
        ]);
        self::assertSame(
            $filtered,
            'C:\htdocs\public\cache\00\01\42\app\modules'
            . DIRECTORY_SEPARATOR
            . 'foo-bar'
            . DIRECTORY_SEPARATOR
            . 'Moo-Too.phtml'
        );
    }

    /**
     * @issue Laminas-2522
     */
    public function testTestForFalseInConstructorParams(): void
    {
        $inflector = new InflectorFilter('something', [], false, false);
        self::assertFalse($inflector->isThrowTargetExceptionsOn());
        self::assertSame($inflector->getTargetReplacementIdentifier(), ':');

        new InflectorFilter('something', [], false, '#');
    }

    /**
     * @issue Laminas-2964
     */
    public function testNoInflectableTarget(): void
    {
        $inflector = new InflectorFilter('abc');
        $inflector->addRules([':foo' => []]);
        self::assertSame($inflector(['fo' => 'bar']), 'abc');
    }

    /**
     * @issue Laminas-7544
     */
    public function testAddFilterRuleMultipleTimes(): void
    {
        $rules = $this->inflector->getRules();
        self::assertSame(0, count($rules));
        $this->inflector->setFilterRule('controller', StringToLower::class);
        $rules = $this->inflector->getRules('controller');
        self::assertSame(1, count($rules));
        $this->inflector->addFilterRule('controller', [TestAsset\Alpha::class, StringToLower::class]);
        $rules = $this->inflector->getRules('controller');
        /** @psalm-suppress PossiblyFalseArgument */
        self::assertSame(3, count($rules));
        $context = StringToLower::class;
        $this->inflector->setStaticRuleReference('context', $context);
        $this->inflector->addFilterRule('controller', [TestAsset\Alpha::class, StringToLower::class]);
        $rules = $this->inflector->getRules('controller');
        /** @psalm-suppress PossiblyFalseArgument */
        self::assertSame(5, count($rules));
    }

    #[Group('Laminas-8997')]
    public function testPassingArrayToConstructorSetsStateAndRules(): void
    {
        $options   = $this->getOptions();
        $inflector = new InflectorFilter($options);
        $this->_testOptions($inflector);
    }

    #[Group('Laminas-8997')]
    public function testPassingArrayToSetConfigSetsStateAndRules(): void
    {
        $options   = $this->getOptions();
        $inflector = new InflectorFilter();
        $inflector->setOptions($options);
        $this->_testOptions($inflector);
    }

    #[Group('Laminas-8997')]
    public function testPassingConfigObjectToConstructorSetsStateAndRules(): void
    {
        $config    = $this->getConfig();
        $inflector = new InflectorFilter($config);
        $this->_testOptions($inflector);
    }

    #[Group('Laminas-8997')]
    public function testPassingConfigObjectToSetConfigSetsStateAndRules(): void
    {
        $config    = $this->getConfig();
        $inflector = new InflectorFilter();
        $inflector->setOptions($config);
        $this->_testOptions($inflector);
    }
}

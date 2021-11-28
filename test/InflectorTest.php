<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use ArrayObject;
use Laminas\Filter\Exception;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\FilterPluginManager;
use Laminas\Filter\Inflector as InflectorFilter;
use Laminas\Filter\PregReplace;
use Laminas\Filter\StringToLower;
use Laminas\Filter\StringToUpper;
use Laminas\Filter\Word\CamelCaseToDash;
use Laminas\Filter\Word\CamelCaseToUnderscore;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use Traversable;

use function array_values;
use function count;
use function get_class;

use const DIRECTORY_SEPARATOR;

class InflectorTest extends TestCase
{
    /** @var InflectorFilter */
    protected $inflector;

    /** @var FilterPluginManager */
    protected $broker;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp(): void
    {
        $this->inflector = new InflectorFilter();
        $this->broker    = $this->inflector->getPluginManager();
    }

    public function testGetPluginManagerReturnsFilterManagerByDefault()
    {
        $broker = $this->inflector->getPluginManager();
        $this->assertInstanceOf(FilterPluginManager::class, $broker);
    }

    public function testSetPluginManagerAllowsSettingAlternatePluginManager()
    {
        $defaultManager = $this->inflector->getPluginManager();
        $manager        = new FilterPluginManager(new ServiceManager());
        $this->inflector->setPluginManager($manager);
        $receivedManager = $this->inflector->getPluginManager();
        $this->assertNotSame($defaultManager, $receivedManager);
        $this->assertSame($manager, $receivedManager);
    }

    public function testTargetAccessorsWork()
    {
        $this->inflector->setTarget('foo/:bar/:baz');
        $this->assertEquals('foo/:bar/:baz', $this->inflector->getTarget());
    }

    public function testTargetInitiallyNull()
    {
        $this->assertNull($this->inflector->getTarget());
    }

    public function testPassingTargetToConstructorSetsTarget()
    {
        $inflector = new InflectorFilter('foo/:bar/:baz');
        $this->assertEquals('foo/:bar/:baz', $inflector->getTarget());
    }

    public function testSetTargetByReferenceWorks()
    {
        $target = 'foo/:bar/:baz';
        $this->inflector->setTargetReference($target);
        $this->assertEquals('foo/:bar/:baz', $this->inflector->getTarget());
        $target .= '/:bat';
        $this->assertEquals('foo/:bar/:baz/:bat', $this->inflector->getTarget());
    }

    public function testSetFilterRuleWithStringRuleCreatesRuleEntryAndFilterObject()
    {
        $rules = $this->inflector->getRules();
        $this->assertEquals(0, count($rules));
        $this->inflector->setFilterRule('controller', PregReplace::class);
        $rules = $this->inflector->getRules('controller');
        $this->assertEquals(1, count($rules));
        $filter = $rules[0];
        $this->assertInstanceOf(FilterInterface::class, $filter);
    }

    public function testSetFilterRuleWithFilterObjectCreatesRuleEntryWithFilterObject()
    {
        $rules = $this->inflector->getRules();
        $this->assertEquals(0, count($rules));
        $filter = new PregReplace();
        $this->inflector->setFilterRule('controller', $filter);
        $rules = $this->inflector->getRules('controller');
        $this->assertEquals(1, count($rules));
        $received = $rules[0];
        $this->assertInstanceOf(FilterInterface::class, $received);
        $this->assertSame($filter, $received);
    }

    public function testAddFilterRuleAppendsRuleEntries()
    {
        $rules = $this->inflector->getRules();
        $this->assertEquals(0, count($rules));
        $this->inflector->setFilterRule('controller', [PregReplace::class, TestAsset\Alpha::class]);
        $rules = $this->inflector->getRules('controller');
        $this->assertEquals(2, count($rules));
        $this->assertInstanceOf(FilterInterface::class, $rules[0]);
        $this->assertInstanceOf(FilterInterface::class, $rules[1]);
    }

    public function testSetStaticRuleCreatesScalarRuleEntry()
    {
        $rules = $this->inflector->getRules();
        $this->assertEquals(0, count($rules));
        $this->inflector->setStaticRule('controller', 'foobar');
        $rules = $this->inflector->getRules('controller');
        $this->assertEquals('foobar', $rules);
    }

    public function testSetStaticRuleMultipleTimesOverwritesEntry()
    {
        $rules = $this->inflector->getRules();
        $this->assertEquals(0, count($rules));
        $this->inflector->setStaticRule('controller', 'foobar');
        $rules = $this->inflector->getRules('controller');
        $this->assertEquals('foobar', $rules);
        $this->inflector->setStaticRule('controller', 'bazbat');
        $rules = $this->inflector->getRules('controller');
        $this->assertEquals('bazbat', $rules);
    }

    public function testSetStaticRuleReferenceAllowsUpdatingRuleByReference()
    {
        $rule  = 'foobar';
        $rules = $this->inflector->getRules();
        $this->assertEquals(0, count($rules));
        $this->inflector->setStaticRuleReference('controller', $rule);
        $rules = $this->inflector->getRules('controller');
        $this->assertEquals('foobar', $rules);
        $rule .= '/baz';
        $rules = $this->inflector->getRules('controller');
        $this->assertEquals('foobar/baz', $rules);
    }

    public function testAddRulesCreatesAppropriateRuleEntries()
    {
        $rules = $this->inflector->getRules();
        $this->assertEquals(0, count($rules));
        $this->inflector->addRules([
            ':controller' => [PregReplace::class, TestAsset\Alpha::class],
            'suffix'      => 'phtml',
        ]);
        $rules = $this->inflector->getRules();
        $this->assertEquals(2, count($rules));
        $this->assertEquals(2, count($rules['controller']));
        $this->assertEquals('phtml', $rules['suffix']);
    }

    public function testSetRulesCreatesAppropriateRuleEntries()
    {
        $this->inflector->setStaticRule('some-rules', 'some-value');
        $rules = $this->inflector->getRules();
        $this->assertEquals(1, count($rules));
        $this->inflector->setRules([
            ':controller' => [PregReplace::class, TestAsset\Alpha::class],
            'suffix'      => 'phtml',
        ]);
        $rules = $this->inflector->getRules();
        $this->assertEquals(2, count($rules));
        $this->assertEquals(2, count($rules['controller']));
        $this->assertEquals('phtml', $rules['suffix']);
    }

    public function testGetRule()
    {
        $this->inflector->setFilterRule(':controller', [TestAsset\Alpha::class, StringToLower::class]);
        $this->assertInstanceOf(StringToLower::class, $this->inflector->getRule('controller', 1));
        $this->assertFalse($this->inflector->getRule('controller', 2));
    }

    public function testFilterTransformsStringAccordingToRules()
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
        $this->assertEquals('Foo-Bar/baz-Bat.phtml', $filtered);
    }

    public function testTargetReplacementIdentiferAccessorsWork()
    {
        $this->assertEquals(':', $this->inflector->getTargetReplacementIdentifier());
        $this->inflector->setTargetReplacementIdentifier('?=');
        $this->assertEquals('?=', $this->inflector->getTargetReplacementIdentifier());
    }

    public function testTargetReplacementIdentiferWorksWhenInflected()
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

        $this->assertEquals('Foo-Bar/baz-Bat.phtml', $filtered);
    }

    public function testThrowTargetExceptionsAccessorsWork()
    {
        $this->assertEquals(':', $this->inflector->getTargetReplacementIdentifier());
        $this->inflector->setTargetReplacementIdentifier('?=');
        $this->assertEquals('?=', $this->inflector->getTargetReplacementIdentifier());
    }

    public function testThrowTargetExceptionsOnAccessorsWork()
    {
        $this->assertTrue($this->inflector->isThrowTargetExceptionsOn());
        $this->inflector->setThrowTargetExceptionsOn(false);
        $this->assertFalse($this->inflector->isThrowTargetExceptionsOn());
    }

    public function testTargetExceptionThrownWhenTargetSourceNotSatisfied()
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

    public function testTargetExceptionNotThrownOnIdentifierNotFollowedByCharacter()
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
        $this->assertEquals($filtered, 'e:\path\to\foo-bar\Moo-Too.phtml');
    }

    public function getOptions()
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
     *
     * @return Traversable
     */
    public function getConfig()
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
        $this->assertEquals($options['target'], $inflector->getTarget());

        $this->assertInstanceOf(FilterPluginManager::class, $broker);
        $this->assertTrue($inflector->isThrowTargetExceptionsOn());
        $this->assertEquals($options['targetReplacementIdentifier'], $inflector->getTargetReplacementIdentifier());

        $rules = $inflector->getRules();
        foreach (array_values($options['rules'][':controller']) as $key => $rule) {
            $class = get_class($rules['controller'][$key]);
            $this->assertStringContainsString($rule, $class);
        }
        foreach (array_values($options['rules'][':action']) as $key => $rule) {
            $class = get_class($rules['action'][$key]);
            $this->assertStringContainsString($rule, $class);
        }
        $this->assertEquals($options['rules']['suffix'], $rules['suffix']);
    }

    public function testSetConfigSetsStateAndRules()
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
    public function testCheckInflectorWithPregBackreferenceLikeParts()
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
        $this->assertEquals(
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
    public function testTestForFalseInConstructorParams()
    {
        $inflector = new InflectorFilter('something', [], false, false);
        $this->assertFalse($inflector->isThrowTargetExceptionsOn());
        $this->assertEquals($inflector->getTargetReplacementIdentifier(), ':');

        $inflector = new InflectorFilter('something', [], false, '#');
    }

    /**
     * @issue Laminas-2964
     */
    public function testNoInflectableTarget()
    {
        $inflector = new InflectorFilter('abc');
        $inflector->addRules([':foo' => []]);
        $this->assertEquals($inflector(['fo' => 'bar']), 'abc');
    }

    /**
     * @issue Laminas-7544
     */
    public function testAddFilterRuleMultipleTimes()
    {
        $rules = $this->inflector->getRules();
        $this->assertEquals(0, count($rules));
        $this->inflector->setFilterRule('controller', PregReplace::class);
        $rules = $this->inflector->getRules('controller');
        $this->assertEquals(1, count($rules));
        $this->inflector->addFilterRule('controller', [TestAsset\Alpha::class, StringToLower::class]);
        $rules = $this->inflector->getRules('controller');
        $this->assertEquals(3, count($rules));
        $this->_context = StringToLower::class;
        $this->inflector->setStaticRuleReference('context', $this->_context);
        $this->inflector->addFilterRule('controller', [TestAsset\Alpha::class, StringToLower::class]);
        $rules = $this->inflector->getRules('controller');
        $this->assertEquals(5, count($rules));
    }

    /**
     * @group Laminas-8997
     */
    public function testPassingArrayToConstructorSetsStateAndRules()
    {
        $options   = $this->getOptions();
        $inflector = new InflectorFilter($options);
        $this->_testOptions($inflector);
    }

    /**
     * @group Laminas-8997
     */
    public function testPassingArrayToSetConfigSetsStateAndRules()
    {
        $options   = $this->getOptions();
        $inflector = new InflectorFilter();
        $inflector->setOptions($options);
        $this->_testOptions($inflector);
    }

    /**
     * @group Laminas-8997
     */
    public function testPassingConfigObjectToConstructorSetsStateAndRules()
    {
        $config    = $this->getConfig();
        $inflector = new InflectorFilter($config);
        $this->_testOptions($inflector);
    }

    /**
     * @group Laminas-8997
     */
    public function testPassingConfigObjectToSetConfigSetsStateAndRules()
    {
        $config    = $this->getConfig();
        $inflector = new InflectorFilter();
        $inflector->setOptions($config);
        $this->_testOptions($inflector);
    }
}

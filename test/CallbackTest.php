<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter;

use Laminas\Filter\Callback as CallbackFilter;
use PHPUnit\Framework\TestCase;

class CallbackTest extends TestCase
{
    public function testObjectCallback()
    {
        $filter = new CallbackFilter([$this, 'objectCallback']);
        $this->assertEquals('objectCallback-test', $filter('test'));
    }

    public function testConstructorWithOptions()
    {
        $filter = new CallbackFilter([
            'callback'        => [$this, 'objectCallbackWithParams'],
            'callback_params' => 0,
        ]);

        $this->assertEquals('objectCallbackWithParams-test-0', $filter('test'));
    }

    public function testStaticCallback()
    {
        $filter = new CallbackFilter(
            [__CLASS__, 'staticCallback']
        );
        $this->assertEquals('staticCallback-test', $filter('test'));
    }

    public function testStringClassCallback()
    {
        $filter = new CallbackFilter(self::class);
        $this->assertEquals('stringClassCallback-test', $filter('test'));
    }

    public function testSettingDefaultOptions()
    {
        $filter = new CallbackFilter([$this, 'objectCallback'], 'param');
        $this->assertEquals(['param'], $filter->getCallbackParams());
        $this->assertEquals('objectCallback-test', $filter('test'));
    }

    public function testSettingDefaultOptionsAfterwards()
    {
        $filter = new CallbackFilter([$this, 'objectCallback']);
        $filter->setCallbackParams('param');
        $this->assertEquals(['param'], $filter->getCallbackParams());
        $this->assertEquals('objectCallback-test', $filter('test'));
    }

    public function testCallbackWithStringParameter()
    {
        $filter = new CallbackFilter('strrev');
        $this->assertEquals('!olleH', $filter('Hello!'));
    }

    public function testCallbackWithArrayParameters()
    {
        $filter = new CallbackFilter('strrev');
        $this->assertEquals('!olleH', $filter('Hello!'));
    }

    public function objectCallback($value)
    {
        return 'objectCallback-' . $value;
    }

    public static function staticCallback($value)
    {
        return 'staticCallback-' . $value;
    }

    public function __invoke($value)
    {
        return 'stringClassCallback-' . $value;
    }

    public function objectCallbackWithParams($value, $param = null)
    {
        return 'objectCallbackWithParams-' . $value . '-' . $param;
    }
}

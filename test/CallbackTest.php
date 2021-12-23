<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Callback as CallbackFilter;
use PHPUnit\Framework\TestCase;

class CallbackTest extends TestCase
{
    public function testObjectCallback(): void
    {
        $filter = new CallbackFilter([$this, 'objectCallback']);
        $this->assertSame('objectCallback-test', $filter('test'));
    }

    public function testConstructorWithOptions(): void
    {
        $filter = new CallbackFilter([
            'callback'        => [$this, 'objectCallbackWithParams'],
            'callback_params' => 0,
        ]);

        $this->assertSame('objectCallbackWithParams-test-0', $filter('test'));
    }

    public function testStaticCallback(): void
    {
        $filter = new CallbackFilter(
            [self::class, 'staticCallback']
        );
        $this->assertSame('staticCallback-test', $filter('test'));
    }

    public function testStringClassCallback(): void
    {
        $filter = new CallbackFilter(self::class);
        $this->assertSame('stringClassCallback-test', $filter('test'));
    }

    public function testSettingDefaultOptions(): void
    {
        $filter = new CallbackFilter([$this, 'objectCallback'], 'param');
        $this->assertSame(['param'], $filter->getCallbackParams());
        $this->assertSame('objectCallback-test', $filter('test'));
    }

    public function testSettingDefaultOptionsAfterwards(): void
    {
        $filter = new CallbackFilter([$this, 'objectCallback']);
        $filter->setCallbackParams('param');
        $this->assertSame(['param'], $filter->getCallbackParams());
        $this->assertSame('objectCallback-test', $filter('test'));
    }

    public function testCallbackWithStringParameter(): void
    {
        $filter = new CallbackFilter('strrev');
        $this->assertSame('!olleH', $filter('Hello!'));
    }

    public function testCallbackWithArrayParameters(): void
    {
        $filter = new CallbackFilter('strrev');
        $this->assertSame('!olleH', $filter('Hello!'));
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

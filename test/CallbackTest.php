<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Callback as CallbackFilter;
use LaminasTest\Filter\TestAsset\CallbackClass;
use PHPUnit\Framework\TestCase;

class CallbackTest extends TestCase
{
    public function testObjectCallback(): void
    {
        $filter = new CallbackFilter([new CallbackClass(), 'objectCallback']);
        self::assertSame('objectCallback-test', $filter('test'));
    }

    public function testConstructorWithOptions(): void
    {
        $filter = new CallbackFilter([
            'callback'        => [new CallbackClass(), 'objectCallbackWithParams'],
            'callback_params' => 0,
        ]);

        self::assertSame('objectCallbackWithParams-test-0', $filter('test'));
    }

    public function testStaticCallback(): void
    {
        $filter = new CallbackFilter(
            [CallbackClass::class, 'staticCallback']
        );
        self::assertSame('staticCallback-test', $filter('test'));
    }

    public function testStringClassCallback(): void
    {
        $filter = new CallbackFilter(CallbackClass::class);
        self::assertSame('stringClassCallback-test', $filter('test'));
    }

    public function testSettingDefaultOptions(): void
    {
        $filter = new CallbackFilter([new CallbackClass(), 'objectCallback'], 'param');
        self::assertSame(['param'], $filter->getCallbackParams());
        self::assertSame('objectCallback-test', $filter('test'));
    }

    public function testSettingDefaultOptionsAfterwards(): void
    {
        $filter = new CallbackFilter([new CallbackClass(), 'objectCallback']);
        $filter->setCallbackParams('param');
        self::assertSame(['param'], $filter->getCallbackParams());
        self::assertSame('objectCallback-test', $filter('test'));
    }

    public function testCallbackWithStringParameter(): void
    {
        $filter = new CallbackFilter('strrev');
        self::assertSame('!olleH', $filter('Hello!'));
    }

    public function testCallbackWithArrayParameters(): void
    {
        $filter = new CallbackFilter('strrev');
        self::assertSame('!olleH', $filter('Hello!'));
    }
}

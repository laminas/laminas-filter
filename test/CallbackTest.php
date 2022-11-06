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
        self::assertSame('objectCallback-test', $filter('test'));
    }

    public function testConstructorWithOptions(): void
    {
        $filter = new CallbackFilter([
            'callback'        => [$this, 'objectCallbackWithParams'],
            'callback_params' => 0,
        ]);

        self::assertSame('objectCallbackWithParams-test-0', $filter('test'));
    }

    public function testStaticCallback(): void
    {
        $filter = new CallbackFilter(
            [self::class, 'staticCallback']
        );
        self::assertSame('staticCallback-test', $filter('test'));
    }

    public function testStringClassCallback(): void
    {
        $filter = new CallbackFilter(self::class);
        self::assertSame('stringClassCallback-test', $filter('test'));
    }

    public function testSettingDefaultOptions(): void
    {
        $filter = new CallbackFilter([$this, 'objectCallback'], 'param');
        self::assertSame(['param'], $filter->getCallbackParams());
        self::assertSame('objectCallback-test', $filter('test'));
    }

    public function testSettingDefaultOptionsAfterwards(): void
    {
        $filter = new CallbackFilter([$this, 'objectCallback']);
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

    public function objectCallback(string $value): string
    {
        return 'objectCallback-' . $value;
    }

    public static function staticCallback(string $value): string
    {
        return 'staticCallback-' . $value;
    }

    public function __invoke(string $value): string
    {
        return 'stringClassCallback-' . $value;
    }

    public function objectCallbackWithParams(string $value, int|string|null $param = null): string
    {
        return 'objectCallbackWithParams-' . $value . '-' . (string) $param;
    }
}

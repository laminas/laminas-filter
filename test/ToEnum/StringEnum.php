<?php

declare(strict_types=1);

namespace LaminasTest\Filter\ToEnum;

enum StringEnum: string
{
    case Foo = 'foo';
    case Bar = 'bar';
}

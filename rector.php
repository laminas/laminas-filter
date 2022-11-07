<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ArrowFunction\StaticArrowFunctionRector;
use Rector\CodingStyle\Rector\Closure\StaticClosureRector;
use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector;
use Rector\Php70\Rector\FuncCall\RandomFunctionRector;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\TypeDeclaration\Rector\ArrowFunction\AddArrowFunctionReturnTypeRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_80,
    ]);

    $rectorConfig->rules([
        StaticArrowFunctionRector::class,
        StaticClosureRector::class,
        AddClosureReturnTypeRector::class,
        PrivatizeFinalClassPropertyRector::class,
        AddArrowFunctionReturnTypeRector::class,
    ]);

    $rectorConfig->parallel();
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/test',
    ]);

    $rectorConfig->skip([
        // possibly too detail on some cases?
        CountOnNullRector::class,

        // possibly null undefined on purpose?
        AddDefaultValueForUndefinedVariableRector::class,

        // define list of services?
        StringClassNameToClassConstantRector::class,

        // not a secure lib on purpose?
        RandomFunctionRector::class,

        // probably handle after execute?
        JsonThrowOnErrorRector::class,

        \Rector\Php80\Rector\FunctionLike\UnionTypesRector::class,
        //\Rector\Php80\Rector\FunctionLike\MixedTypeRector::class,
    ]);
};

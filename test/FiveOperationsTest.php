<?php
declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\FiveOperations;
use PHPUnit\Framework\TestCase;

class FiveOperationsTest extends TestCase
{

    /**
     * @dataProvider operationProvider
     */
    public function testOperationsWithOptionsGivenByConstructor(string $operation, $operand, $valueToFilter, $expected): void
    {
        $filter = new FiveOperations([
            'operation' => $operation,
            'operand' => $operand
        ]);
        $this->assertEquals($expected, $filter->filter($valueToFilter));
    }
    
    /**
     * @dataProvider operationProvider
     */
    public function testOperationsWithOptionsGivenBySetters(string $operation, $operand, $valueToFilter, $expected): void
    {
        $filter = new FiveOperations();
        $filter->setOperation($operation)
        ->setOperand($operand);
        $this->assertEquals($expected, $filter->filter($valueToFilter));
    }
    
    public function testInvalidOperation(): void
    {
        $this->expectException(\Laminas\Filter\Exception\InvalidArgumentException::class);
        $filter = new FiveOperations([
            'operation' => 'unknown',
            'operand' => 0
        ]);
        $this->assertEquals(10, $filter->filter(5));        
    }
    
    public function testInvalidOperand(): void
    {
        $this->expectException(\Laminas\Filter\Exception\InvalidArgumentException::class);
        $filter = new FiveOperations([
            'operation' => FiveOperations::ADD
        ]);
        $this->assertEquals(10, $filter->filter(5));
    }
    
    public function operationProvider(): array
    {
        // operation, operand, value to filter and expected value
        return [
            [FiveOperations::ADD, 4, 5 , 9],
            [FiveOperations::SUB, 3, 10, 7],
            [FiveOperations::MUL, 5, 6, 30],
            [FiveOperations::DIV, 12, 144, 12],
            [FiveOperations::MOD, 2, 3, 1]
        ];
    }    
}
<?php
declare(strict_types=1);

namespace LaminasTest\Filter;

use PHPUnit\Framework\TestCase;
use Laminas\Filter\Div;

class DivTest extends TestCase
{

    public function testDivWithOptionGivenByConstructor(): void
    {
        $filter = new Div([
            'operand' => 3
        ]);
        $this->assertEquals(9, $filter->filter(27));
    }
    
    public function testOperationsWithOptionsGivenBySetters(): void
    {
        $filter = new Div();
        $filter->setOperand(3);
        $this->assertEquals(9, $filter->filter(27));
    }
    
    public function testInvalidOperand(): void
    {
        $this->expectException(\Laminas\Filter\Exception\InvalidArgumentException::class);
        $filter = new Div();
        $this->assertEquals(5, $filter->filter(5));
    }
}
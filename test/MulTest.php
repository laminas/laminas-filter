<?php
declare(strict_types=1);

namespace LaminasTest\Filter;

use PHPUnit\Framework\TestCase;
use Laminas\Filter\Mul;

class MulTest extends TestCase
{

    public function testMulWithOptionGivenByConstructor(): void
    {
        $filter = new Mul([
            'operand' => 2
        ]);
        $this->assertEquals(12, $filter->filter(6));
    }
    
    public function testOperationWithOptionGivenBySetter(): void
    {
        $filter = new Mul();
        $filter->setOperand(2);
        $this->assertEquals(12, $filter->filter(6));
    }
    
    public function testInvalidOperand(): void
    {
        $this->expectException(\Laminas\Filter\Exception\InvalidArgumentException::class);
        $filter = new Mul();
        $this->assertEquals(5, $filter->filter(5));
    }
}
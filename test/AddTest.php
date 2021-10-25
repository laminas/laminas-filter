<?php
declare(strict_types=1);

namespace LaminasTest\Filter;

use PHPUnit\Framework\TestCase;
use Laminas\Filter\Add;

class AddTest extends TestCase
{

    public function testAddWithOptionGivenByConstructor(): void
    {
        $filter = new Add([
            'operand' => 4
        ]);
        $this->assertEquals(7, $filter->filter(3));
    }
    
    public function testAddWithOptionGivenBySetter(): void
    {
        $filter = new Add();
        $filter->setOperand(4);
        $this->assertEquals(7, $filter->filter(3));
    }
    
    public function testInvalidOperand(): void
    {
        $this->expectException(\Laminas\Filter\Exception\InvalidArgumentException::class);
        $filter = new Add();
        $this->assertEquals(6, $filter->filter(5));
    }
}
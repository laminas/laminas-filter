<?php
declare(strict_types=1);

namespace LaminasTest\Filter;


use PHPUnit\Framework\TestCase;
use Laminas\Filter\Sub;

class SubTest extends TestCase
{

    public function testSubWithOptionGivenByConstructor(): void
    {
        $filter = new Sub([
            'operand' => 2
        ]);
        $this->assertEquals(3, $filter->filter(5));
    }
    
    public function testSubWithOptionGivenBySetter(): void
    {
        $filter = new Sub();
        $filter->setOperand(2);
        $this->assertEquals(3, $filter->filter(5));
    }
    
    public function testInvalidOperand(): void
    {
        $this->expectException(\Laminas\Filter\Exception\InvalidArgumentException::class);
        $filter = new Sub();
        $this->assertEquals(4, $filter->filter(5));
   }
}
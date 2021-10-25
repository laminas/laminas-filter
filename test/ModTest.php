<?php
declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\FiveOperations;
use PHPUnit\Framework\TestCase;
use Laminas\Filter\Add;
use Laminas\Filter\Sub;
use Laminas\Filter\Mul;
use Laminas\Filter\Div;
use Laminas\Filter\Mod;

class ModTest extends TestCase
{

    public function testModWithOptionGivenByConstructor(): void
    {
        $filter = new Mod([
            'operand' => 7            
        ]);
        $this->assertEquals(1, $filter->filter(50));
    }
    
    public function testModWithOptionGivenBySetter(): void
    {
        $filter = new Mod();
        $filter->setOperand(7);
        $this->assertEquals(1, $filter->filter(50));}
    
    public function testInvalidOperand(): void
    {
        $this->expectException(\Laminas\Filter\Exception\InvalidArgumentException::class);
        $filter = new Mod();
        $this->assertEquals(0, $filter->filter(5));
    }
}
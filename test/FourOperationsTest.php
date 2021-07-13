<?php
namespace LaminasTest\Filter;

use Laminas\Filter\FourOperations;
use PHPUnit\Framework\TestCase;

class FourOperationsTest extends TestCase
{
    public function testOperations()
    {
        $filter = new FourOperations(['operation'=>'add','value'=>4]);
        $this->assertEquals(9, $filter->filter(5));
        $filter = new FourOperations(['operation'=>'sub','value'=>3]);
        $this->assertEquals(7, $filter->filter(10));
        $filter = new FourOperations(['operation'=>'mul','value'=>5]);
        $this->assertEquals(30, $filter->filter(6));
        $filter = new FourOperations(['operation'=>'div','value'=>12]);
        $this->assertEquals(12, $filter->filter(144));
        $filter = new FourOperations(['operation'=>'mod','value'=>2]);
        $this->assertEquals(1, $filter->filter(3));
    }
}
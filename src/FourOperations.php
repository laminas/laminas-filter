<?php
namespace Laminas\Filter;


use Laminas\Filter\AbstractFilter;
use Laminas\Filter\Exception\InvalidArgumentException;

class FourOperations extends AbstractFilter {
    const ADD = 'add';
    const SUB = 'sub';
    const MUL = 'mul';
    const DIV = 'div';
    const MOD = 'mod';
    
    /**
     * $options expects an array with two keys:
     * operation: add, sub, mul or div
     * value: value of second operand
     * @param array $options
     */
    public function __construct(array $options)
    {
        $operations = [self::ADD,self::SUB,self::MUL,self::DIV,self::MOD];
        if (!isset($options['operation']) || (!in_array($options['operation'],$operations))){
            throw new InvalidArgumentException(sprintf(
                '%s expects argument operation string with one of theses values: add, sub, mul or div; received "%s"',
                __METHOD__, (float) $options['operation']));
        }
        if (!isset($options['value'])){
            throw new InvalidArgumentException(sprintf(
                '%s expects argument value; received none',
                __METHOD__));
        }
        $this->options = $options;
    }
    
    public function filter($value)
    {
        $value = (float) $value;
        $operand = (float) $this->options['value'];
        switch ($this->options['operation']){
            case self::ADD:
                return ($value + $operand);
            case self::SUB:
                return ($value - $operand);
            case self::MUL:
                return ($value * $operand);
            case self::DIV:
                return ($value / $operand);
            case self::MOD:
                return ($value % $operand);
        }
        return $value;
    }
}
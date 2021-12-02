<?php
namespace Laminas\Filter;

use Laminas\Filter\AbstractFilter;
use Laminas\Filter\Exception\InvalidArgumentException;

class Mul extends AbstractArithmeticOperation
{
    /**
     *
     * {@inheritdoc}
     * @see \Laminas\Filter\FilterInterface::filter()
     */
    public function filter($value)
    {
        if (! isset($this->options['operand'])) {
            throw new InvalidArgumentException(sprintf('%s expects argument value; received none', __METHOD__));
        }

        $value = $value;
        $operand = $this->options['operand'];
        return ($value * $operand);
    }
}
<?php
namespace Laminas\Filter;

use Laminas\Filter\AbstractFilter;
use Laminas\Filter\Exception\InvalidArgumentException;

abstract class AbstractArithmeticOperation extends AbstractFilter
{    
    protected $options = [
    /** expected key "operand" **/
    ];

    /**
     * $options expects an array with one key:
     * operand: value of second operand
     *
     * @param array $options
     */
    public function __construct(array $options = null)
    {
        if ($options instanceof \Traversable) {
            $options = iterator_to_array($options);
        }

        if (! is_array($options) || (! isset($options['operand']))) {
            $args = func_get_args();
            if (isset($args[0])) {
                $this->setOperand($args[0]);
            }
        } else {
            $this->setOptions($options);
        }
    }

    /**
     * 
     * @param integer | float $operand
     */
    public function setOperand($operand): AbstractArithmeticOperation
    {
        $this->options['operand'] = $operand;
        return $this;
    }
}
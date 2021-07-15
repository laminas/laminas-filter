<?php
namespace Laminas\Filter;

use Laminas\Filter\AbstractFilter;
use Laminas\Filter\Exception\InvalidArgumentException;

class FiveOperations extends AbstractFilter
{

    const ADD = 'add';

    const SUB = 'sub';

    const MUL = 'mul';

    const DIV = 'div';

    const MOD = 'mod';

    protected $options = [
        'operation' => null,
        'operand' => null
    ];

    protected $operations = [
        self::ADD,
        self::SUB,
        self::MUL,
        self::DIV,
        self::MOD
    ];

    /**
     * $options expects an array with two keys:
     * operation: add, sub, mul or div
     * operand: value of second operand
     *
     * @param array $options
     */
    public function __construct(array $options = null)
    {
        if ($options instanceof \Traversable) {
            $options = iterator_to_array($options);
        }

        if (! is_array($options) || (! isset($options['operation']) && ! isset($options['operand']))) {
            $args = func_get_args();
            if (isset($args[0])) {
                $this->setOperation($args[0]);
            }
            if (isset($args[1])) {
                $this->setOperand($args[1]);
            }
        } else {
            $this->setOptions($options);
        }
    }

    /**
     *
     * @param string $operation
     * @throws InvalidArgumentException
     */
    public function setOperation($operation)
    {
        if (! in_array($operation, $this->operations)) {
            throw new InvalidArgumentException(sprintf('%s expects argument operation string with one of theses values: add, sub, mul, div or mod; received "%s"', __METHOD__, $operation));
        }

        $this->options['operation'] = $operation;
        return $this;
    }

    public function setOperand($operand)
    {
        $this->options['operand'] = $operand;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Laminas\Filter\FilterInterface::filter()
     */
    public function filter($value)
    {
        if (! isset($this->options['operation']) || (! in_array($this->options['operation'], $this->operations))) {
            throw new InvalidArgumentException(sprintf('%s expects argument operation string with one of theses values: add, sub, mul, div or mod; received "%s"', __METHOD__, $this->options['operation']));
        }

        if (! isset($this->options['operand'])) {
            throw new InvalidArgumentException(sprintf('%s expects argument value; received none', __METHOD__));
        }

        $value = (float) $value;
        $operand = (float) $this->options['operand'];
        switch ($this->options['operation']) {
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
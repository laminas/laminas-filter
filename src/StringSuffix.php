<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Traversable;

use function get_class;
use function gettype;
use function is_object;
use function is_scalar;
use function is_string;
use function sprintf;

class StringSuffix extends AbstractFilter
{
    /** @var array<string, string|null> */
    protected $options = [
        'suffix' => null,
    ];

    /**
     * @param string|array|Traversable $options
     */
    public function __construct($options = null)
    {
        if ($options !== null) {
            $this->setOptions($options);
        }
    }

    /**
     * Set the suffix string
     *
     * @param string $suffix
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setSuffix($suffix)
    {
        if (! is_string($suffix)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects "suffix" to be string; received "%s"',
                __METHOD__,
                is_object($suffix) ? get_class($suffix) : gettype($suffix)
            ));
        }

        $this->options['suffix'] = $suffix;

        return $this;
    }

    /**
     * Returns the suffix string, which is appended at the end of the input value
     *
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function getSuffix()
    {
        if (! isset($this->options['suffix'])) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a "suffix" option; none given',
                self::class
            ));
        }

        return $this->options['suffix'];
    }

    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        if (! is_scalar($value)) {
            return $value;
        }

        $value = (string) $value;

        return $value . $this->getSuffix();
    }
}

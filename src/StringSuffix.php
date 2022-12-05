<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function gettype;
use function is_object;
use function is_scalar;
use function is_string;
use function sprintf;

/**
 * @psalm-type Options = array{
 *     suffix?: null|string,
 * }
 * @extends AbstractFilter<Options>
 */
class StringSuffix extends AbstractFilter
{
    /** @var Options */
    protected $options = [
        'suffix' => null,
    ];

    /**
     * @param Options|iterable|null $options
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
                is_object($suffix) ? $suffix::class : gettype($suffix)
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

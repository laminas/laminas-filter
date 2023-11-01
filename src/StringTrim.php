<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Traversable;

use function is_array;
use function is_string;
use function preg_replace;
use function strlen;

/**
 * @psalm-type Options = array{
 *     charlist?: string|null,
 * }
 * @extends AbstractFilter<Options>
 * @final
 */
class StringTrim extends AbstractFilter
{
    /** @var Options */
    protected $options = [
        'charlist' => null,
    ];

    /**
     * Sets filter options
     *
     * @param  string|Options|iterable|null $charlistOrOptions
     */
    public function __construct($charlistOrOptions = null)
    {
        if ($charlistOrOptions !== null) {
            if (! is_array($charlistOrOptions) && ! $charlistOrOptions instanceof Traversable) {
                $this->setCharList($charlistOrOptions);
            } else {
                $this->setOptions($charlistOrOptions);
            }
        }
    }

    /**
     * Sets the charList option
     *
     * @param  string $charList
     * @return self Provides a fluent interface
     */
    public function setCharList($charList)
    {
        if (! strlen($charList)) {
            $charList = null;
        }

        $this->options['charlist'] = $charList;

        return $this;
    }

    /**
     * Returns the charList option
     *
     * @return string|null
     */
    public function getCharList()
    {
        return $this->options['charlist'];
    }

    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns the string $value with characters stripped from the beginning and end
     *
     * @param  mixed $value
     * @return string|mixed
     * @psalm-return ($value is string ? string : mixed)
     */
    public function filter($value)
    {
        if (! is_string($value)) {
            return $value;
        }
        $value = (string) $value;

        if (null === $this->options['charlist']) {
            return $this->unicodeTrim($value);
        }

        return $this->unicodeTrim($value, $this->options['charlist']);
    }

    /**
     * Unicode aware trim method
     * Fixes a PHP problem
     *
     * @param string $value
     * @param string $charlist
     * @return string
     */
    protected function unicodeTrim($value, $charlist = '\\\\s')
    {
        $chars = preg_replace(
            ['/[\^\-\]\\\]/S', '/\\\{4}/S', '/\//'],
            ['\\\\\\0', '\\', '\/'],
            $charlist
        );

        $pattern = '/^[' . $chars . ']+|[' . $chars . ']+$/usSD';

        return preg_replace($pattern, '', $value);
    }
}

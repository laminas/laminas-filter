<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

use Closure;
use Laminas\Filter\AbstractFilter;
use Laminas\Filter\Exception;

use function preg_quote;
use function preg_replace;

/**
 * @psalm-type Options = array{
 *     search_separator?: string,
 *     replacement_separator?: string,
 *     ...
 * }
 * @template TOptions of Options
 * @template-extends AbstractFilter<TOptions>
 */
class SeparatorToSeparator extends AbstractFilter
{
    /** @var string */
    protected $searchSeparator;
    /** @var string */
    protected $replacementSeparator;

    /**
     * @param  string $searchSeparator      Separator to search for
     * @param  string $replacementSeparator Separator to replace with
     */
    public function __construct($searchSeparator = ' ', $replacementSeparator = '-')
    {
        $this->setSearchSeparator($searchSeparator);
        $this->setReplacementSeparator($replacementSeparator);
    }

    /**
     * Sets a new separator to search for
     *
     * @param  string $separator Separator to search for
     * @return self
     */
    public function setSearchSeparator($separator)
    {
        $this->searchSeparator = $separator;
        return $this;
    }

    /**
     * Returns the actual set separator to search for
     *
     * @return string
     */
    public function getSearchSeparator()
    {
        return $this->searchSeparator;
    }

    /**
     * Sets a new separator which replaces the searched one
     *
     * @param  string $separator Separator which replaces the searched one
     * @return self
     */
    public function setReplacementSeparator($separator)
    {
        $this->replacementSeparator = $separator;
        return $this;
    }

    /**
     * Returns the actual set separator which replaces the searched one
     *
     * @return string
     */
    public function getReplacementSeparator()
    {
        return $this->replacementSeparator;
    }

    /**
     * Defined by Laminas\Filter\Filter
     *
     * Returns the string $value, replacing the searched separators with the defined ones
     *
     * @param  string|mixed $value
     * @return mixed
     * @psalm-return ($value is string ? string : mixed)
     */
    public function filter($value)
    {
        return self::applyFilterOnlyToStringableValuesAndStringableArrayValues(
            $value,
            Closure::fromCallable([$this, 'filterNormalizedValue'])
        );
    }

    /**
     * @param  string $value
     * @return string
     */
    private function filterNormalizedValue($value)
    {
        if ($this->searchSeparator === null) {
            throw new Exception\RuntimeException('You must provide a search separator for this filter to work.');
        }

        return preg_replace(
            '#' . preg_quote($this->searchSeparator, '#') . '#',
            $this->replacementSeparator,
            $value
        );
    }
}

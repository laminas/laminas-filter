<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Stdlib\ArrayUtils;
use Traversable;

use function in_array;
use function is_array;

class AllowList extends AbstractFilter
{
    /** @var bool */
    protected $strict = false;

    /** @var array */
    protected $list = [];

    /**
     * @param null|array|Traversable $options
     */
    public function __construct($options = null)
    {
        if (null !== $options) {
            $this->setOptions($options);
        }
    }

    /**
     * Determine whether the in_array() call should be "strict" or not. See in_array docs.
     *
     * @param bool $strict
     */
    public function setStrict($strict = true): void
    {
        $this->strict = (bool) $strict;
    }

    /**
     * Returns whether the in_array() call should be "strict" or not. See in_array docs.
     *
     * @return bool
     */
    public function getStrict()
    {
        return $this->strict;
    }

    /**
     * Set the list of items to white-list.
     *
     * @param array|Traversable $list
     */
    public function setList($list = []): void
    {
        if (! is_array($list)) {
            $list = ArrayUtils::iteratorToArray($list);
        }

        $this->list = $list;
    }

    /**
     * Get the list of items to white-list
     *
     * @return array
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * {@inheritDoc}
     *
     * Will return $value if its present in the white-list. If $value is rejected then it will return null.
     */
    public function filter($value)
    {
        return in_array($value, $this->getList(), $this->getStrict()) ? $value : null;
    }
}

<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter;

use Traversable;

class StringSuffix extends AbstractFilter
{
    /**
     * @var array
     */
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
     *
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setSuffix($suffix)
    {
        if (! is_string($suffix)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects "suffix" to be string; received "%s"',
                __METHOD__,
                (is_object($suffix) ? get_class($suffix) : gettype($suffix))
            ));
        }

        $this->options['suffix'] = $suffix;

        return $this;
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        if (! isset($this->options['suffix'])) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a "suffix" option; none given',
                __CLASS__
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

        $value = (string)$value;

        return $value . $this->getSuffix();
    }
}

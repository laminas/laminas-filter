<?php

/**
 * @link      http://github.com/zendframework/zend-filter for the canonical source repository
 *
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Filter;

interface FilterProviderInterface
{
    /**
     * Provide plugin manager configuration for filters.
     *
     * @return array
     */
    public function getFilterConfig();
}

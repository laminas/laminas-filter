<?php

namespace Laminas\Filter;

/**
 * @deprecated Use AllowList
 */
class Whitelist extends AllowList
{
    public function __construct($options = null)
    {
        trigger_error(
            sprintf(
                'The class %s has been deprecated; please use %s\\AllowList',
                __CLASS__,
                __NAMESPACE__
            ),
            E_USER_DEPRECATED
        );

        parent::__construct($options);
    }
}

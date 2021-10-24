<?php

namespace Laminas\Filter;

/**
 * @deprecated Use DenyList
 */
class Blacklist extends DenyList
{
    public function __construct($options = null)
    {
        trigger_error(
            sprintf(
                'The class %s has been deprecated; please use %s\\DenyList',
                __CLASS__,
                __NAMESPACE__
            ),
            E_USER_DEPRECATED
        );

        parent::__construct($options);
    }
}

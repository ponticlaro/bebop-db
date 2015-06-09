<?php

namespace Ponticlaro\Bebop\Db\Query\Presets;

class IgnoreStickyArg extends \Ponticlaro\Bebop\Db\Query\Arg {
    
    protected $key = 'ignore_sticky_posts';

    public function __construct($ignore = null)
    {
        if (is_bool($ignore)) 
            $this->is($ignore);
    }

    public function is($ignore)
    {
        if (is_bool($ignore))
            $this->value = $ignore;

        return $this;
    }
}
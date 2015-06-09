<?php

namespace Ponticlaro\Bebop\Db\Query\Presets;

class SearchArg extends \Ponticlaro\Bebop\Db\Query\Arg {

    protected $key = 's';

    public function __construct($keywords = null)
    {
        if (is_string($keywords)) 
            $this->value = $keywords;
    }
}
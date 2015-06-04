<?php

namespace Ponticlaro\Bebop\Db\Query\Presets;

use Ponticlaro\Bebop\Common\Collection;

class OrderByArg extends \Ponticlaro\Bebop\Db\Query\Arg {
    
    protected $data;

    protected $has_multiple_keys = true;

    public function __construct($by = null, $direction = 'DESC')
    {
        $this->data = new Collection();

        if (is_string($by) && is_string($direction)) {

            $this->data->set('orderby', $by);
            $this->data->set('order', $direction);
        }
    }

    public function by($by)
    {
        if (is_string($by))
            $this->data->set('orderby', $by);

        return $this;
    }

    public function direction($direction)
    {
        if (is_string($direction))
            $this->data->set('order', $direction);

        return $this;
    }

    public function getValue()
    {
        return $this->data->getAll() ?: null;
    }
}
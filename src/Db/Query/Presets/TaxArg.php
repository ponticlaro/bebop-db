<?php

namespace Ponticlaro\Bebop\Db\Query\Presets;

use Ponticlaro\Bebop\Common\Collection;

class TaxArg extends \Ponticlaro\Bebop\Db\Query\Arg {
    
    protected $key = 'tax_query';

    protected $relation = 'AND';

    protected $items;

    protected $current_child;
    
    protected $is_parent = true;

    public function __construct($key = null)
    {
        if(is_null($this->items)) $this->items = new Collection;

        if ($key && is_string($key)) {

            $this->addChild($key);
        }
    }

    public function relation($relation)
    {
        if (is_string($relation))
            $this->relation = $relation;

        return $this;
    }

    public function addChild()
    {
        if (!is_null($this->current_child)) 
            $this->items->push($this->current_child);

        $item = call_user_func_array(
            array(
                new \ReflectionClass('Ponticlaro\Bebop\Db\Query\Presets\TaxArgChild'), 
                'newInstance'
            ), 
            func_get_args()
        );

        $this->current_child = $item;

        return $this;
    }

    public function getCurrentChild()
    {
        return $this->current_child ?: null;
    }

    public function getValue()
    {
        if (!is_null($this->current_child)) {

            $this->items->push($this->current_child);
            $this->current_child = null;
        }

        $args = array();

        foreach ($this->items->getAll() as $item) {
            
            if ($item->getValue())
                $args[] = $item->getValue();
        }

        if ($args)
            $args = array('relation' => $this->relation) + $args;

        return $args ?: null;
    }
}
<?php

namespace Kollus\Component\Container;

abstract class AbstractContainer
{
    /**
     * AbstractContainer constructor.
     * @param object|array $items
     */
    public function __construct($items = [])
    {
        if (is_array($items) || is_object($items)) {
            foreach ((array)$items as $key => $value) {
                $method = 'set'.str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
                if (method_exists($this, $method)) {
                    $this->{$method}($value);
                }
            }
        }
    }
}

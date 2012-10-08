<?php
/**
 * Simple dependency injection. Could use this directly, but you probably want 
 * to use the `Project` class with sets some things up for easier use.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core;

!defined('ABSPATH') && exit;

use \Closure;
use \InvalidArgumentException;

class DI
{
    private $vals = array();

    public function __construct($vals=array())
    {
        $this->vals = $vals;
    }

    public function __set($key, $val)
    {
        $this->vals[$key] = $val;
    }

    public function __get($key)
    {
        if(empty($this->vals[$key]))
        {
            throw new InvalidArgumentException("'{$key}' is not defined");
        }

        return is_callable($this->vals[$key]) ? $this->vals[$key]($this) : $this->vals[$key];
    }

    public function share(Closure $call)
    {
        return function ($c) use ($call) {
            static $obj;

            is_null($obj) && $obj = $call($c);

            return $obj;
        };
    }
}

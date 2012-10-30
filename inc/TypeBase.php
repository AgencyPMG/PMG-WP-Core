<?php
/**
 * Baseclass for the type creation classes.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core;

!defined('ABSPATH') && exit;


abstract class TypeBase
{
    protected $type;

    protected $singular;

    protected $plural;

    protected $args;

    public function __construct($type, $singular, $plural, $args=array())
    {
        $this->type = $type;
        $this->singular = $singular;
        $this->plural = $plural;
        $this->args = $args;
        add_action('init', array($this, 'register'), 20);
    }

    /**
     * Let users change the args for the type directly.
     *
     * @since   1.0
     * @access  public
     * @return  void;
     */
    public function __set($key, $val)
    {
        $this->args[$key] = $val;
    }

    /**
     * Get something from the args array.
     *
     * @since   1.0
     * @access  public
     * @return  mixed
     */
    public function __get($key)
    {
        if('type' == $key)
            return $this->type;

        return isset($this->args[$key]) ? $this->args[$key] : null;
    }

    /**
     * Hooked into init.  Actually registers the post type or taxonomy.
     *
     * @since   1.0
     * @access  public
     * @return  void
     */
    abstract public function register();
}

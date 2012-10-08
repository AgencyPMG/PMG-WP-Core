<?php
/**
 * Base class that handles auto hooking things.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core;

!defined('ABSPATH') && exit;

use \ReflectionClass;
use \ReflectionMethod;

abstract class PluginBase
{
    /**
     * Container for the objects.
     *
     * @since   0.1
     */
    private static $registry = array();

    /**
     * Get an instance of the current, called class.
     *
     * @since   0.1
     * @access  public
     * @return  object An instance of $cls
     */
    public static function instance()
    {
        $cls = get_called_class();
        !isset(self::$registry[$cls]) && self::$registry[$cls] = new $cls;
        return self::$registry[$cls];
    }

    /**
     * Adds the `_setup` method to plugins loaded.
     *
     * @since   0.1
     * @uses    add_action
     * @return  void
     */
    public static function init()
    {
        add_action('plugins_loaded', array(static::instance(), '_setup'), 2);
    }

    /**
     * Empty, public constructor.
     *
     * @since   0.1
     * @access  public
     */
    public function __construct()
    {
        // empty
    }

    /**
     * Hooked into `plugins_loaded`.  Adds actions/filters using ReflectionClass
     * and ReflectionMethod.
     *
     * @since   0.1
     * @return  void
     */
    public function _setup()
    {
        $r = new ReflectionClass($this);

        foreach($r->getMethods(ReflectionMethod::IS_PUBLIC) as $m)
        {

            if($m->isConstructor() || $m->isStatic() || __FUNCTION__ == $m->name)
                continue;

            $doc = $m->getDocComment();

            $hook = preg_match('/@hook\s(.*?)\s/ui', $doc, $match) ? $match[1] : $m->name;

            if('none' == $hook)
                continue;

            $prio = preg_match('/@priority\s(.*?)\s/ui', $doc, $match) ? intval($match[1]) : 10;

            add_filter(
                $hook,
                array($this, $m->name),
                $prio,
                $m->getNumberofParameters()
            );
        }
    }
}

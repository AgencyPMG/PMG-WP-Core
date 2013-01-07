<?php
/**
 * A few functions without a namespace to make things easier.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     http://opensource.org/licenses/MIT MIT
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

!defined('ABSPATH') && exit;

use PMG\Core\Project;

/**
 * Get an instance of Project.  $key will become the meta prefix.
 *
 * @since   1.0
 * @param   string $key The project key.
 * @return  object An instance of PMG\Core\Project
 */
function pmgcore($key)
{
    static $registry = array();

    empty($registry[$key]) && $registry[$key] = new Project($key);

    return $registry[$key];
}

/**
 * Hooked into `plugins_loaded`. Fire an action signifying pmgcore is loaded.
 *
 * @since   1.1
 * @uses    do_action
 * @return  void
 */
function pmgcore_loaded()
{
    do_action('pmgcore_loaded');
}

/**
 * Hooked into `after_setup_theme`.  Fire an action signifying pmgcore is
 * loaded for themes
 *
 * @since   1.0
 * @uses    do_action
 * @return  void
 */
function pmgcore_loaded_theme()
{
    do_action('pmgcore_loaded_theme');
}

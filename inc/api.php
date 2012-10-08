<?php
/**
 * A few functions without a namespace to make things easier.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
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

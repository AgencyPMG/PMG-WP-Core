<?php
/*
Plugin Name: PMG Core
Plugin URI: http://pmg.co
Description: Core utlities and classes for all PMG built websites. If you deactivate this plugin, there's a good chance your site will blow up.
Version: 1.0
Text Domain: pmgcore
Author: Christopher Davis
Author URI: http://pmg.co/people/chris
License: GPL2

    Copyright 2012 Performance Media Group <seo@pmg.co>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

namespace PMG\Core;

!defined('ABSPATH') && exit;

spl_autoload_register(__NAMESPACE__ . '\\loader');
/**
 * PMG Core autoloader. Looks for classes in this namespace and loads them.
 *
 * @since   1.0
 * @param   string $cls The class to load.
 * @uses    plugin_dir_path
 * @return  null
 */
function loader($cls)
{
    static $path = null;
    is_null($path) && $path = plugin_dir_path(__FILE__);

    $cls = ltrim($cls, '\\');

    if(strpos($cls, __NAMESPACE__) !== 0)
        return;

    $cls = str_replace(
        '\\', DIRECTORY_SEPARATOR, str_replace(__NAMESPACE__, '', $cls));

    require_once($path . "inc{$cls}.php");
}

require_once(dirname(__FILE__) . '/inc/api.php');

// A tiny bit of functionality
Cleaner::init();
Headers::init();
Uploads::init();

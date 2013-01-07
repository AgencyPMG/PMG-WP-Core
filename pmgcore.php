<?php
/*
Plugin Name: PMG Core
Plugin URI: http://pmg.co
Description: Core utlities and classes for all PMG built websites. If you deactivate this plugin, there's a good chance your site will blow up.
Version: 1.0
Text Domain: pmgcore
Author: Christopher Davis
Author URI: http://pmg.co/people/chris
License: MIT

    Copyright (c) 2012 Performance Media Group, Christopher Davis

    Permission is hereby granted, free of charge, to any person obtaining a
    copy of this software and associated documentation files (the "Software"),
    to deal in the Software without restriction, including without limitation
    the rights to use, copy, modify, merge, publish, distribute, sublicense,
    and/or sell copies of the Software, and to permit persons to whom the
    Software is furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
    FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
    DEALINGS IN THE SOFTWARE.
*/

namespace PMG\Core;

!defined('ABSPATH') && exit;

define('PMGCORE_URL', plugins_url('/', __FILE__));

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

add_action('plugins_loaded', 'pmgcore_loaded', 2);
add_action('after_setup_theme', 'pmgcore_loaded_theme', 2);

// A tiny bit of functionality
Functionality\Cleaner::init();
Functionality\Headers::init();
Functionality\Uploads::init();
Functionality\Enqueue::init();

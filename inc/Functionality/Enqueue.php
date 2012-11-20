<?php
/**
 * Enqueue a few scripts and styles in the admin area.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     http://opensource.org/licenses/MIT MIT
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core\Functionality;

!defined('ABSPATH') && exit;

use PMG\Core\PluginBase;

class Enqueue extends PluginBase
{
    const VER = 2;

    public function _setup()
    {
        add_action('admin_enqueue_scripts', array($this, 'register'));
        add_action('admin_print_scripts-post-new.php', array($this, 'scripts'));
        add_action('admin_print_scripts-post.php', array($this, 'scripts'));
        add_action('admin_print_styles-post-new.php', array($this, 'styles'));
        add_action('admin_print_styles-post.php', array($this, 'styles'));
    }

    public function register()
    {
        wp_register_style(
            'pmgcore-tab-css',
            PMGCORE_URL . 'css/tabs.css',
            array(),
            self::VER,
            'screen'
        );

        wp_register_script(
            'pmgcore-tab-js',
            PMGCORE_URL . 'js/tabs.js',
            array('jquery'),
            self::VER
        );

        wp_register_script(
            'pmgcore-media',
            PMGCORE_URL . 'js/media.js',
            array('jquery'),
            self::VER,
            true
        );
    }

    public function scripts()
    {
        wp_enqueue_script('pmgcore-tab-js');
    }

    public function styles()
    {
        wp_enqueue_style('pmgcore-tab-css');
    }
}

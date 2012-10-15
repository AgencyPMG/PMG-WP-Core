<?php
/**
 * Add and remove a few headers.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core\Functionality;

!defined('ABSPATH') && exit;

use PMG\Core\PluginBase;

class Headers extends PluginBase
{
    public function _setup()
    {
        add_action('template_redirect', array($this, 'template_redirect'));
        add_filter('wp_headers', array($this, 'wp_headers'));
    }

    /**
     * Remove wp_shortlink_header from `template_redirect`
     *
     * @since   1.0
     * @access  public
     * @return  void
     */
    public function template_redirect()
    {
        remove_action('template_redirect', 'wp_shortlink_header', 11);
    }

    /**
     * Hooked into `wp_headers`.  Adds X-Frame-Options, X-Powered-By, and
     * X-UA-Compatible headers. and removes X-Pingback
     *
     * @since   1.0
     * @access  public
     * @return  array The header array
     */
    public function wp_headers($h)
    {
        $h['X-Frame-Options'] = 'SAMEORIGIN';
        $h['X-Powered-By'] = 'WordPress + PMG';
        $h['X-UA-Compatible'] = 'IE=edge,chrome=1';

        if(isset($h['X-Pingback']))
            unset($h['X-Pingback']);

        return $h;
    }
} // end Headers

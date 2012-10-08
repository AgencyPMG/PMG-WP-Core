<?php
/**
 * Clean up some things around WP
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core;

!defined('ABSPATH') && exit;

class Cleaner extends PluginBase
{
    /**
     * Hooked into `init`. Sets some options programmatically and removes
     * other actions.
     *
     * @since       1.0
     * @access      public
     * @uses        remove_action
     * @uses        add_filter
     * @return      void
     */
    public function plugins_loaded()
    {
        remove_action('wp_head', 'wp_generator');
        remove_filter('pre_term_description', 'wp_filter_kses');

        add_filter('pre_option_default_pingback_flag', '__return_zero');
        add_filter('pre_option_default_ping_status', '__return_zero');
        add_filter('pre_option_default_comment_status', '__return_zero');
        add_filter('pre_option_comment_moderation', '__return_true');
        add_filter('pre_option_enable_xmlrpc', '__return_zero');
        add_filter('pre_option_enable_app', '__return_zero');
    }

    /**
     * Cleanup the WordPress dashboard a bit.
     *
     * @priority    1
     * @since       1.0
     * @access      public
     * @uses        remove_meta_box
     * @return      void
     */
    public function wp_dashboard_setup()
    {
        /**
         * Removes the "Right Now" widget that tells you post/comment counts
         * and what theme you're using.
         */
        if(apply_filters('pmg_core_remove_right_now', false))
        {
            remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
        }

        /**
         * Removes the recent comments widget
         */
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

        /**
         * Removes the incoming links widget.
         */
        remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');

        /**
         * Removes the plugins widgets that displays the most popular,
         * newest, and recently updated plugins
         */
        remove_meta_box('dashboard_plugins', 'dashboard', 'normal');

        /**
         * Removes the quick press widget that allows you post from 
         * the dashboard.
         */
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');

        /**
         * Removes the widget containing the list of recent drafts
         */
        remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');

        /**
         * Removes the "WordPress Blog" widget
         */
        remove_meta_box('dashboard_primary', 'dashboard', 'side');

        /**
         * Removes the "Other WordPress News" widget
         */
        remove_meta_box('dashboard_secondary', 'dashboard', 'side');
    }

    /**
     * Disable comments.
     *
     * @priority    1
     * @since       1.0
     * @uses        wp_die
     * @uses        apply_filters
     * @return      void
     */
    public function comment_moderation()
    {
        if($this->comments_allowed())
            return;

        wp_die(
            __('Sorry, comments are closed', 'g6'),
            __('Comments Closed', 'g6'),
            array('response' => 403)
        );
    }

    /**
     * If comments are disabled, remove the comment page.
     *
     * @since   1.0
     * @access  public
     * @uses    remove_menu_page
     */
    public function admin_menu()
    {
        if($this->comments_allowed())
            return;

        remove_menu_page('edit-comments.php');
    }

    /**
     * Allow users with the `unfiltered_html` cap to put anything they like in
     * term descriptions.
     *
     * @since   1.0
     * @access  public
     * @uses    current_user_can
     * @uses    wp_filter_kses
     * @return  string
     */
    public function pre_term_description($content)
    {
        return current_user_can('unfiltered_html') ?
            $content : wp_filter_kses($content);
    }

    /**
     * Whether or not comments are allowed.
     *
     * @since   1.0
     * @access  protected
     * @uses    apply_filters
     * @return  bool
     */
    protected function comments_allowed()
    {
        return apply_filters('pmg_core_allow_comments', true);
    }
} // end cleaner

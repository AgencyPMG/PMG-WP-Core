<?php
/**
 * Automates the creation of post types.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core;

!defined('ABSPATH') && exit;

class PostType
{
    private $type;

    private $singular;

    private $plural;

    private $args;

    public function __construct($type, $singular, $plural, $args=array())
    {
        $this->type = $type;
        $this->singular = $singular;
        $this->plural = $plural;
        $this->args = $args;
        add_action('init', array($this, 'register'), 20);
    }

    /**
     * Let users change the args for the post type directly.
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
     * Hooked into init.  Actually registers the post type.
     *
     * @since   1.0
     * @access  public
     * @uses    register_post_type
     * @return  void
     */
    public function register()
    {
        // let users define labels if they want.
        if(!isset($this->args['labels']))
            $this->args['labels'] = $this->get_labels();

        if(isset($this->args['menu_name']))
        {
            $this->args['labels']['menu_name'] = $this->args['menu_name'];
            unset($this->args['menu_name']);
        }

        register_post_type(
            $this->type,
            apply_filters("pmgcore_{$this->type}_args", $this->args)
        );
    }

    private function get_labels()
    {
        $labels = array(
            'name'              => $this->plural,
            'singular_name'     => $this->singular,
            'add_new'           => sprintf(__('New %s', 'pmgcore'), $this->singular),
            'all_items'         => sprintf(__('All %s', 'pmgcore'), $this->plural),
            'edit_item'         => sprintf(__('Edit %s', 'pmgcore'), $this->singular),
            'view_item'         => sprintf(__('View %s', 'pmgcore'), $this->singular),
            'search_items'      => sprintf(__('Search %s', 'pmgcore'), $this->plural),
            'not_found'         => sprintf(__('No %s Found', 'pmgcore'), $this->plural),
            'parent_item_colon' => sprintf(__('Parent %s:', 'pmgcore'), $this->singular)
        );

        $labels['add_new_item'] = $labels['add_new'];
        $labels['new_item'] = $labels['add_new'];
        $labels['not_found_in_trash'] = $labels['not_found'];

        return apply_filters("pmgcore_{$this->type}_labels", $labels);
    }
}

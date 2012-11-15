<?php
/**
 * Automates the creation of post types.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     http://opensource.org/licenses/MIT MIT
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core;

!defined('ABSPATH') && exit;

class PostType extends TypeBase
{
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

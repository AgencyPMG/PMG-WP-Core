<?php
/**
 * Automates the creation of taxonomies.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core;

!defined('ABSPATH') && exit;

class Taxonomy extends TypeBase
{
    private $post_types = array('post');

    public function __construct($type, $singular, $plural, $args=array(), $types=array('post'))
    {
        $this->post_types = $types;
        parent::__construct($type, $singular, $plural, $args);
    }

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

        register_taxonomy(
            $this->type,
            $this->post_types,
            apply_filters("pmgcore_tax_{$this->type}_args", $this->args)
        );
    }

    private function get_labels()
    {
        return apply_filters("pmgcore_tax_{$this->type}_labels", array(
            'name'                  => $this->plural,
            'singular_name'         => $this->singular,
            'search_items'          => sprintf(__('Search %s', 'marklogic'), $this->plural),
            'popular_items'         => sprintf(__('Popular %s', 'marklogic'), $this->plural),
            'all_items'             => sprintf(__('All %s', 'marklogic'), $this->plural),
            'parent_item'           => sprintf(__('Parent %s', 'marklogic'), $this->singular),
            'parent_item_colon'     => sprintf(__('Parent %s:', 'marklogic'), $this->singular),
            'edit_item'             => sprintf(__('Edit %s', 'marklogic'), $this->singular),
            'update_item'           => sprintf(__('Update $s', 'marklogic'), $this->singular),
            'add_new_item'          => sprintf(__('New %s', 'marklogic'), $this->singular),
            'new_item_name'         => sprintf(__('New %s Name', 'marklogic'), $this->singular),
            'separate_items_with_commas' => sprintf(__('Seperate %s with commas', 'marklogic'), strtolower($this->plural)),
            'add_or_remove_items'   => sprintf(__('Add or Remove %s', 'marklogic'), $this->plural),
            'choose_from_most_uses' => sprintf(__('Choose from most used %s', 'marklogic'), strtolower($this->plural)),
        ));
    }
}

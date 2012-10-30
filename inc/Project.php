<?php
/**
 * Central dependency injection object for use in plugins/themes.  Sets up 
 * instances of Meta objects for users, posts, & comments.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core;

use PMG\Core\Fields\FieldInterface;

!defined('ABSPATH') && exit;

class Project extends DI
{
    private $prefix;

    private $settings = array();

    private $mb_fields = array();

    private $meta_fields = array();

    private $admin_pages = array();

    private $meta_boxes = array();

    private $user_boxes = array();

    private $term_boxes = array();

    private $post_types = array();

    public function __construct($meta_prefix, $vals=array())
    {
        $this->prefix = $meta_prefix;
        parent::__construct($vals);

        $this->settings_factory_class = __NAMESPACE__ . '\\Fields\\Settings';

        $this->admin_page_class = __NAMESPACE__ . '\\AdminPage';

        $this->meta_class = __NAMESPACE__ . '\\Meta\\Meta';
        $this->term_meta_class = __NAMESPACE__ . '\\Meta\\TermMeta';
        $this->meta_box_class = __NAMESPACE__ . '\\MetaBox';
        $this->user_box_class = __NAMESPACE__ . '\\UserBox';
        $this->term_box_class = __NAMESPACE__ . '\\TermBox';
        $this->mb_fields_class = __NAMESPACE__ . '\\Fields\\MetaBoxFields';
        $this->meta_fields_class = __NAMESPACE__ . '\\Fields\\MetaFields';
        $this->post_type_class = __NAMESPACE__ . '\\PostType';

        $this->postmeta = $this->share(function($c) {
            $cls = $c->meta_class;
            return new $cls('post', $c->get_prefix());
        });

        $this->usermeta = $this->share(function($c) {
            $cls = $c->meta_class;
            return new $cls('user', $c->get_prefix());
        });

        $this->commentmeta = $this->share(function($c) {
            $cls = $c->meta_class;
            return new $cls('comment', $c->get_prefix());
        });

        $this->termmeta = $this->share(function($c) {
            global $wpdb;

            $cls = $c->term_meta_class;

            if(isset($wpdb->termmeta))
                $cls = $c->meta_class;

            return new $cls('term', $c->get_prefix());
        });
    }

    public function get_prefix()
    {
        return $this->prefix;
    }

    public function setting($name, $page=null)
    {
        if(empty($this->settings[$name]))
        {
            $cls = $this->settings_factory_class;
            $this->settings[$name] = new $cls("{$this->prefix}_{$name}", $page);
        }

        return $this->settings[$name];
    }

    public function box_fields($name)
    {
        if(empty($this->mb_fields[$name]))
        {
            $cls = $this->mb_fields_class;
            $this->mb_fields[$name] = new $cls("{$this->prefix}_{$name}");
        }

        return $this->mb_fields[$name];
    }

    public function meta_fields($name)
    {
        if(empty($this->meta_fields[$name]))
        {
            $cls = $this->meta_fields_class;
            $this->meta_fields[$name] = new $cls("{$this->prefix}_{$name}");
        }

        return $this->meta_fields[$name];
    }

    public function admin_page($key, FieldInterface $s, $opts=array())
    {
        if(!empty($this->admin_pages[$key]))
            return false;

        $cls = $this->admin_page_class;

        $this->admin_pages[$key] = new $cls($this->get_prefix(), $s, $opts);

        return true;
    }

    public function meta_box($key, FieldInterface $f, $opts=array(), $types=array())
    {
        if(!empty($this->meta_boxes[$key]))
            return false;

        $cls = $this->meta_box_class;

        $this->meta_boxes[$key] = new $cls(
            $this->get_prefix(), $f, $this->postmeta, $opts, $types);

        return true;
    }

    public function user_box($key, FieldInterface $f, $cap='edit_user')
    {
        if(!empty($this->user_boxes[$key]))
            return false;

        $cls = $this->user_box_class;

        $this->user_boxes[$key] = new $cls(
            $this->get_prefix(), $f, $this->usermeta, $cap);

        return true;
    }

    public function term_box($key, FieldInterface $f, $tax=array())
    {
        if(!empty($this->term_boxes[$key]))
            return false;

        $cls = $this->term_box_class;

        $this->term_boxes[$key] = new $cls(
            $this->get_prefix(), $f, $this->termmeta, $tax);

        return true;
    }

    public function create_type($type, $singular, $plural, $args=array())
    {
        if(!empty($this->post_types[$type]))
            return $this->post_types[$type];

        $cls = $this->post_type_class;

        $this->post_types[$type] = new $cls($type, $singular, $plural, $args);

        return $this->post_types[$type];
    }
}

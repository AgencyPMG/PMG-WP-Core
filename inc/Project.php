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

!defined('ABSPATH') && exit;

class Project extends DI
{
    private $prefix;

    private $settings = array();

    public function __construct($meta_prefix, $vals=array())
    {
        $this->prefix = $meta_prefix;
        parent::__construct($vals);

        $this->settings_factory_class = __NAMESPACE__ . '\\SettingsFields';

        $this->meta_class = __NAMESPACE__ . '\\Meta';

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
}

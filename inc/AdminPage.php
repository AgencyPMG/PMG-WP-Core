<?php
/**
 * Admin page base class.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core;

!defined('ABSPATH') && exit;

use PMG\Core\Fields\FieldInterface;

class AdminPage
{
    private $proj;

    private $parent = null;

    private $icon = 'tools';

    private $slug;

    private $title;

    private $menu_name;

    private $cap = 'manage_options';

    private $fields;

    public function __construct($proj, FieldInterface $s, $opts=array())
    {
        $this->proj = $proj;

        $this->slug = !empty($opts['slug']) ? $opts['slug'] : $proj;
        $this->title = !empty($opts['title']) ? $opts['title'] : __('Options', 'pmgcore');
        $this->menu_name = !empty($opts['menu_name']) ? $opts['menu_name'] : __('Options', 'pmgcore');
        $this->parent = !empty($opts['parent']) ? $opts['parent'] : null;
        $this->icon = !empty($opts['icon']) ? $opts['icon'] : 'tools';
        $this->cap = !empty($opts['cap']) ? $opts['cap'] : 'manage_options';

        $this->fields = $s;
    }

    public function admin_menu()
    {
        if(is_null($this->parent))
        {
            $p = add_menu_page(
                $this->title,
                $this->menu_name,
                $this->cap,
                $this->slug,
                array($this, 'page_cb')
            );
        }
        else
        {
            $p = add_submenu_page(
                $this->parent,
                $this->title,
                $this->menu_name,
                $this->cap,
                $this->slug,
                array($this, 'page_cb')
            );
        }

        if(method_exists($this, 'styles'))
            add_action("admin_print_styles-{$p}", array($this, 'styles'));

        if(method_exists($this, 'scripts'))
            add_action("admin_print_scripts-{$p}", array($this, 'scripts'));
    }

    /**
     * @hook none
     */
    public function page_cb()
    {
        ?>
        <div class="wrap">
            <?php screen_icon($this->icon); ?>
            <h2><?php echo esc_html($this->title); ?></h2>
            <?php $this->fields->render(); ?>
        </div>
        <?php
    }
} // end AdminPage

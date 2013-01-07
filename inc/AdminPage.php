<?php
/**
 * Admin page base class.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     http://opensource.org/licenses/MIT MIT
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

    private $menu_icon = '';

    private $slug;

    private $title;

    private $menu_name;

    private $position;

    private $cap = 'manage_options';

    private $fields;

    public function __construct($proj, FieldInterface $s, $opts=array())
    {
        $this->proj = $proj;

        $this->slug = !empty($opts['slug']) ? $opts['slug'] : $proj;
        $this->title = !empty($opts['title']) ? $opts['title'] : __('Options', 'pmgcore');
        $this->menu_name = !empty($opts['menu_name']) ? $opts['menu_name'] : __('Options', 'pmgcore');
        $this->parent = !empty($opts['parent']) ? $opts['parent'] : null;
        $this->position = !empty($opts['position']) ? $opts['position'] : null;
        $this->menu_icon = !empty($opts['menu_icon']) ? $opts['menu_icon'] : '';
        $this->icon = !empty($opts['icon']) ? $opts['icon'] : 'tools';
        $this->cap = !empty($opts['cap']) ? $opts['cap'] : 'manage_options';

        $this->fields = $s;

        add_action('admin_menu', array($this, 'admin_menu'), 20);
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
                array($this, 'page_cb'),
                $this->menu_icon,
                $this->position
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

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

abstract class AdminPage extends PluginBase
{
    protected $parent = null;

    protected $icon = 'tools';

    protected $slug;

    protected $title;

    protected $menu_name;

    protected $opt;

    protected $cap = 'manage_options';

    abstract public function admin_init();

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
            <?php settings_errors($this->opt); ?>
            <form method="post" action="<?php echo admin_url('options.php'); ?>">
                <?php
                settings_fields($this->opt);
                do_settings_sections($this->opt);
                submit_button(__('Save Settings', 'pmgcore'));
                ?>
            </form>
        </div>
        <?php
    }
} // end AdminPage

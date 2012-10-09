<?php
/**
 * Settings fields and registraction wrapped up in a nice package.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core;

!defined('ABSPATH') && exit;

class SettingsFields extends FieldFactory
{
    /**
     * The page on which these fields belong.
     *
     * @since   1.0
     * @access  protected
     * @var     string
     */
    protected $page;

    public function __construct($opt, $page=null)
    {
        parent::__construct($opt);
        $this->page = is_null($page) ? $opt : $page;
        add_action('admin_init', array($this, 'register'));
    }

    /********** Public API **********/

    /**
     * Get the value for $key from $this->opt in the option table.
     *
     * @since   1.0
     * @access  public
     * @param   string $key The option key to fetch
     * @param   mixed $default (optional) The default value if the option isn't set
     * @return  mixed Whatever happens to be in the option value.
     */
    public function get($key, $default='')
    {
        $opts = get_option($this->opt, array());
        return isset($opts[$key]) ? $opts[$key] : $default;
    }

    /********** Hooks **********/

    /**
     * Hooked into `admin_init`.  Takes care of registering the section as well
     * as adding the fields/section.
     *
     * @since   1.0
     * @access  public
     * @uses    register_setting
     * @uses    add_settings_section
     * @uses    add_settings_field
     * @return  void
     */
    public function register()
    {
        register_setting(
            $this->page,
            $this->opt,
            array($this, 'validate')
        );

        foreach($this->sections as $s => $section)
        {
            add_settings_section(
                $s,
                $section['title'],
                array($this, 'section_cb'),
                $this->page
            );
        }

        foreach($this->fields as $f => $field)
        {
            $field['value'] = $this->get($f);
            $field['label_for'] = $this->gen_name($f);

            add_settings_field(
                $f,
                $field['label'],
                array($this, 'cb'),
                $this->page,
                $field['section'],
                $field
            );
        }
    }

    public function section_cb($args)
    {
        if(!empty($this->sections[$args['id']]['help']))
        {
            echo '<p class="description">',
                esc_html($this->sections[$args['id']]['help']), '</p>';
        }
    }
} // end SettingsFactory

<?php
/**
 * A class that makes generating form fields easier. This isn't meant to be
 * used directly.  See SettingsFactory & MetaFactory.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core\Fields;

!defined('ABSPATH') && exit;

abstract class FieldBase
{
    const CHECK_ON = 'on';
    const CHECK_OFF = 'off';

    /**
     * Settings name or prefix for metabox fields.
     * 
     * @since   1.0
     * @access  private
     * @var     string
     */
    protected $opt = null;

    /**
     * Container for registered form fields
     *
     * @since   1.0
     * @access  private
     */
    protected $fields = array();

    /**
     * Container for the field sections.
     *
     * @since   1.0
     * @access  protected
     * @var     array
     */
    protected $sections = array();


    public function __construct($opt)
    {
        $this->opt = $opt;
    }

    /********** Public API **********/

    /**
     * Generic field callback.  Checks for some things then dispatches the call
     * to an appropriate method.
     *
     * @since   1.0
     * @access  public
     * @return  null
     */
    public function cb($args)
    {
        $type = isset($args['type']) ? $args['type'] : 'text_input';
        $cls = isset($args['class']) ? $args['class'] : 'widefat';
        $value = isset($args['value']) ? $args['value'] : '';
        $key = isset($args['key']) ? $args['key'] : false;

        if(!$key)
            $this->error(__('Set a key for this field', 'pmgcore'));
        elseif(method_exists($this, $type))
            $this->$type($value, $key, $cls, $args);
        else
            $this->error(__('Invalid field type', 'pmgcore'));
    }

    /**
     * Add a field.
     *
     * @since   1.0
     * @access  public
     * @param   string $key The field key
     * @param   array $args The field arguments.
     * @return  null
     */
    public function add_field($key, $args=array())
    {
        $args = wp_parse_args($args, array(
            'type'      => 'text_input', // the field type
            'class'     => 'widefat', // field class
            'value'     => '', // The value of the field
            'cleaners'  => array('esc_attr'), // Used in the `save` function
            'section'   => 'default', // the setting section.
            'page'      => isset($this->page) ? $this->page : '', // the settings page
            'label'     => '',
        ));

        $args['key'] = $key;

        $this->fields[$key] = $args;
    }

    /**
     * Add a settings section. If $help is given, it will be put below the
     * section title.
     *
     * @since   1.0
     * @access  public
     * @param   string $key The section key.
     * @param   string $title The title of the section
     * @param   string $help (optional) Help text to display.
     * @return  void
     */
    public function add_section($key, $args)
    {
        $args = wp_parse_args($args, array(
            'title'     => '',
            'help'      => '',
        ));

        $this->sections[$key] = array(
            'title' => $args['title'],
            'help'  => $args['help'],
        );
    }

    /**
     * Clean and validate the data uses the `cleaners` specified for each
     * field.
     *
     * @since   1.0
     * @access  public
     * @param   array $dirty The data to validate/clean
     * @return  array
     */
    public function validate($dirty)
    {
        $clean = array();
        foreach($this->fields as $key => $field)
        {
            if(isset($dirty[$key]) && $dirty[$key])
            {
                if('checkbox' == $field['type'])
                {
                    $clean[$key] = static::CHECK_ON;
                }
                elseif('editor' == $field['type'])
                {
                    $clean[$key] = current_user_can('unfiltered_html') ?
                        $dirty[$key] : wp_filter_post_kses($dirty[$key]);
                }
                elseif('multiselect' == $field['type'])
                {
                    $clean[$key] = array();
                    foreach((array)$dirty[$key] as $val)
                    {
                        foreach($field['cleaners'] as $cb)
                            $val = call_user_func($cb, $val);

                        $clean[$key][] = $val;
                    }
                }
                else
                {
                    $val = $dirty[$key];
                    foreach($field['cleaners'] as $cb)
                        $val = call_user_func($cb, $val);
                    $clean[$key] = $val;
                }
            }
            elseif('checkbox' == $field['type'])
            {
                $clean[$key] = static::CHECK_OFF;
            }
        }
        return $clean;
    }

    public function get_opt()
    {
        return $this->opt;
    }

    public function get_fields()
    {
        return $this->fields;
    }

    /********** Field Callbakcs **********/

    protected function input($type, $value, $key, $cls='widefat')
    {
        $name = $this->gen_name($key);
        printf(
            '<input type="%1$s" class="%2$s" name="%3$s" id="%3$s" value="%4$s" %5$s />',
            esc_attr($type),
            esc_attr($cls),
            esc_attr($name),
            esc_attr('checkbox' == $type ? $key : $value),
            'checkbox' == $type ? checked(static::CHECK_ON, $value, false) : ''
        );
    }

    protected function text_input($value, $key, $cls='widefat')
    {
        $this->input('text', $value, $key, $cls);
    }

    protected function password_input($value, $key, $cls='widefat')
    {
        $this->input('password', $value, $key, $cls);
    }

    protected function checkbox($value, $key, $cls=null, $args)
    {
        $this->input('checkbox', $value, $key, '', $args);
    }

    protected function textarea($value, $key, $cls='widefat', $args)
    {
        printf(
            '<textarea id="%1$s" name="%1$s" class="%2$s" %3$s>%4$s</textarea>',
            esc_attr($this->gen_name($key)),
            esc_attr($cls),
            isset($args['rows']) ? 'rows="' . absint($args['rows']) . '"' : '',
            !empty($args['raw']) ? $value : esc_textarea($value)
        );
    }

    protected function select($value, $key, $cls='', $args)
    {
        $options = isset($args['options']) ? $args['options'] : array();
        $is_multi = !empty($args['multi']);
        $name = $this->gen_name($key);

        if($is_multi)
            $name .= '[]';

        printf(
            '<select id="%1$s" name="%1$s" class="%2$s" %3$s>',
            esc_attr($name),
            esc_attr($cls),
            $is_multi ? 'multiple="mulitple"' : ''
        );
        foreach($options as $val => $label)
        {
            if($is_multi)
            {
                if(in_array($val, (array)$value))
                    $s = 'selected="selected"';
                else
                    $s = '';
            }
            else
            {
                $s = selected($val, $value, false);
            }

            printf(
                '<option value="%1$s" %2$s>%3$s</option>',
                esc_attr($val),
                $s,
                esc_html($label)
            );
        }
        echo '</select>';
    }

    protected function multiselect($value, $key, $cls, $args)
    {
        $args['multi'] = true;
        $this->select($value, $key, $cls, $args);
    }

    protected function radio($value, $key, $cls, $args)
    {
        $options = isset($args['options']) ? $args['options'] : array();
        $name = $this->gen_name($key);

        foreach($options as $val => $label)
        {
            echo '<p>';
            printf(
                '<label for="%1$s[%2$s]"><input type="radio" name="%1$s" '.
                'id="%1$s[%2$s]" value="%2$s" %3$s /> %4$s</label>',
                esc_attr($name),
                esc_attr($val),
                checked($value, $val, false),
                esc_html($label)
            );
            echo '</p>';
        }
    }

    protected function editor($value, $key, $cls, $args)
    {
        $name = $this->gen_name($key);

        $e_args = array();

        if(isset($args['rows']))
            $e_args['textarea_rows'] = $args['rows'];

        wp_editor($value, $name, $e_args);
    }

    /**
     * Spit out a label.
     *
     * @since   1.0
     * @access  protected
     * @param   string $id The value to put in the `for` attr
     * @param   string $label The actual label
     */
    protected function label($id, $label)
    {
        printf(
            '<label for="%1$s">%2$s</label>',
            esc_attr($id),
            esc_html($label)
        );
    }

    /********** Internals **********/

    protected function error($msg)
    {
        echo esc_html($msg);
    }

    protected function gen_name($key)
    {
        return is_null($this->opt) ? $key : sprintf('%s[%s]', $this->opt, $key);
    }
} // end FieldFactory

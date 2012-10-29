<?php
/**
 * MetaFields with a different render implementation.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core\Fields;

!defined('ABSPATH') && exit;

use PMG\Core\Meta\MetaInterface;

class MetaBoxFields extends MetaFields implements FieldInterface
{
    /**
     * Render the fields.
     *
     * @since   1.0
     * @access  public
     * @return  null
     */
    public function render()
    {
        if(func_num_args() >= 2)
        {
            $id = func_get_arg(0);
            $m = func_get_arg(1);

            if(!is_subclass_of($m, 'PMG\\Core\\Meta\\MetaInterface'))
            {
                trigger_error(
                    __('Invalid meta interface', 'pmgcore'), E_USER_WARNING);
                return;
            }
        }
        else
        {
            return; // bail
        }

        $this->setup_values($id, $m);

        echo '<ul class="pmgcore-tab-nav">';
        foreach($this->sections as $s => $section)
        {
            printf(
                '<li><a href="#" data-id="%s" data-group="%s">%s</a></li>',
                esc_attr($this->gen_id($s)),
                esc_attr($this->opt),
                esc_html($section['title'])
            );
        }
        echo '</ul>';

        foreach($this->sections as $s => $section)
        {
            $fields = wp_list_filter($this->fields, array('section' => $s));

            echo '<div id="' . $this->gen_id($s) . '" class="pmg-core-tab ' . esc_attr($this->opt) . '">';

            echo '<table class="form-table">';
            foreach($fields as $key => $field)
            {
                echo '<tr>';

                if('editor' == $field['type'])
                {
                    echo '<td colspan="2">';

                    echo '<h5>';
                    $this->label($this->gen_name($key), $field['label']);
                    echo '</h5>';

                    $this->cb($field);

                    echo '</td>';
                }
                else
                {
                    echo '<th scope="row">';
                    $this->label($this->gen_name($key), $field['label']);
                    echo '</th>';

                    echo '<td>';
                    $this->cb($field);
                    echo '</td>';
                }

                echo '</tr>';
            }
            echo '</table>';

            echo '</div>';
        }
    }
} // end MetaFields

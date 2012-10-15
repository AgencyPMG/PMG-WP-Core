<?php
/**
 * Any fields that will use something that implements a MetaInterface
 * object to populate field data.  Might be usermeta or otherwise.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core\Fields;

!defined('ABSPATH') && exit;

class MetaFields extends FieldBase
{
    /**
     * Render the fields.  For use outside the settings api.
     *
     * @since   1.0
     * @access  public
     * @return  null
     */
    public function render($id)
    {
        if($this->type)
            $this->setup_values($id, $m);

        echo '<table class="form-table">';
        foreach($this->fields as $key => $field)
        {
            echo '<tr>';

            if('editor' == $field['type'])
            {
                echo '<td colspan="2">';

                echo '<h4>';
                $this->label($this->gen_name($key), $field['label']);
                echo '</h4>';

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
    }

    /**
     * Save the values using $this->meta.
     *
     * @since   1.0
     * @access  public
     * @param   array $values The values to save
     * @param   int $id The object ID
     * @return  null
     */
    public function save($id, $values, Meta $m)
    {
        $values = $this->validate($values);

        foreach($this->fields as $key => $field)
        {
            if(isset($values[$key]))
            {
                $m->save($id, $key, $values[$key]);
            }
            else
            {
                // not in the validated array, assume it needs to be deleted
                $m->delete($id, $key);
            }
        }
    }

    /**
     * Fetch field values and set them for rendering.
     *
     * @since   1.0
     * @access  protected
     * @return  null
     */
    protected function setup_values($id, Meta $m)
    {
        foreach($this->fields as $key => $field)
        {
            $this->fields[$key]['value'] = $m->get($id, $key);
        }
    }
} // end MetaFactory

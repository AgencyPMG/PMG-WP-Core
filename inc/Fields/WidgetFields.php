<?php
/**
 * Handles widget fields and the like.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     http://opensource.org/licenses/MIT MIT
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core\Fields;

!defined('ABSPATH') && exit;

class WidgetFields extends FieldBase implements FieldInterface
{
    private $widget = null;

    public function render()
    {
        if(func_num_args() >= 2)
        {
            $instance = func_get_arg(0);
            $this->widget = func_get_arg(1);
        }
        else
        {
            return;
        }

        $this->setup_values($instance);

        foreach($this->fields as $key => $field)
        {
            echo '<p>';
            $this->label($this->gen_name($key), $field['label']);
            echo '<br />';
            $this->cb($field);
            echo '</p>';
        }
    }

    protected function setup_values($ins)
    {
        foreach($this->fields as $key => $field)
        {
            $this->fields[$key]['value'] = isset($ins[$key]) ? $ins[$key] : '';
        }
    }

    public function gen_name($key)
    {
        return $this->widget->get_field_name($key);
    }
}

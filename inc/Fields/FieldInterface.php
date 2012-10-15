<?php
/**
 * Interface for the various field classes.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core\Fields;

!defined('ABSPATH') && exit;

interface FieldInterface
{
    public function cb($args);

    public function add_field($key, $args);

    public function add_section($key, $args);

    public function render();
}

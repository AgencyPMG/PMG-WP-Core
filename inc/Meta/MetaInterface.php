<?php
/**
 * Interface for metadata api.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core\Meta;

!defined('ABSPATH') && exit;

interface MetaInterface
{
    public function __construct($type, $prefix);

    public function get_key($key);

    public function get($id, $key, $default);

    public function save($id, $key, $val);

    public function delete($id, $key, $val);

    public function delete_all($id, $key, $val);
}

<?php
/**
 * Fake having a taxonomy meta table with options.  This won't be used if we've
 * created a custom termmeta table.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core\Meta;

!defined('ABSPATH') && exit;

class TermMeta implements MetaInterface
{
    const ALL = '_pmgcore_delete_all';

    private $prefix;

    private $type;

    private $to_delete = array();

    private $to_save = array();

    private $did_save = false;

    public function __construct($type, $prefix)
    {
        $this->type = $type;
        $this->prefix = $prefix;
        add_action('edit_term', array($this, '_save'), 100);
        add_action('shutdown', array($this, 'maybe_save'));
    }

    public function get($id, $key, $default='')
    {
        $m = $this->get_option();
        return !empty($m[$id][$key]) ? $m[$id][$key] : $default;
    }

    public function save($id, $key, $val)
    {
        if(!isset($this->to_save[$id]))
            $this->to_save[$id] = array();

        $this->to_save[$id][$key] = $val;
    }

    public function delete($id, $key, $val='')
    {
        if(!isset($this->to_delete[$id]))
            $this->to_delete[$id] = array();

        $this->to_delete[$id][$key] = $val;
    }

    public function delete_all($id, $key, $val='')
    {
        if(!isset($this->to_delete[$id]))
            $this->to_delete[$id] = array();

        $this->to_delete[$id][self::ALL] = true;
    }

    /**
     * Hooked into `edit_term`.  Saves the changes we made to meta.
     *
     * @since   1.0
     * @uses    get_option
     * @uses    update_option
     */
    public function _save()
    {
        if(empty($this->to_save) && empty($this->to_delete))
            return;

        $m = $this->get_option();

        // array_merge_rescursive screws up numeric keys.
        foreach($this->to_save as $id => $fields)
        {
            if(!isset($m[$id]))
                $m[$id] = array();

            foreach($fields as $k => $v)
                $m[$id][$k] = $v;
        }

        foreach($this->to_delete as $id => $fields)
        {
            // handle delete alls.
            if(!empty($fields[self::ALL]))
            {
                $m[$id] = array();
                continue;
            }

            foreach($fields as $k => $v)
            {
                if(isset($m[$id][$k]) && (!$v || $m[$id][$k] == $v))
                    unset($m[$id][$k]);
            }
        }

        update_option("pmgcore_termmeta_{$this->prefix}",  $m);

        $this->did_save = true;
    }

    /**
     * Hooked into shutdown.  Mean to catch changes that were main on a regular
     * page load.  This won't fire is self::_save has been called once already.
     *
     * @since   1.0
     * @access  public
     * @return  void
     */
    public function maybe_save()
    {
        if($this->did_save)
            return;

        $this->_save();
    }

    private function get_option()
    {
        return get_option("pmgcore_termmeta_{$this->prefix}", array());
    }
}

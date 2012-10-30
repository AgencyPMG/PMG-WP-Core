<?php
/**
 * Automates the addition of fields on user pages.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core;

!defined('ABSPATH') && exit;

use PMG\Core\Fields\FieldInterface;
use PMG\Core\Meta\MetaInterface;

class UserBox
{
    /**
     * Name of the parent project.
     *
     * @since   1.0
     * @access  private
     * @var     string
     */
    private $proj;

    /**
     * The capability required to view the fields.
     *
     * @since   1.0
     * @access  private
     * @var     string
     */
    private $cap = 'edit_user';

    /**
     * Container for the field factory.
     *
     * @since   1.0
     * @access  private
     * @var     FieldInterface
     */
    private $fi = null;

    /**
     * Container for the meta interface object.
     *
     * @since   1.0
     * @access  private
     * @var     MetaInterface
     */
    private $mi = null;

    /**
     * Constructor.
     *
     * @since   1.0
     * @access  public
     * @param   string $proj The parent project name
     * @param   FieldInterface $f The field interface that takes care of
     *          rendering the actual fields.
     * @param   array|string $types (optional) The array of post types to which
     *          this meta box is to be added.  Defaults to all public post
     *          types
     * @param   array $opts Additional options -- title, priority, context
     * @return  void
     */
    public function __construct($proj, FieldInterface $f, MetaInterface $m, $cap='edit_user')
    {
        $this->fi = $f;
        $this->mi = $m;

        $this->proj = $proj;
        $this->cap = $cap;

        add_action('edit_user_profile_update', array($this, 'save'));
        add_action('personal_options_update', array($this, 'save'));
        add_action('show_user_profile', array($this, 'render'));
        add_action('edit_user_profile', array($this, 'render'));
    }

    public function render($user)
    {
        if(!current_user_can($this->cap, $user->ID))
            return;

        $nn = $this->get_nonce();
        wp_nonce_field($nn . $user->ID, $nn, false);

        $this->fi->render($user->ID, $this->mi);
    }

    public function save($user_id)
    {
        $nn = $this->get_nonce();
        if(
            !isset($_POST[$nn]) ||
            !wp_verify_nonce($_POST[$nn], $nn . $user_id)
        ) return;

        if(!current_user_can('edit_user', $user_id))
            return;

        $k = $this->fi->get_opt();

        $vals = $this->fi->validate(isset($_POST[$k]) ? $_POST[$k] : array());

        foreach($this->fi->get_fields() as $key => $field)
        {
            if(!empty($vals[$key]))
            {
                $this->mi->save($user_id, $key, $vals[$key]);
            }
            else
            {
                $this->mi->delete($user_id, $key);
            }
        }
    }

    private function get_nonce()
    {
        return $this->proj . $this->fi->get_opt() . '_nonce';
    }
}

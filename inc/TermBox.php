<?php
/**
 * Automates the addition of fields to term pages.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     http://opensource.org/licenses/MIT MIT
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core;

!defined('ABSPATH') && exit;

use PMG\Core\Fields\FieldInterface;
use PMG\Core\Meta\MetaInterface;

class TermBox
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
     * Taxonomies on which to place these fields.
     *
     * @since   1.0
     * @access  private
     * @var     array
     */
    private $taxonomes = array();

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
    public function __construct($proj, FieldInterface $f, MetaInterface $m, $tax=array())
    {
        $this->fi = $f;
        $this->mi = $m;

        $this->proj = $proj;
        $this->taxonomies = $tax;
        add_action('init', array($this, '_setup'), 100);
    }

    /**
     * Hooked into init. Because there's no generic hook that fires on all
     * taxonomies, we need to wait until init when the taxonomies are
     * registered. All of this to get around not forcing uses to enter
     * taxonomies every time.
     *
     * @since   1.0
     * @access  public
     * @uses    add_action
     * @return  void
     */
    public function _setup()
    {
        foreach($this->get_taxonomies() as $tax)
        {
            add_action("{$tax}_edit_form", array($this, 'render'), 10, 2);
        }
        add_action('edit_term', array($this, 'save'), 10, 3);
    }

    public function render($term, $tax)
    {
        if(!current_user_can(get_taxonomy($tax)->cap->edit_terms, $term->term_id))
            return;

        $nn = $this->get_nonce();
        wp_nonce_field($nn . $term->term_id, $nn, false);

        $this->fi->render($term->term_id, $this->mi);
    }

    public function save($term_id, $tt_id, $tax)
    {
        if(!in_array($tax, $this->get_taxonomies()))
            return;


        $nn = $this->get_nonce();
        if(
            !isset($_POST[$nn]) ||
            !wp_verify_nonce($_POST[$nn], $nn . $term_id)
        ) return;

        if(!current_user_can(get_taxonomy($tax)->cap->edit_terms, $term_id))
            return;

        $k = $this->fi->get_opt();

        $vals = $this->fi->validate(isset($_POST[$k]) ? $_POST[$k] : array());

        foreach($this->fi->get_fields() as $key => $field)
        {
            if(!empty($vals[$key]))
            {
                $this->mi->save($term_id, $key, $vals[$key]);
            }
            else
            {
                $this->mi->delete($term_id, $key);
            }
        }
    }

    private function get_nonce()
    {
        return $this->proj . $this->fi->get_opt() . '_nonce';
    }

    private function get_taxonomies()
    {
        if(empty($this->taxonomies))
        {
            $this->taxonomies = array_keys(get_taxonomies(
                array('show_ui' => true), 'names'));
        }

        return $this->taxonomies;
    }
}

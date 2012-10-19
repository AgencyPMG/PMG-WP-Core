<?php
/**
 * Meta box base class.  Helps automate the create of meta boxes for post types.
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

class MetaBox
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
     * The post types on which this meta box will go
     *
     * @since   1.0
     * @access  priavte
     * @var     array
     */
    private $types = array();

    /**
     * Title of the meta box.
     *
     * @since   1.0
     * @access  private
     * @var     string
     */
    private $title = '';

    /**
     * Context for the meta box.
     *
     * @since   1.0
     * @access  private
     * @var     string ('normal', 'advanced' or 'side')
     */
    private $context = 'normal';

    /**
     * priority of the meta box
     *
     * @since   1.0
     * @access  private
     * @var     string ('high', 'core', 'default', 'low')
     */
    private $prio = 'high';

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
    public function __construct($proj, FieldInterface $f, MetaInterface $m, $opts=array(), $types=array())
    {
        $this->fi = $f;
        $this->mi = $m;

        $this->proj = $proj;
        $this->types = $types;
        $this->title = !empty($opts['title']) ? $opts['title'] : $proj;
        $this->prio = !empty($opts['priority']) ? $opts['priority'] : 'high';
        $this->context = !empty($opts['context']) ? $opts['context'] : 'normal';

        add_action('add_meta_boxes', array($this, 'box'));
        add_action('save_post', array($this, 'save'), 10, 2);
    }

    public function box($post_type)
    {
        if(!in_array($post_type, $this->get_types()))
            return;

        add_meta_box(
            $this->proj . $this->fi->get_opt(),
            $this->title,
            array($this, 'box_cb'),
            $post_type,
            $this->context,
            $this->prio
        );
    }

    public function box_cb($post)
    {
        $nn = $this->get_nonce();
        wp_nonce_field($nn . $post->ID, $nn, false);
        $this->fi->render($post->ID, $this->mi);
    }

    public function save($post_id, $post)
    {
        if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        if(!in_array($post->post_type, $this->get_types()))
            return;

        $nn = $this->get_nonce();
        if(
            !isset($_POST[$nn]) ||
            !wp_verify_nonce($_POST[$nn], $nn . $post_id)
        ) return;

        $cap = get_post_type_object($post->post_type)->cap->edit_post;
        if(!current_user_can($cap, $post_id))
            return;

        $k = $this->fi->get_opt();

        $vals = $this->fi->validate(isset($_POST[$k]) ? $_POST[$k] : array());

        foreach($this->fi->get_fields() as $key => $field)
        {
            if(!empty($vals[$key]))
            {
                $this->mi->save($post_id, $key, $vals[$key]);
            }
            else
            {
                $this->mi->delete($post_id, $key);
            }
        }
    }

    private function get_nonce()
    {
        return $this->proj . $this->fi->get_opt() . '_nonce';
    }

    private function get_types()
    {
        if(empty($this->types))
            $this->types = array_keys(
                get_post_types(array('public' => true), 'names')
            );

        return $this->types;
    }
}

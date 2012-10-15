<?php
/**
 * Programmatically set upload path and url.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core\Functionality;

!defined('ABSPATH') && exit;

use PMG\Core\PluginBase;

class Uploads extends PluginBase
{
    public function _setup()
    {
        add_filter('pre_option_upload_path', array($this, 'upload_path'));
        add_filter('pre_option_upload_url_path', array($this, 'upload_url'));
    }

    public function upload_path($p)
    {
        return trailingslashit($_SERVER['DOCUMENT_ROOT']) . 'uploads';
    }

    public function upload_url($u)
    {
        return preg_replace('#^https?://#ui', '//', WP_HOME . '/uploads');
    }
} // end Uploads

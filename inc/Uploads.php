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

namespace PMG\Core;

!defined('ABSPATH') && exit;

class Uploads extends PluginBase
{
    public function pre_option_upload_path($p)
    {
        return trailingslashit($_SERVER['DOCUMENT_ROOT']) . 'uploads';
    }

    public function pre_option_upload_url_path($u)
    {
        return preg_replace('#^https?://#ui', '//', WP_HOME . '/uploads');
    }
} // end Uploads

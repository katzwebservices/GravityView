<?php
/**
 * Enhance Members compatibility with GravityView.
 *
 * @file      class-gravityview-plugin-hooks-members.php
 *
 * @license   GPL2+
 * @author    GravityView <hello@gravityview.co>
 *
 * @link      https://gravityview.co
 *
 * @copyright Copyright 2022, Katz Web Services, Inc.
 *
 * @since 2.14.1
 */

/**
 * @inheritDoc
 */
class GravityView_Plugin_Hooks_Members extends GravityView_Plugin_and_Theme_Hooks
{
    /**
     * @var string Check for the Members_Plugin class
     */
    protected $class_name = 'Members_Plugin';

    protected $style_handles = [
        'members-edit-post',
        'members-pointers',
        'members-admin',
        'thickbox',
        'editor-buttons',
    ];

    protected $script_handles = [
        'members-edit-post',
        'members-pointers',
        'wp-tinymce',
        'quicktags',
        'buttons',
        'thickbox',
        'post',
        'jquery-ui-autocomplete',
        'wplink',
        'wp-embed',
        'media-upload',
        'editor',
        'members-block-permissions-editor',
        'postbox',
        'wp-util',
        'wp-pointer',
    ];
}

new GravityView_Plugin_Hooks_Members();

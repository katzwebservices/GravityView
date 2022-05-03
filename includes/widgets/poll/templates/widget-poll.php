<?php
/**
 * Display the Poll widget.
 *
 * @file ../class-gravityview-widget-poll.php
 */
$gravityview_view = GravityView_View::getInstance();

foreach ($gravityview_view->poll_fields as $form_id => $poll_field) {

    /**
     * Merge tag is already generated by the class.
     * Access array of settings to generate your own using the $gravityview_view->poll_settings variable.
     */
    $merge_tag = $gravityview_view->poll_merge_tag;

    echo GFCommon::replace_variables($merge_tag, GFAPI::get_form($form_id), ['id' => 0]);
}

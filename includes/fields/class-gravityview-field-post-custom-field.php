<?php
/**
 * @file class-gravityview-field-post-custom-field.php
 */
class GravityView_Field_Post_Custom_Field extends GravityView_Field
{
    public $name = 'post_custom_field';

    public $is_searchable = true;

    /**
     * @var array Custom fields are text, but can be any format (including JSON)
     */
    public $search_operators = ['contains', 'is', 'isnot'];

    /**
     * @see GF_Field_Post_Custom_Field
     */
    public $_gf_field_class_name = 'GF_Field_Post_Custom_Field';

    public $group = 'post';

    public $icon = 'dashicons-admin-generic';

    public function __construct()
    {
        $this->label = esc_html__('Post Custom Field', 'gravityview');
        parent::__construct();

        $this->add_hooks();
    }

    /**
     * Add hooks for the field.
     *
     * @since 1.17
     *
     * @return void
     */
    private function add_hooks()
    {
        add_filter('gravityview/edit_entry/field_value_post_custom_field', [$this, 'edit_entry_field_value'], 10, 2);
    }

    /**
     * Fix "List" Field Type pre-population of content in Edit Entry mode.
     *
     * @since 1.17
     *
     * @param mixed    $field_value field value used to populate the input
     * @param GF_Field $field       Gravity Forms field object
     *
     * @return mixed If a List input for Custom Field, returns JSON-decoded value. Otherwise, original value.
     */
    public function edit_entry_field_value($field_value, $field)
    {
        if ('list' === $field->inputType) {
            $field_value = is_string($field_value) ? json_decode($field_value, true) : $field_value;

            if (!is_array($field_value)) {
                gravityview()->log->error('"List" Custom Field value not an array or string.', ['data' => compact('field_value', 'field')]);
            }
        }

        return $field_value;
    }
}

new GravityView_Field_Post_Custom_Field();

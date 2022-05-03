<?php
/**
 * @file class-gravityview-field-payment-date.php
 *
 * @since 1.16
 */
class GravityView_Field_Payment_Date extends GravityView_Field_Date_Created
{
    public $name = 'payment_date';

    public $is_searchable = true;

    public $search_operators = ['less_than', 'greater_than', 'is', 'isnot'];

    public $group = 'pricing';

    public $_custom_merge_tag = 'payment_date';

    public $icon = 'dashicons-cart';

    /**
     * GravityView_Field_Date_Created constructor.
     */
    public function __construct()
    {

        // Constructor before the variables because the class extends Date_Created
        parent::__construct();

        $this->label = esc_html__('Payment Date', 'gravityview');
        $this->description = esc_html__('The date the payment was received.', 'gravityview');

        add_filter('gravityview/field/payment_date/value', [$this, 'get_value'], 10, 6);
    }

    /**
     * Filter the value of the field, future.
     *
     * @since 2.0
     *
     * @param mixed       $value   The value of the field.
     * @param \GV\Field   $field   The field as seen by future.
     * @param \GV\View    $view    The view requested in.
     * @param \GV\Source  $source  The data source (form).
     * @param \GV\Entry   $entry   The entry.
     * @param \GV\Request $request The request context.
     *
     * @return mixed $value The filtered value.
     */
    public function get_value($value, $field, $view, $source, $entry, $request)
    {
        /** Supply the raw value instead whatever may have been filtered before. */
        $raw_value = empty($entry[$this->name]) ? null : $entry[$this->name];

        return $this->get_content($value, $entry->as_entry(), $field->as_configuration(), ['value' => $raw_value]);
    }
}

new GravityView_Field_Payment_Date();

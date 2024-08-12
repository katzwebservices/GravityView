<?php
/**
 * @file class-gravityview-field-time.php
 * @package GravityView
 * @subpackage includes\fields
 */

/**
 * Add custom options for date fields
 */
class GravityView_Field_Time extends GravityView_Field {

	var $name = 'time';

	var $is_searchable = true;

	var $search_operators = array( 'is', 'isnot', 'greater_than', 'less_than' );

	/** @see GF_Field_Time */
	var $_gf_field_class_name = 'GF_Field_Time';

	var $group = 'advanced';

	var $icon = 'dashicons-clock';

	/**
	 * @internal Do not define. This is overridden by the class using a filter.
	 * @todo Fix using variable for time field
	 */
	var $is_numeric;

	/**
	 * @var string The part of the Gravity Forms query that's modified to enable sorting by time. `value` gets replaced.
	 * @since 1.14
	 */
	const GF_SORTING_SQL = 'SELECT 0 as query, lead_id as id, value';

	/**
	 * @var string Used to implode and explode the custom sort key for query modification.
	 * @since 1.14
	 */
	private $_sort_divider = '|:time:|';

	/**
	 * @var string Used to store the time format for the field ("12" or "24") so it can be used in the query filter
	 * @since 1.14
	 */
	private $_time_format = null;

	/**
	 * @var string Used to store the date format for the field, based on the input being displayed, so it can be used in the query filter
	 * @since 1.14
	 */
	private $_date_format = null;

	/**
	 * GravityView_Field_Time constructor.
	 */
	public function __construct() {

		$this->label = esc_html__( 'Time', 'gk-gravityview' );

		parent::__construct();

		add_filter( 'gravityview/sorting/time', array( $this, 'modify_sort_id' ), 10, 2 );

		add_filter( 'gravityview_search_criteria', array( $this, '_maybe_filter_gravity_forms_query' ), 10, 4 );

	}


	/**
	 * Modify the sort key for the time field so it can be parsed by the query filter
	 *
	 * @see _modify_query_sort_by_time_hack
	 *
	 * @since 1.14
	 * @param string $sort_field_id Existing sort field ID (like "5")
	 * @param int    $form_id Gravity Forms Form ID being sorted
	 *
	 * @return string Modified sort key imploded with $_sort_divider, like `5|:time:|12|:time:|h:i A`
	 */
	public function modify_sort_id( $sort_field_id, $form_id ) {

		$time_format = self::_get_time_format_for_field( $sort_field_id, $form_id );

		$date_format = self::date_format( $time_format, $sort_field_id );

		// Should look something like `5|:time:|12|:time:|h:i A`
		$new_sort_field_id = implode( $this->_sort_divider, array( $sort_field_id, $time_format, $date_format ) );

		return $new_sort_field_id;
	}

	/**
	 * If the sorting key matches the key set in modify_sort_id(), then modify the Gravity Forms query SQL
	 *
	 * @since 1.14
	 * @see modify_sort_id()
	 *
	 * @param array $criteria Search criteria used by GravityView
	 * @param array $form_ids Forms to search
	 * @param int   $view_id ID of the view being used to search
	 *
	 * @return array $criteria If a match, the sorting will be updated to set `is_numeric` to true and make sure the field ID is an int
	 */
	public function _maybe_filter_gravity_forms_query( $criteria, $form_ids, $view_id ) {

		// If the search is not being sorted, return early
		if ( empty( $criteria['sorting']['key'] ) ) {
			return $criteria;
		}

		$pieces = explode( $this->_sort_divider, $criteria['sorting']['key'] );

		/**
		 * If the sort key does not match the key set in modify_sort_id(), do not modify the Gravity Forms query SQL
		 *
		 * @see modify_sort_id()
		 */
		if ( empty( $pieces[1] ) ) {
			return $criteria;
		}

		// Pass these to the _modify_query_sort_by_time_hack() method
		$this->_time_format = $pieces[1];
		$this->_date_format = $pieces[2];

		// Remove fake input IDs (5.1 doesn't exist. Use 5)
		$criteria['sorting']['key'] = floor( $pieces[0] );

		/**
		 * Make sure sorting is numeric (# of seconds). IMPORTANT.
		 *
		 * @see GVCommon::is_field_numeric() is_numeric should also be set here
		 */
		$criteria['sorting']['is_numeric'] = true;

		// Modify the Gravity Forms WP Query
		add_filter( 'query', array( $this, '_modify_query_sort_by_time_hack' ) );

		return $criteria;
	}

	/**
	 * Modify Gravity Forms query SQL to convert times to numbers
	 * Gravity Forms couldn't sort by time...until NOW
	 *
	 * @since 1.14
	 * @param string $query MySQL query
	 *
	 * @return string Modified query, if the query matches the expected Gravity Forms SQL string used for sorting time fields. Otherwise, original query.
	 */
	function _modify_query_sort_by_time_hack( $query ) {

		/**
		 * If this is a Gravity Forms entry selection sorting query, generated by sort_by_field_query(),
		 * then we want to modify the query.
		 *
		 * @see GFFormsModel::sort_by_field_query()
		 */
		if ( strpos( $query, self::GF_SORTING_SQL ) > 0 ) {

			if ( '24' === $this->_time_format ) {
				$sql_str_to_date = "STR_TO_DATE( `value`, '%H:%i' )";
			} else {
				$sql_str_to_date = "STR_TO_DATE( `value`, '%h:%i %p' )";
			}

			switch ( $this->_date_format ) {
				case 'h':
				case 'H':
					$modification = "TIME_FORMAT( {$sql_str_to_date}, '%H' )";
					break;
				case 'i':
					$modification = "TIME_FORMAT( {$sql_str_to_date}, '%i' )";
					break;
				case 'H:i':
				case 'h:i A':
				default:
					$modification = "TIME_TO_SEC( {$sql_str_to_date} )";
			}

			/**
			 * Convert the time (12:30 pm) to the MySQL `TIME_TO_SEC()` value for that time (45000)
			 * This way, Gravity Forms is able to sort numerically.
			 */
			$replacement_query = str_replace( 'value', "{$modification} as value", self::GF_SORTING_SQL );

			/**
			 * Replace it in the main query
			 */
			$query = str_replace( self::GF_SORTING_SQL, $replacement_query, $query );

			/**
			 * REMOVE the Gravity Forms WP Query modifications!
			 */
			remove_filter( 'query', array( $this, '_modify_query_sort_by_time_hack' ) );
		}

		return $query;
	}


	public function field_options( $field_options, $template_id, $field_id, $context, $input_type, $form_id ) {

		// Set variables
		parent::field_options( $field_options, $template_id, $field_id, $context, $input_type, $form_id );

		if ( 'edit' === $context ) {
			return $field_options;
		}

		/**
		 * Set default date format based on field ID and Form ID
		 */
		add_filter( 'gravityview_date_format', array( $this, '_filter_date_display_date_format' ) );

		$this->add_field_support( 'date_display', $field_options );

		remove_filter( 'gravityview_date_format', array( $this, '_filter_date_display_date_format' ) );

		return $field_options;
	}

	/**
	 * Return the field's time format by fetching the form ID and checking the field settings
	 *
	 * @since 1.14
	 *
	 * @return string Either "12" or "24". "12" is default.
	 */
	private function _get_time_format() {
		global $post;

		$current_form = isset( $_POST['form_id'] ) ? intval( $_POST['form_id'] ) : gravityview_get_form_id( $post->ID );

		return self::_get_time_format_for_field( $this->_field_id, $current_form );
	}

	/**
	 * Return the field's time format by fetching the form ID and checking the field settings
	 *
	 * @since 1.14
	 *
	 * @param string $field_id ID for Gravity Forms time field
	 * @param int    $form_id ID for Gravity Forms form
	 * @return string Either "12" or "24". "12" is default.
	 */
	public static function _get_time_format_for_field( $field_id, $form_id = 0 ) {

		// GF defaults to 12, so should we.
		$time_format = '12';

		if ( $form_id ) {
			$form = GVCommon::get_form( $form_id );

			if ( $form ) {
				$field = GFFormsModel::get_field( $form, floor( $field_id ) );
				if ( $field && $field instanceof GF_Field_Time ) {
					$field->sanitize_settings(); // Make sure time is set
					$time_format = $field->timeFormat;
				}
			}
		}

		return $time_format;
	}

	/**
	 * Modify the default PHP date formats used by the time field based on the field IDs and the field settings
	 *
	 * @since 1.14
	 *
	 * @return string PHP date() format text to to display the correctly formatted time value for the newly created field
	 */
	public function _filter_date_display_date_format() {

		$time_format = $this->_get_time_format();
		$field_id    = $this->_field_id;

		return self::date_format( $time_format, $field_id );
	}

	/**
	 * Get the default date format for a field based on the field ID and the time format setting
	 *
	 * @since 1.14

	 * @param string $time_format The time format ("12" or "24"). Default: "12" {@since 1.14}
	 * @param int    $field_id The ID of the field. Used to figure out full time/hours/minutes/am/pm {@since 1.14}
	 *
	 * @return string PHP date format for the time
	 */
	public static function date_format( $time_format = '12', $field_id = 0 ) {

		$field_input_id = gravityview_get_input_id_from_id( $field_id );

		$default = 'h:i A';

		// This doesn't take into account 24-hour
		switch ( $field_input_id ) {
			// Hours
			case 1:
				return ( '12' === $time_format ) ? 'h' : 'H';
				break;
			// Minutes
			case 2:
				return 'i';
				break;
			// AM/PM
			case 3:
				return 'A';
				break;
			// Full time field
			case 0:
				return ( '12' === $time_format ) ? $default : 'H:i';
				break;
		}

		return $default;
	}
}

new GravityView_Field_Time();

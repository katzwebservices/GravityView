<?php
/**
 * GravityView Frontend functions
 *
 * @package   GravityView
 * @license   GPL2+
 * @author    Katz Web Services, Inc.
 * @link      http://gravityview.co
 * @copyright Copyright 2014, Katz Web Services, Inc.
 *
 * @since 1.0.0
 */



class GravityView_frontend {

	function __construct() {

		// Shortcode to render view (directory)
		add_shortcode( 'gravityview', array( 'GravityView_frontend', 'render_view_shortcode' ) );
		add_action( 'init', array( 'GravityView_frontend', 'init_rewrite' ) );
		add_filter( 'query_vars', array( 'GravityView_frontend', 'add_query_vars_filter' ) );

		// Enqueue scripts and styles after GravityView_Template::register_styles()
		add_action( 'wp_enqueue_scripts', array( 'GravityView_frontend', 'add_scripts_and_styles' ), 20);

		add_filter( 'the_content', array( 'GravityView_frontend', 'insert_view_in_content' ) );
		add_filter( 'comments_open', array( 'GravityView_frontend', 'comments_open' ), 10, 2);

	}

	/**
	 * Register rewrite rules to capture the single entry view
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function init_rewrite() {

		global $wp_rewrite;

		if( !$wp_rewrite->using_permalinks() ) {
			return;
		}

		$endpoint = self::get_entry_var_name();

		//add_permastruct( "{$endpoint}", $endpoint.'/%'.$endpoint.'%/?', true);
		add_rewrite_endpoint( "{$endpoint}", EP_ALL );


	}

	/**
	 * Make the entry query var public to become available at WP_Query
	 *
	 * @access public
	 * @static
	 * @param array $vars
	 * @return $vars
	 */
	public static function add_query_vars_filter( $vars ){
		$vars[] = self::get_entry_var_name();
		return $vars;
	}


	/**
	 * Return the query var / end point name for the entry
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function get_entry_var_name() {
		return sanitize_title( apply_filters( 'gravityview_directory_endpoint', 'entry' ) );
	}


	/**
	 * Retrieve the default args for shortcode and theme function
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function get_default_args() {

		$defaults = array(
			'id' => '',
			'page_size' => '',
			'sort_field' => '',
			'sort_direction' => 'ASC',
			'start_date' => '',
			'end_date' => '',
			'class' => '',
			'search_value' => '',
			'search_field' => '',
		);

		return $defaults;
	}


	/**
	 * Callback function for add_shortcode()
	 *
	 * @access public
	 * @static
	 * @param mixed $atts
	 * @return void
	 */
	public static function render_view_shortcode( $atts ) {

		GravityView_Plugin::log_debug( '[render_view_shortcode] Init Shortcode. Attributes: ' . print_r( $atts, true ) );

		//confront attributes with defaults
		$args = shortcode_atts( self::get_default_args() , $atts, 'gravityview' );

		GravityView_Plugin::log_debug( '[render_view_shortcode] Init Shortcode. Merged Attributes: ' . print_r( $args, true ) );

		return self::render_view( $args );
	}

	/**
	 * Retrieves the shortcode atts
	 * @param  string $content
	 * @return mixed in case of success retrieve the shortcode attributes else, empty
	 */
	public static function get_view_shortcode_atts( $content ) {
		if ( false === strpos( $content, '[' ) ) {
			return array();
		}

		preg_match_all( '/' . get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER );
		if ( empty( $matches ) )
			return array();

		foreach ( $matches as $shortcode ) {
			if ( 'gravityview' === $shortcode[2] )
				return shortcode_parse_atts( $shortcode[3] );
		}

		return array();
	}




	/**
	 * In case View post is called directly, insert the view in the post content
	 *
	 * @access public
	 * @static
	 * @param mixed $content
	 * @return void
	 */
	public static function insert_view_in_content( $content ) {
		$post = get_post();

		if( 'gravityview' === get_post_type( $post ) ) {
			$content .= self::render_view( array( 'id' => $post->ID ) );
		}

		return $content;
	}

	public static function comments_open( $open, $post_id ) {

		$post = get_post( $post_id );

		if( 'gravityview' === get_post_type( $post ) ) {
			return false;
		}

		return $open;
	}


	/**
	 * Core function to render a View based on a set of arguments ($args):
	 *   $id - View id
	 *   $page_size - Page
	 *   $sort_field - form field id to sort
	 *   $sort_direction - ASC / DESC
	 *   $start_date - Ymd
	 *   $end_date - Ymd
	 *   $class - assign a html class to the view
	 *   $offset (optional) - This is the start point in the current data set (0 index based).
	 *
	 *
	 * @access public
	 * @static
	 * @param mixed $args
	 * @return void
	 */
	public static function render_view( $args ) {

		GravityView_Plugin::log_debug( '[render_view] Init View. Arguments: ' . print_r( $args, true ) );

		// validate attributes
		if( empty( $args['id'] ) ) {
			GravityView_Plugin::log_error( '[render_view] Returning; no ID defined.');
			return;
		}
		//get template settings
		$template_settings = get_post_meta( $args['id'], '_gravityview_template_settings', true );
		GravityView_Plugin::log_debug( '[render_view] Template Settings: ' . print_r( $template_settings, true ) );

		//Override shortcode args over View template settings
		$args = wp_parse_args( $args, $template_settings );

		GravityView_Plugin::log_debug( '[render_view] Arguments after merging with View settings: ' . print_r( $args, true ) );

		//extract( $args ); - no more extracts please!

		// It's password protected and you need to log in.
		if( post_password_required( $args['id'] ) ) {

			GravityView_Plugin::log_error( sprintf('[render_view] Returning: View %d is password protected.', $args['id'] ) );

			// If we're in an embed or on an archive page, show the password form
			if( get_the_ID() !== $args['id'] ) { return get_the_password_form(); }

			// Otherwise, just get outta here
			return;
		}

		// get form, fields and settings assign to this view
		$form_id = get_post_meta( $args['id'], '_gravityview_form_id', true );
		GravityView_Plugin::log_debug( '[render_view] Form ID: ' . print_r( $form_id, true ) );

		$template_id  = get_post_meta( $args['id'], '_gravityview_directory_template', true );
		GravityView_Plugin::log_debug( '[render_view] Template ID: ' . print_r( $template_id, true ) );

		$dir_fields = get_post_meta( $args['id'], '_gravityview_directory_fields', true );
		GravityView_Plugin::log_debug( '[render_view] Fields: ' . print_r( $dir_fields, true ) );

		// remove fields according to visitor visibility permissions (if logged-in)
		$dir_fields = self::filter_fields( $dir_fields );
		GravityView_Plugin::log_debug( '[render_view] Fields after visibility filter: ' . print_r( $dir_fields, true ) );

		// set globals for templating
		global $gravityview_view;
		$gravityview_view = new GravityView_View(array(
			'form_id' => $form_id,
			'view_id' => $args['id'],
			'fields'  => $dir_fields,
		));

		// check if user requests single entry
		$single_entry = get_query_var( self::get_entry_var_name() );

		if( empty( $single_entry ) ) {

			// user requested Directory View
			GravityView_Plugin::log_debug( '[render_view] Executing Directory View' );

			//fetch template and slug
			$view_slug =  apply_filters( 'gravityview_template_slug_'. $template_id, 'table', 'directory' );
			GravityView_Plugin::log_debug( '[render_view] View template slug: ' . print_r( $view_slug, true ) );

			$view_entries = self::get_view_entries( $args, $form_id, $template_settings );

			GravityView_Plugin::log_debug( '[render_view] Get Entries. Found: ' . print_r( $view_entries['count'], true ) .' entries');

			$gravityview_view->paging = $view_entries['paging'];
			$gravityview_view->context = 'directory';
			$sections = array( 'header', 'body', 'footer' );

		} else {
			// user requested Single Entry View
			GravityView_Plugin::log_debug( '[render_view] Executing Single View' );

			//fetch template and slug
			$view_slug =  apply_filters( 'gravityview_template_slug_'. $template_id, 'table', 'single' );
			GravityView_Plugin::log_debug( '[render_view] View single template slug: ' . print_r( $view_slug, true ) );

			//fetch entry detail
			$view_entries['count'] = 1;
			$view_entries['entries'][] = gravityview_get_entry( $single_entry );
			GravityView_Plugin::log_debug( '[render_view] Get single entry: ' . print_r( $view_entries['entries'], true ) );

			$gravityview_view->context = 'single';
			$sections = array( 'single' );

		}

		// add template style
		self::add_style( $template_id );

		// Prepare to render view and set vars
		$gravityview_view->entries = $view_entries['entries'];
		$gravityview_view->total_entries = $view_entries['count'];

		// finaly we'll render some html
		ob_start();
		$sections = apply_filters( 'gravityview_render_view_sections', $sections, $template_id );
		foreach( $sections as $section ) {
			GravityView_Plugin::log_debug( '[render_view] Rendering '. $section . ' section.' );
			$gravityview_view->render( $view_slug, $section );
		}

		// Print the View ID to enable proper cookie pagination ?>
		<input type="hidden" id="gravityview-view-id" value="<?php echo $args['id']; ?>">
		<?php
		$output = ob_get_clean();

		return $output;
	}


	/**
	 * Core function to calculate View multi entries (directory) based on a set of arguments ($args):
	 *   $id - View id
	 *   $page_size - Page
	 *   $sort_field - form field id to sort
	 *   $sort_direction - ASC / DESC
	 *   $start_date - Ymd
	 *   $end_date - Ymd
	 *   $class - assign a html class to the view
	 *   $offset (optional) - This is the start point in the current data set (0 index based).
	 *
	 *
	 * @access public
	 * @static
	 * @param mixed $args
	 * @param int $form_id
	 * @param array $template_settings
	 * @return void
	 */
	public static function get_view_entries( $args, $form_id ) {

		GravityView_Plugin::log_debug( '[get_view_entries] init' );
		// start filters and sorting

		// Search Criteria
		$search_criteria = apply_filters( 'gravityview_fe_search_criteria', array( 'field_filters' => array() ) );
		GravityView_Plugin::log_debug( '[get_view_entries] Search Criteria after hook gravityview_fe_search_criteria: ' . print_r( $search_criteria, true ) );

		// implicity search
		if( !empty( $args['search_value'] ) ) {
			$search_criteria['field_filters'][] = array(
				'key' => ( ( !empty( $args['search_field'] ) && is_numeric( $args['search_field'] ) ) ? $args['search_field'] : null ), // The field ID to search
				'value' => esc_attr( $args['search_value'] ), // The value to search
				'operator' => 'contains', // What to search in. Options: `is` or `contains`
			);
		}
		GravityView_Plugin::log_debug( '[get_view_entries] Search Criteria after implicity search: ' . print_r( $search_criteria, true ) );

		//start date & end date filter - Override values defined in shortcode (if needed)
		if( !empty( $args['start_date'] ) ) {
			if( empty( $search_criteria['start_date'] ) || ( !empty( $search_criteria['start_date'] ) && strtotime( $args['start_date'] ) > strtotime( $search_criteria['start_date'] ) ) ) {
				$search_criteria['start_date'] = $args['start_date'];
			}
		}

		if( !empty( $args['end_date'] ) ) {
			if( empty( $search_criteria['end_date'] ) || ( !empty( $search_criteria['end_date'] ) && strtotime( $args['end_date'] ) < strtotime( $search_criteria['end_date'] ) ) ) {
				$search_criteria['start_date'] = $args['end_date'];
			}
		}

		GravityView_Plugin::log_debug( '[get_view_entries] Search Criteria after date params: ' . print_r( $search_criteria, true ) );


		// Sorting
		$sorting = array();
		if( !empty( $args['sort_field'] ) ) {
			$sorting = array( 'key' => $args['sort_field'], 'direction' => $args['sort_direction'] );
		}

		GravityView_Plugin::log_debug( '[get_view_entries] Sort Criteria : ' . print_r( $sorting, true ) );


		// Paging & offset
		$page_size = !empty( $args['page_size'] ) ? $args['page_size'] : 25;

		if( isset( $args['offset'] ) ) {
			$offset = $args['offset'];
		} else {
			$curr_page = empty( $_GET['pagenum'] ) ? 1 : intval( $_GET['pagenum'] );
			$offset = ( $curr_page - 1 ) * $page_size;
		}
		$paging = array( 'offset' => $offset, 'page_size' => $page_size );

		GravityView_Plugin::log_debug( '[get_view_entries] Paging: ' . print_r( $paging, true ) );


		// remove not approved entries
		if( !empty( $args['show_only_approved'] ) ) {
			$search_criteria['field_filters'][] = array( 'key' => 'is_approved', 'value' => 'Approved' );
			$search_criteria['field_filters']['mode'] = 'all'; // force all the criterias to be met

			GravityView_Plugin::log_debug( '[get_view_entries] Search Criteria if show only approved: ' . print_r( $search_criteria, true ) );
		}

		// Only show active listings
		$search_criteria['status'] = apply_filters( 'gravityview_status', 'active', $args );

		//fetch entries
		$count = 0;
		$entries = gravityview_get_entries( $form_id, compact( 'search_criteria', 'sorting', 'paging' ), $count );

		GravityView_Plugin::log_debug( '[get_view_entries] Get Entries. Found: ' . print_r( $count, true ) .' entries');

		return compact( 'count', 'entries', 'paging' );
	}




	// helper functions

	/**
	 * Filter area fields based on specified conditions
	 *
	 * @access public
	 * @param array $dir_fields
	 * @return void
	 */
	public static function filter_fields( $dir_fields ) {

		if( empty( $dir_fields ) || !is_array( $dir_fields ) ) {
			return $dir_fields;
		}

		foreach( $dir_fields as $area => $fields ) {
			foreach( $fields as $uniqid => $properties ) {

				if( self::hide_field_check_conditions( $properties ) ) {
					unset( $dir_fields[ $area ][ $uniqid ] );
				}

			}
		}

		return $dir_fields;

	}


	/**
	 * Check wether a certain field should not be presented based on its own properties.
	 *
	 * @access public
	 * @param array $properties
	 * @return true (field should be hidden) or false (field should be presented)
	 */
	public static function hide_field_check_conditions( $properties ) {

		// logged-in visibility
		if( !empty( $properties['only_loggedin'] ) && !current_user_can( $properties['only_loggedin_cap'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Register styles and scripts
	 *
	 * @access public
	 * @return void
	 */
	public static function add_scripts_and_styles() {
		global $post, $posts;

		foreach ($posts as $p) {

			// enqueue template specific styles
			if( is_a( $p, 'WP_Post' ) && ( function_exists('has_shortcode') && has_shortcode( $p->post_content, 'gravityview') ||  'gravityview' === get_post_type() ) ) {

				wp_enqueue_script( 'gravityview-fe-view', plugins_url('includes/js/fe-views.js', GRAVITYVIEW_FILE), array( 'jquery', 'gravityview-jquery-cookie' ), GravityView_Plugin::version, true );

				wp_enqueue_style( 'gravityview_default_style', plugins_url('templates/css/gv-default-styles.css', GRAVITYVIEW_FILE), array(), GravityView_Plugin::version, 'all' );

				$template_id = get_post_meta( $p->ID, '_gravityview_directory_template', true );

				self::add_style( $template_id );
			}

		}

	}

	/**
	 * Add template extra style if exists
	 * @param string $template_id
	 */
	public static function add_style( $template_id ) {

		if( !empty( $template_id ) && wp_style_is( 'gravityview_style_' . $template_id, 'registered' ) ) {
			GravityView_Plugin::log_debug( '[add_style] Adding extra template style for: ' . print_r( $template_id, true ) );
			wp_enqueue_style( 'gravityview_style_' . $template_id );
		}

	}


}

new GravityView_frontend;


/**
 * Theme function to get a GravityView view
 *
 * @access public
 * @param string $view_id (default: '')
 * @param array $atts (default: array())
 * @return void
 */
function get_gravityview( $view_id = '', $atts = array() ) {
	if( !empty( $view_id ) ) {
		$atts['id'] = $view_id;
		$args = wp_parse_args( $atts, GravityView_frontend::get_default_args() );
		return GravityView_frontend::render_view( $args );
	}
	return '';
}

/**
 * Theme function to render a GravityView view
 *
 * @access public
 * @param string $view_id (default: '')
 * @param array $atts (default: array())
 * @return void
 */
function the_gravityview( $view_id = '', $atts = array() ) {
	echo get_gravityview( $view_id, $atts );
}





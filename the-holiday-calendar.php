<?php
/*
Plugin Name: The Holiday Calendar
Version: 1.11.1
Plugin URI: http://www.theholidaycalendar.com
Description: Shows the upcoming holidays.
Author: Mva7
Author URI: http://www.mva7.nl
*/

require_once('helpers/helper.class.php');
require_once('helpers/http-get-helper.class.php');
require_once('gui-elements/calendar.class.php');
require_once('constants/constants.class.php');
require_once('admin/widget-form.class.php');
require_once('widgets/widget.class.php');
require_once('widgets/widget-manager.class.php');
require_once('posts/post-manager.class.php');
require_once('admin/post-form.class.php');
require_once('admin/settings.class.php');
require_once('helpers/session-helper.class.php');
require_once('helpers/request-helper.class.php');
require_once('helpers/translation-helper.class.php');
require_once('helpers/update-helper.class.php');
require_once('helpers/string-helper.class.php');
require_once('helpers/settings-helper.class.php');

add_action( 'widgets_init', create_function('', 'return register_widget("the_holiday_calendar");'));
add_action( 'init', array( 'the_holiday_calendar', 'create_post_type' ) );
add_filter( 'query_vars', array( 'the_holiday_calendar', 'add_queryvars' ) );
add_action( 'add_meta_boxes', array( 'the_holiday_calendar', 'add_meta_box' ) );
add_action( 'save_post', array( 'the_holiday_calendar', 'save' ) );
add_action( 'wp_enqueue_scripts', array( 'the_holiday_calendar', 'load_css' ) );
add_filter( 'body_class', array( 'the_holiday_calendar', 'add_body_classes') );
add_filter( 'the_title', array( 'the_holiday_calendar', 'override_title'), 10, 2 );
//add_action( 'template_redirect', array( 'the_holiday_calendar', 'override_template') );
//add_filter( 'the_content', array( 'the_holiday_calendar', 'override_content') );
add_filter( 'wp_title', array( 'the_holiday_calendar', 'override_page_title'), 10, 2 );
add_action( 'pre_get_posts', array( 'the_holiday_calendar', 'modify_query') );
add_filter('the_posts', array( 'the_holiday_calendar', 'create_dummy_posts'));
add_filter( 'manage_edit-' . thc_constants::POSTTYPE . '_columns' , array( 'the_holiday_calendar', 'add_date_column' ));
add_action( 'manage_' . thc_constants::POSTTYPE . '_posts_custom_column', array( 'the_holiday_calendar', 'fill_date_column' ), 10, 2 );
add_filter( 'manage_edit-' . thc_constants::POSTTYPE . '_sortable_columns', array( 'the_holiday_calendar', 'make_sortable_date_column' ) );
add_filter( 'get_the_excerpt', array( 'the_holiday_calendar', 'get_excerpt' ), 99);
add_filter( 'the_excerpt', array( 'the_holiday_calendar', 'get_excerpt' ), 99);

class the_holiday_calendar extends WP_Widget {
	
	var $dateError;

	// constructor
	function the_holiday_calendar() {
		parent::WP_Widget(false, $name = __('The Holiday Calendar', 'wp_widget_plugin') );		
		
		thc_update_helper::migrate_widget_settings();
		
		if (!is_admin()) {
			wp_enqueue_script('jquery');
		}		
		
		if (!session_id())
			session_start();
			
		if( is_admin() )
			$thc_settings = new thc_settings();
	}
	
	function get_excerpt($excerpt) {
		if(!request_helper::get_query_was_modified())
		{
			return $excerpt;
		}		
		
		global $post;
		if(thc_string_helper::contains($post->post_excerpt, thc_constants::EXCERPT_MARKER_PREFIX))
		{
			$startpos = strpos($post->post_excerpt, thc_constants::EXCERPT_MARKER_PREFIX) + strlen(thc_constants::EXCERPT_MARKER_PREFIX);
		
			$read_more_text_id = substr($post->post_excerpt, $startpos, 13);
		
			$read_more_texts = request_helper::get_read_more_texts();
			
			$excerpt = $read_more_texts[$read_more_text_id];
		}
		
		return $excerpt;
	}
	
	function make_sortable_date_column( $columns ) {
		$columns['thc_event_date'] = 'thc_event_date';

		return $columns;
	}
	
	function add_date_column($columns) {
		unset(
			$columns['date']
		);
		$new_columns = array(
			'thc_event_date' => 'Event date'
		);
		return array_merge($columns, $new_columns);
	}
	
	function fill_date_column( $column, $post_id ) {
		global $post;

		switch( $column ) {

			/* If displaying the 'duration' column. */
			case 'thc_event_date' :

				/* Get the post meta. */
				$event_date = get_post_meta( $post_id, 'eventDate', true );

				echo $event_date;

				break;
		}
	}
	
	function modify_query( $query ) {	
		global $wp_query;
		if ( !is_admin() && $query->get('post_type') == thc_constants::POSTTYPE
		&& $query->is_main_query() && array_key_exists('date', $wp_query->query_vars)) {
			$query->set('post_type', thc_constants::POSTTYPE);			
			$query->set('meta_query', array(
					array(
						'key'     => 'eventDate',
						'value'   => http_get_helper::get_day(),
						'compare' => '=',
					),
				));
			$query->set('order', 'ASC');
			$query->set('posts_per_page', 100);	
			
			request_helper::set_query_is_modified(true);
			request_helper::set_query_was_modified(true);
		}
		else {
			request_helper::set_query_is_modified(false);
		}
		
		return $query;
	}
	
	function create_dummy_posts($posts)
	{	
		$countryIso = http_get_helper::get_countryIso();		

		if($countryIso == null || !request_helper::get_query_is_modified())
		{
			return $posts;
		}

		global $wp_query;		
		
		$day = isset($wp_query->query_vars['date']) ? $wp_query->query_vars['date'] : date('Y-m-d');
		
		$formattedDate = thc_helper::formatDate($day);
	
		$posts = array_merge($posts, thc_helper::get_remote_events_as_posts($countryIso, NULL, $day));
		
		return $posts;
	}
	
	function override_template() {
		if(get_post_type() == thc_constants::POSTTYPE )
		{			
			include(TEMPLATEPATH."/index.php");
			
			exit;
		}
	}
	
	function override_title($title, $id) {	
		global $post;
		if(get_post_type() == thc_constants::POSTTYPE)
		{
			if(is_admin())
			{
				//$title = self::get_requested_date() . ' - ' . $title;
			}
			else {
				if(is_archive() && in_the_loop())
				{
					$title = $title . ' (' . self::get_requested_date() . ')';		
				}
				else if (thc_settings_helper::get_show_date_in_title() == 1 && is_single() && !request_helper::get_surpress_title_filter() && $id == get_the_ID())
				{
					
					$event_date = get_post_meta( $post->ID, 'eventDate', true );
					$event_date = thc_helper::formatDate($event_date);
					
					$title .= ' (' . $event_date . ')';
				}
			}
		}
		
		return $title;
	}
	
	function override_page_title($title, $sep) {
		if(!is_admin() && get_post_type() == thc_constants::POSTTYPE && is_archive())
		{
			$title = self::get_requested_date() . ' | ' . get_bloginfo( 'name' );
		}
		
		return $title;
	}
	
	function get_requested_date() {
		global $wp_query;

		$day = isset($wp_query->query_vars['date']) ? $wp_query->query_vars['date'] : date('Y-m-d');		
		
		return thc_helper::formatDate($day);
	}
	
	function add_queryvars( $qvars )
	{
	  $qvars[] = 'date';
	  $qvars[] = 'country';
	  $qvars[] = 'thc-month';
	  
	  return $qvars;
	}
	
	function add_body_classes( $classes ) {
		$classes[] = 'mva7-thc-activetheme-' . get_template();
		
		return $classes;
	}
	
	function create_post_type() {
		register_post_type( thc_constants::POSTTYPE,
			array(
			'labels' => array(
				'name' => __( 'Events' ),
				'singular_name' => __( 'Event' ),
			),
			'rewrite' => array( 'slug' => thc_constants::EVENTS_SLUG, 'with_front' => true ),
			'public' => true,
			'has_archive' => true,
			'menu_position' => 5,
			'menu_icon' => 'dashicons-calendar-alt'
			)
		);
		flush_rewrite_rules( false );
	}
	
	function load_css() {
		wp_register_style( 'thc-style', plugins_url('the-holiday-calendar.css', __FILE__) );
		wp_enqueue_style( 'thc-style' );
	}
	
	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {
            
		add_meta_box(
			'some_meta_box_name'
			,__( 'The Holiday Calendar', 'myplugin_textdomain' )
			,array( 'thc_post_form', 'render_meta_box_content' )
			,thc_constants::POSTTYPE
			,'normal'
			,'high'
		);   
	}	
	
	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save( $post_id ) {		
		thc_post_manager::save( $post_id );
	}	
	
	// widget form creation
	function form($instance) {
		thc_widget_form::render_form($instance);
	}

	// update widget
	function update($new_instance, $old_instance) {
		$updated_instance = thc_widget_manager::update_widget_instance($new_instance, $old_instance);

		return $updated_instance;
	}

	// display widget
	function widget($args, $instance) {
	   thc_widget::show($args, $instance);
	}
}

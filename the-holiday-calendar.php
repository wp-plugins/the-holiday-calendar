<?php
/*
Plugin Name: The Holiday Calendar
Version: 1.5
Plugin URI: http://www.theholidaycalendar.com
Description: Shows the upcoming holidays.
Author: Mva7
Author URI: http://www.mva7.nl
*/

require_once('helpers/helper.class.php');
require_once('gui-elements/calendar.class.php');
require_once('constants/constants.class.php');
require_once('admin/widget-form.class.php');
require_once('widgets/widget.class.php');
require_once('widgets/widget-manager.class.php');
require_once('posts/post-manager.class.php');
require_once('admin/post-form.class.php');

add_action( 'widgets_init', create_function('', 'return register_widget("the_holiday_calendar");'));
add_action( 'init', array( 'the_holiday_calendar', 'create_post_type' ) );
add_filter( 'query_vars', array( 'the_holiday_calendar', 'add_queryvars' ) );
add_action( 'add_meta_boxes', array( 'the_holiday_calendar', 'add_meta_box' ) );
add_action( 'save_post', array( 'the_holiday_calendar', 'save' ) );
add_action( 'wp_enqueue_scripts', array( 'the_holiday_calendar', 'load_css' ) );
add_filter( 'body_class', array( 'the_holiday_calendar', 'add_body_classes') );
add_filter( 'the_title', array( 'the_holiday_calendar', 'override_title') );
add_action( 'template_redirect', array( 'the_holiday_calendar', 'override_template') );
add_filter( 'the_content', array( 'the_holiday_calendar', 'override_content') );
add_filter( 'wp_title', array( 'the_holiday_calendar', 'override_page_title'), 10, 2 );

class the_holiday_calendar extends WP_Widget {
	
	var $dateError;

	// constructor
	function the_holiday_calendar() {
		parent::WP_Widget(false, $name = __('The Holiday Calendar', 'wp_widget_plugin') );		
		
		if (!is_admin()) {
			wp_enqueue_script('jquery');
		}		
		
		if (!session_id())
			session_start();
	}
	
	function override_template() {
		if(get_post_type() == thc_constants::POSTTYPE )
		{
			include(TEMPLATEPATH."/page.php");
			exit;
		}
	}
	
	function override_title($title) {
		if(get_post_type() == thc_constants::POSTTYPE)
		{
			if(is_admin())
			{
				$title = self::get_requested_date() . ' - ' . $title;
			}
			else {
				if(is_archive() && in_the_loop())
				{
					$title = self::get_requested_date();		
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
		$dateFormat = isset($wp_query->query_vars['dateFormat']) ? $wp_query->query_vars['dateFormat'] : 5;
		
		return thc_helper::formatDate($day, $dateFormat);
	}
	
	function add_queryvars( $qvars )
	{
	  $qvars[] = 'date';
	  $qvars[] = 'dateFormat';
	  $qvars[] = 'country';
	  
	  return $qvars;
	}
	
	function add_body_classes( $classes ) {
		// add 'class-name' to the $classes array
		$classes[] = 'mva7-thc-activetheme-' . get_template();
		// return the $classes array
		return $classes;
	}
	
	function include_template_function( $template_path ) {
		$this->prevent_404_when_no_posts();
		$new_content = $this->get_content();		
		
		return isset($new_content) ? $new_content : $template_path;
	}
	
	function prevent_404_when_no_posts() {
		global $wp_query, $post;
		if ( isset($wp_query->query['post_type']) && $wp_query->query['post_type'] == thc_constants::POSTTYPE && $wp_query->post_count == 0 ) {
			status_header( '200' );
			$wp_query->is_404 = false;
			$wp_query->is_archive = true;
			$wp_query->is_post_type_archive = true;
			$post = new stdClass();
			$post->post_type = $wp_query->query['post_type'];
		}
	}
	
	function override_content() {
		if(get_post_type() == thc_constants::POSTTYPE )
		{
			if ( is_archive()) {
				// checks if the file exists in the theme first,
				// otherwise serve the file from the plugin
				if ( $theme_file = locate_template( array ( 'posts/views/dayView.php' ) ) ) {
					$template_path = $theme_file;
				} else {
					$template_path = plugin_dir_path( __FILE__ ) . '/posts/views/dayView.php';
				}
				
				require($template_path);
			}
		}
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

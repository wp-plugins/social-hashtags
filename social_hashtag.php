<?php
/*
Plugin Name: Social Hashtag
Description: Stores thumbnails & details locally in a custom post_type so you have full control over the content.  Also, this allows you to categorize, make private/public, etc.
Version: 1.0.0
Author: Bryan Shanaver
Author URI: http://fiftyandfifty.org
*/
?>
<?php

define('SOCIAL_HASHTAG_VERSION', '1.0.0');

define('SOCIAL_HASHTAG_URL', plugin_dir_url( __FILE__ ));
define('SOCIAL_HASHTAG_DIR', plugin_dir_path(__FILE__) );

$dir = social_hashtag_cache_dir();
include_once "$dir/lib/social_hashtag.class.php";
include_once "$dir/lib/platforms.class.php";

function social_hashtag_cache_init() {
  global $social_hashtag_cache;
  $social_hashtag_cache = new SOCIAL_HASHTAG_CACHE();
  get_manual_run_request();
}

// front end styles & scripts
function social_hashtag_front_scripts_styles(){
  wp_register_style( 'social_hashtag-style', SOCIAL_HASHTAG_URL . 'lib/social_hashtag.css' );
  wp_enqueue_style( 'social_hashtag-style' );
}
add_action('wp_enqueue_scripts', 'social_hashtag_front_scripts_styles');

function get_manual_run_request(){
  global $social_hashtag_cache;
  if(isset($_POST['run_social_hashtag_cache'])) {
   $social_hashtag_cache->run_social_hashtag_query();
  }
}

function social_hashtag_cache_dir() {
  if (defined('SOCIAL_HASHTAG_DIR') && file_exists(SOCIAL_HASHTAG_DIR)) {
    return SOCIAL_HASHTAG_DIR;
  } else {
    return plugin_dir_path(__FILE__);
  }
}

function social_hashtag_pics( $atts ){
	$defaults = shortcode_atts( 
	  array(
	  'cols' => 8,
	  'rows' => 6), 
	  $atts 
	);
	if( !$social_hashtag_cache ){
	  $social_hashtag_cache = new SOCIAL_HASHTAG_CACHE();
	}
	$social_hashtag_cache->display_social_hashtag_pics( $defaults );
}


function social_hashtag_extra_columns($columns) {
  $columns['social_hashtag_thumbnail'] = 'Thumbnail';
  return $columns;
}


function social_hashtag_show_extra_columns($column) {
  global $post;
  switch ($column) {
    case 'social_hashtag_thumbnail':
      $social_hashtag_thumb_url = get_post_meta($post->ID, 'social_hashtag_thumb_url', true);
      echo "<img src='{$social_hashtag_thumb_url}' />";
      break;
  }
}

function get_social_hashtag_pics_create() {
  wp_schedule_event(time(), 'hourly', 'get_social_hashtag_pics_cron');
}

function get_social_hashtag_pics_deactivate() {
  wp_clear_scheduled_hook('get_social_hashtag_pics_cron');
}

function get_social_hashtag_pics() {
	if( !$social_hashtag_cache ){
	  $social_hashtag_cache = new SOCIAL_HASHTAG_CACHE();
	}
	$social_hashtag_cache->run_social_hashtag_query();
}

// Add initialization and activation hooks
add_action('init', 'social_hashtag_cache_init', 10);
add_action('init', array(&$social_hashtag_cache, 'run_manually_hook'), 11);
add_action('manage_posts_custom_column',  'social_hashtag_show_extra_columns');

add_shortcode('social_hashtag_pics', 'social_hashtag_pics');

add_filter('manage_edit-social_hashtag_columns', 'social_hashtag_extra_columns');

/* crons */

register_activation_hook( __FILE__ , 'get_social_hashtag_pics_create' );
add_action('get_social_hashtag_pics_cron', 'get_social_hashtag_pics' );
register_deactivation_hook( __FILE__ , 'get_social_hashtag_pics_deactivate' );

//add_filter('cron_schedules', array(&$social_hashtag_cache, 'add_cron_intervals'), 20 );


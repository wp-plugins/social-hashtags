<?php
/*
 * @author      Bryan Shanaver <bryan[at]fiftyandfifty[dot]org>
 * @version     1.5.0
 */

class SOCIAL_HASHTAG_CACHE {
  
  var $api_options = array(

    'instagram' => array(
      'api_scheme' => 'https',
      'api_host' => 'api.instagram.com',
      'api_port' => '',
      'api_endpoint' => 'v1/tags/%string%/media/recent?',
      'auth_type' => 'client_id'
    ),
    // 'twitter' => array(
    //   'api_scheme' => 'http',
    //   'api_host' => 'api.twitter.com',
    //   'api_port' => '',
    //   'api_endpoint' => '1.1/search/tweets.json?q=%string%',
    //   'auth_type' => ''
    // ),
    'youtube' => array(
      'api_scheme' => 'https',
      'api_host' => 'gdata.youtube.com',
      'api_port' => '',
      'api_endpoint' => 'feeds/api/videos?q=%string%',
      'auth_type' => ''
    ),
    // 'teleportd' => array(
    //   'api_scheme' => 'http',
    //   'api_host' => 'v1.api.teleportd.com',
    //   'api_port' => '8080',
    //   'api_endpoint' => 'search?string=%string%?',
    //   'auth_type' => 'apikey'
    // ),
  );

  var  $social_api_settings = array(
    'api_selected' => '',
    'api_authentication' => '',
    'search_name' => '',
    'string' => '',
    'period' => '',
    'location' => '',
    'cron_interval' => 'manual'
  );
  
  var $teleportd, $instagram, $twitter, $youtube;

  var $global_options     = 'social_hashtag-global';
  var $social_api_options = 'social_hashtag-apis';
  
  function __construct() {
    $this->teleportd = new PLATFORM_TELEPORTD();
    $this->instagram = new PLATFORM_INSTAGRAM();
    $this->twitter = new PLATFORM_TWITTER();
    $this->youtube = new PLATFORM_YOUTUBE();
    add_action('admin_menu', array(&$this, 'admin_menu'));
    $this->create_posttype_and_taxonomy();
  }
  
  function choose_platform($name){
    switch( $name ) {
      case "instagram":
        $platform = $this->instagram;
        break;
      case "teleportd":
        $platform = $this->teleportd;
        break;
      case "twitter":
        $platform = $this->twitter;
        break;
      case "youtube":
        $platform = $this->youtube;
        break;
      default:
        //wp_die( __('Missing Platform class') );
    }
    return $platform;    
  }
  
  function admin_menu() {
    $page = add_options_page('Social Hashtag Settings', 'Social Hashtags', 'manage_options', 'social_hashtag-cache-api', array(&$this, 'admin_options'));
  }
    
  function admin_options() {
    if (!current_user_can('manage_options'))  {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    // print "<pre>";
    // print_r($_POST);
    // print "</pre>";
       
    if (!empty($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], "update-options") && !empty($_REQUEST['social_hashtag_global'])) {
      $this->save_option( $this->social_api_options, $_REQUEST['social_hashtag_cache'] );
      $this->save_option( $this->global_options, $_REQUEST['social_hashtag_global'] );
    }
    $social_api_options = $this->get_social_hashtag_options();

    $global_options     = $this->get_social_hashtag_options(null, 'global');
    $debug_on           = !empty($global_options['debug_on'])?$global_options['debug_on']:'No';
    $always_private     = !empty($global_options['always_private'])?$global_options['always_private']:'No';
    $max_items          = !empty($global_options['max_items'])?$global_options['max_items']:'50';
    $blacklisted_users  = !empty($global_options['blacklisted_users'])?$global_options['blacklisted_users']:'';

    // print "<pre>";
    // print_r($social_api_options);
    // print "</pre>";

    // print "<pre>";
    // print_r($global_options);
    // print "</pre>";
    
    if( !empty($_REQUEST['delete_api']) ){
      foreach( $social_api_options as $cache_num => $option_settings ){
        if( $_REQUEST['delete_api'] != $cache_num ){
          $rebuild_api_options[] = $option_settings;
        }
      }
      $this->save_option( $this->social_api_options, $rebuild_api_options );
      $social_api_options = $rebuild_api_options;
    }
    
    if( !empty($_REQUEST['add_api']) ){
      $social_api_options[] = array_merge( $this->social_api_settings, array('api_selected' => $_REQUEST['api_option'],'search_name' => $_REQUEST['api_option']) );      
    }
        
?>


    
<div class="wrap">
  <div id="icon-options-general" class="icon32"><br /></div>
  <h2>Social Hashtags</h2>
  <form action="options-general.php?page=social_hashtag-cache-api" method="post" id="social_hashtag_form">
    <?php wp_nonce_field('update-options'); ?>
    <input type="hidden" name="delete_api" id="delete_api" />
      
<?php if( isset($social_api_options[0]) ): ?>    
    
    <h3>Global Settings</h3>

	  <table id="all-plugins-table" class="widefat">   
      <thead>
        <tr>
          <th class="manage-column" scope="col">All APIs Inherit These Settings</th>
          <th class="manage-column" scope="col"> </th>
        </tr>
      </thead>
      <tbody class="plugins">
        <tr class="active">
          <td class="desc">
        	  <select name="social_hashtag_global[debug_on]" class="disable_onchange" >
        	    <option value="No" <?php selected( $debug_on, 'No' ); ?>>No</option>
        	    <option value="Yes" <?php selected( $debug_on, 'Yes' ); ?>>Yes</option>
        		</select> 
          </td>
      	  <th scope="row">
        		<label for="">Turn debug ON</label><br/>
        		<code>Debugging info will be sent to the javascript console when you run manual tests</code>
        	</th>
        </tr>      
        <tr class="active">
          <td class="desc">
        	  <select name="social_hashtag_global[always_private]" class="disable_onchange" >
        	    <option value="No" <?php selected( $always_private, 'No' ); ?>>No</option>
        	    <option value="Yes" <?php selected( $always_private, 'Yes' ); ?>>Yes</option>
        		</select> 
          </td>
      	  <th scope="row">
        		<label for="">Set new items as private by default</label><br/>
        		<code>You will need to review new items and set them to public</code>
        	</th>
        </tr>
        <tr class="active">
          <td class="desc">
            <p><input type="text" name="social_hashtag_global[max_items]" value="<?php print ( is_numeric($max_items) ? $max_items : '0') ?>" class="regular-text disable_onchange" /></p>
          </td>
      	  <th scope="row">
        		<label for="">Max number of items to get per API</label><br/>
        		<code>Set to 0 for no max - this may take a long time to run since some services only let you grab 50 at a time.</code>
        	</th>
        </tr>  
        <tr class="active">
          <td class="desc">
            <p><textarea name="social_hashtag_global[blacklisted_users]" cols="80" rows="4"><?php print $blacklisted_users ?></textarea></p>
          </td>
      	  <th scope="row">
        		<label for="">Blacklisted usernames/handles</label><br/>
        		<code>comma separated</code>
        	</th>
        </tr>
      </tbody>
		</table>   
		
<?php endif; ?>  
		
		<div style="width:100%;height:20px"></div> 
		
    <select name="api_option" style="width:100px">
<?php foreach( $this->api_options as $option => $option_settings ): ?>
      <option value="<?php print $option ?>"><?php print $option ?></option>
<?php endforeach; ?>
    </select> 

    <input type="hidden" name="add_api" id="add_api" />
    <a href="javascript:add_an_api();" class="button-secondary">Add an API Source</a>
    
    <div style="width:100%;height:20px"></div>
    
    <h3>API Settings</h3>
    
<?php 

    if( !empty($social_api_options[0]) ){
      foreach( $social_api_options as $api_num => $api_settings){
        if( !empty($api_settings['api_selected']) ){
          //$url = $this->build_api_search_url($cache_num);
          //$api_urls .= "api_url[{$cache_num}] = '{$url}';\n\t";
          $platform = $this->choose_platform($api_settings['api_selected']);
          if(is_object($platform)){
            $platform->admin_form($api_settings, $api_num, $this->api_options);
          }
        }
      }
    }

?>

    <style>
      th{width:600px;}
      .remove-div{text-align:right;margin-right:10px}
    </style>
    <script type="text/javascript">
      jQuery("#social_hashtag_form").children(".widgets-holder-wrap").children(".sidebar-name").click(function() {
          jQuery(this).parent().toggleClass("closed")
      });    
      function add_an_api(){
        jQuery('#social_hashtag_form #add_api').val('true');
        jQuery('#social_hashtag_form').submit()
      }
      function delete_an_api(num){
        jQuery('#social_hashtag_form #delete_api').val(num);
        jQuery('#social_hashtag_form').submit()
      }
      function test_api(num){
        var api_url = Array();
        jQuery.getJSON(api_url[num] + "&format=json&callback=?", function(data){
          window.console && console.debug(api_url[num]);
          window.console && console.debug(data);
          var success = false;
          try{if(data.meta.code == 200){success = true;}}catch(err){}
          try{if(data.status == 'OK'){success = true;}}catch(err){}
          try{if(data.results){success = true;}}catch(err){}
          if(success){
            alert('Success');
          }else{
            alert('Error');
          }
        });
      }
      function run_manually(num){
        window.console && console.debug('run_manually');
        if(jQuery('#social_hashtag_form .debug').attr('checked')){debug='true';}else{debug='';}
        jQuery.get(('/wp-admin/?run_social_hashtag_manually=true&num=' + num + '&debug=' + debug), function(data) {
          window.console && console.debug(data);
          jQuery("#run_manually_response").html('');
        }).complete(function() { alert('Success'); });
      }
      jQuery(function() {
        jQuery('.disable_onchange').change(function() {
          jQuery('#test_api').attr("href", "javascript:alert('save changes first')");
          jQuery('#run_manually').attr("href", "javascript:alert('save changes first')");
        });  
      });
    </script>

    <p class="submit">
      <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
  </form>
</div>
<?php

  }
  
  function get_available_postypes(){
    $args=array(
      'public'   => true
    ); 
    $output = 'names'; // names or objects, note names is the default
    $operator = 'or'; // 'and' or 'or'
    $post_types = get_post_types( $args, $output, $operator );
    return $post_types;
  }
 
  function save_option($id, $value) {
    $option_exists = (get_option($id, null) !== null);
    if ($option_exists) {
      update_option($id, $value);
    } else {
      add_option($id, $value);
    }
  }  
  
  function import_item($photos, $platform, $platform_options, $plugin_options){
    global $wpdb;
    
    require_once(ABSPATH . "wp-admin" . '/includes/image.php');
    $wordpress_uploads = wp_upload_dir();
    
    $blacklist = explode(',', $plugin_options['blacklisted_users']);
    
    $retrieved = 0;
    $added = 0;
    
    try {
  
        foreach( $photos as $num => $photo ) {
          
          $retrieved++;

          if( !$platform->parse_response($photo, $platform_options) ){ continue; }
          
          if( $plugin_options['max_items'] && $retrieved > $plugin_options['max_items'] ){ break; }
          
          // Check to see if this user is blacklisted, skip to the next on if so   
          if( count($blacklist) ){    
            if(  array_search ( $platform->pic_handle, $blacklist ) ){ if($plugin_options['debug_on']){print $platform->pic_full_title . " [not added] blacklisted user: ".$platform->pic_handle." \n";} continue; }
          }

          // Check to see if this is a retweet, skip to the next on if so  
          if( $platform_options['skip_retweets'] == 'Yes' ){
            if(  strstr ( $platform->pic_full_title , 'RT ') ){ if($plugin_options['debug_on']){print $platform->pic_full_title . " [not added] Retweet suspected : ".$platform->pic_full_title." \n";} continue; }
          }

          // Check to see if we already have this photo, skip to the next one if we do
          $existing_photo = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE meta_key = 'social_hashtag_sha' and meta_value = %s", $platform->pic_sha ) );          
          if( count($existing_photo) >= 1 ){ if($plugin_options['debug_on']){print $platform->pic_full_title . " [not added] sha exists: ".$platform->pic_sha." \n";} continue; }
          else{ if($plugin_options['debug_on']){print $platform->pic_full_title." \n";} }

          $added++;
          
          // if there is a thumnail, process the thumbnail, and attach it
          if( $platform->pic_thumb != '' ) {
            $thumb_imagesize = getimagesize($platform->pic_thumb);
            $img_name = strtolower( preg_replace('/[\s\W]+/','-', $platform->pic_clean_title) );
            $image_file = file_get_contents( $platform->pic_thumb );
            $img_filetype = wp_check_filetype( $platform->pic_thumb, null );
            if( !$img_filetype['ext'] ){ 
              $img_filetype['ext'] = 'jpg';
              $img_filetype['type'] = 'image/jpeg';
            }           
            $img_path = $wordpress_uploads['path'] . "/" . $img_name . "." . $img_filetype['ext'];
            $img_sub_path = $wordpress_uploads['subdir'] . "/" . $img_name . "." . $img_filetype['ext'];
            file_put_contents($img_path , $image_file);
          }else{
            unset($full_imagesize);
          }
          
          // if there is a large image, process it
          if( $platform->pic_full != '' ) {
            $full_imagesize = getimagesize($platform->pic_full);
            if($platform->pic_post_content){
              $post_content = $platform->pic_post_content;
            }
            else{
              $post_content = "<img src='".$platform->pic_full ."' alt='".$platform->pic_clean_title ."' />"; 
            }
          }else{
            unset($thumb_imagesize);
            $post_content = $platform->pic_full_title;
          }
          
          $post = array(
           'post_author' => 1,
           'post_date' => $platform->pic_mysqldate ,
           'post_type' => 'social_hashtag',
           'post_title' => $platform->pic_clean_title,
           'post_content' => $post_content,
           'post_status' => ($plugin_options['always_private'] == 'No' ? 'publish' : 'private' ),
          );
          $post_id = wp_insert_post( $post, true );
          
          add_post_meta($post_id, 'social_hashtag_sha', $platform->pic_sha, true);
          add_post_meta($post_id, 'social_hashtag_platform', $platform->pic_handle_platform, true);
          add_post_meta($post_id, 'social_hashtag_userhandle', $platform->pic_handle, true);
          add_post_meta($post_id, 'social_hashtag_location', $platform->pic_loc, true);
          if( $platform->vid_embed ) {
            add_post_meta($post_id, 'social_hashtag_vid_embed', $platform->vid_embed, true);
          }
          if( $platform->pic_full && $full_imagesize ) {
            add_post_meta($post_id, 'social_hashtag_full_url', $platform->pic_full, true);
            add_post_meta($post_id, 'social_hashtag_full_imagesize', ('w'.$full_imagesize[0].'xh'.$full_imagesize[1]), true);
          }
          if( $platform->pic_thumb && $thumb_imagesize ) {
            $attachment = array(
             'post_author' => 1,
             'post_date' => $platform->pic_mysqldate ,
             'post_type' => 'attachment',
             'post_title' => $platform->pic_clean_title,
             'post_parent' => $post_id,
             'post_status' => 'inherit',
             'post_mime_type' => $img_filetype['type'],
            );
            $attachment_id = wp_insert_post( $attachment, true );
            add_post_meta($attachment_id, '_wp_attached_file', $img_sub_path, true );
            add_post_meta($post_id, 'social_hashtag_thumb_url', $platform->pic_thumb, true);
            add_post_meta($post_id, 'social_hashtag_thumb_imagesize', ('w'.$thumb_imagesize[0].'xh'.$thumb_imagesize[1]), true);
          }
          
          $category_ids = array();
          $tag_ids = array();
          
          // link post to the platform 'category'
          $new_category = term_exists( $platform->pic_handle_platform, 'social_hashtag_categories');
          if( $new_category ){
            array_push( $category_ids, $new_category['term_id'] );
          }else{
            $new_term = wp_insert_term( $platform->pic_handle_platform, 'social_hashtag_categories');
            if(!$new_term['errors']){
              array_push( $category_ids, (int)$new_term['term_id'] );
            }
          }
          
          // link post to the api_search_name category
          if( $this->plugin_options[search_name] ){
            $new_category = term_exists( $this->plugin_options[search_name], 'social_hashtag_categories');
            if( $new_category ){
              array_push( $category_ids, $new_category['term_id'] );
            }else{
              $new_term = wp_insert_term( $this->plugin_options[search_name], 'social_hashtag_categories');
              if(!$new_term['errors']){
                array_push( $category_ids, (int)$new_term['term_id'] );
              }
            }
          }
          
          // attach these categories to the new post
          if( count($category_ids) ) {
            wp_set_post_terms( $post_id, $category_ids, 'social_hashtag_categories' );
          }

          // attach these tags to the new post
          if( count($platform->pic_tags) ) {
            wp_set_object_terms($post_id, $platform->pic_tags, 'social_hashtag_tags');
          }
          
          // attach these tags to the new post
          if( count($platform->pic_strs) ) {
            wp_set_object_terms($post_id, $platform->pic_strs, 'social_hashtag_tags');
          }
          
      
        }

        print $platform->pic_handle_platform . " complete! " . $retrieved . " records retrieved, " . $added . " records added\t|\t";
    } catch (Exception $e) {
        print 'Error: ' . $e->getMessage();
    }
    
  }
  
  
  function run_social_hashtag_query($num=0){
    
    $global_options = $this->get_social_hashtag_options(null, 'global');

    $all_api_options = $this->get_social_hashtag_options();
    $platform_options = $all_api_options[$num];
    $platform = $this->choose_platform($platform_options['api_selected']);
    
    $search_url = $this->build_api_search_url($num);
    
    $count_items = 0;
    
    while( strlen($search_url) > 10 ){
      
      if($global_options['debug_on']){print("\n\nurl: " . $search_url . " \n");}
      
      $json_string = $this->remote_get_contents($search_url);
      $response = json_decode($json_string);
      $photos = $platform->clean_response($response);
      
      if( !is_array($photos) ){
        return "No results found";
        break;
      }
      
      if($global_options['debug_on']){print("count: " . count($photos) . " \n");}

      $this->import_item($photos, $platform, $platform_options, $global_options);
      
      $count_items = count($photos) + $count_items;
      if($count_items > $global_options['max_items'] && $global_options['max_items']){
        if($global_options['debug_on']){print("\n\nMax results settings hit: " . $global_options['max_items']);}
        break;
      }
      
      $platform->get_next_page($response, $search_url);
      $search_url = $platform->next_page;
      
    }

  }
  
  function build_api_search_url($option_num=0){
    
    $plugin_options = $this->get_social_hashtag_options();

    print_r($option_num);

    $api_settings = $this->get_social_hashtag_options($option_num);

    print_r($api_settings);

    $api_options = $this->api_options[$api_settings['api_selected']];
  
    $query = $api_options['api_scheme'] . "://" . $api_options['api_host'];                                           //| http://v1.api.social_hashtag.com
    if( $api_options['api_port'] != '' ){ $query .= ":" . $api_options['api_port']; }                                 //| http://v1.api.social_hashtag.com:8080
    $query .= "/" . str_replace("%string%", urlencode($api_settings['string']), $api_options['api_endpoint'] );     //| http://v1.api.social_hashtag.com:8080/search?string=xxxx
    
    if( $api_options['auth_type'] ){
      $query .= "&" . $api_options['auth_type'] . "=" . $api_settings['api_authentication'];                         //| http://v1.api.social_hashtag.com:8080/search?string=xxxx&apikey=xxxxxxxxxx
    }
  
    if( $api_settings['api_selected'] == 'social_hashtag' ) {
      $query.= "&window=50";
      $query.= "&period=" . $api_settings['period'];    
      $query.= "&location=" . urlencode($api_settings['location']); 
    }

    if( $api_settings['api_selected'] == 'instagram' ) {
      //$query.= "&max_tag_id=1334773328821"; 
    }
    
    if( $api_settings['api_selected'] == 'twitter' ) {
      $query.= "&rpp=100";
      $query.= "&result_type=mixed&include_entities=true";
      $query.= "&until=" . urlencode($api_settings['period']); 
      $query.= "&geocode=" . urlencode($api_settings['location']); 
    }
    
    if( $api_settings['api_selected'] == 'youtube' ) {
      $query.= "&max-results=50";
      $query.= "&v=2&alt=jsonc";
    }
    
    return $query;
  }

  function get_social_hashtag_options($option_num=null, $type='api'){
    $options = get_option( ($type=='api'?$this->social_api_options:$this->global_options) );    
    if ( !empty($options[$option_num]) ) {
      return $options[$option_num];
    }
    elseif( is_numeric($option_num) ){
      return $this->social_api_settings;
    }
    else {
      return $options;
    }
  }  
  
  function run_manually_hook() {
    if( isset($_REQUEST['run_social_hashtag_manually']) ) {
      if( $_REQUEST['debug'] ){ $this->debug = true; }
      $this->get_social_hashtag_pics($_REQUEST['num']);
      die();
    }
  }
  
  function add_cron_intervals( $schedules ) {
  	$schedules['five_minutes'] = array(
  		'interval' => 300,
  		'display' => __('[social_hashtag-cache] Five Minutes')
  	);
  	$schedules['ten_minutes'] = array(
  		'interval' => 600,
  		'display' => __('[social_hashtag-cache] Ten Minutes')
  	);
  	$schedules['thirty_minutes'] = array(
  		'interval' => 1800,
  		'display' => __('[social_hashtag-cache] Thirty Minutes')
  	);
  	return $schedules;
  }
  
  // this function either runs from cron or manually from admin - if it runs without a num, it does all the searches
  function get_social_hashtag_pics($num=null) {
    if( is_numeric($num) ) {
      $this->run_social_hashtag_query($num);
    }
    else{
      $social_api_options = $this->get_plugin_options();
      foreach( $social_api_options as $cache_num => $option_settings ){
        $this->run_social_hashtag_query($cache_num);
      }
    }
  }
  
  function remote_get_contents($url) {
    if (function_exists('curl_get_contents') AND function_exists('curl_init')){
      if($this->debug){print "\n- USING CURL \n";}
      return $this->curl_get_contents($url);
    }
    else{
      if($this->debug){print "\n- USING file_get_contents \n";}
      return file_get_contents($url);
    }
  }

  function curl_get_contents($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
  }

  function create_posttype_and_taxonomy() {
    register_post_type( 'social_hashtag',
      array(
        'labels' => array(
        'name' => __( 'Social Hashtags' ),
        'singular_name' => __( 'Social Hashtag' ),
        'add_new' => __( 'Add New Social Hashtag' ),
        'add_new_item' => __( 'Add New Social Hashtag' ),
        'edit_item' => __( 'Edit Social Hashtag' ),
        'new_item' => __( 'Add New Social Hashtag' ),
        'view_item' => __( 'View Social Hashtag' ),
        'search_items' => __( 'Search Social Hashtags' ),
        'not_found' => __( 'No social Hashtags found' ),
        'not_found_in_trash' => __( 'No social hashtag found in trash' )
      ),
      'public' => true,
      'supports' => array( 'title', 'thumbnail', 'editor', 'custom-fields'),
      'capability_type' => 'post',
      'has_archive' => 'social',
      'hierarchical' => false,
      'taxonomies' => array('social_hashtag_categories'),
      'rewrite' => array("slug" => "social"), // Permalinks format
      'menu_position' => '5'
      )
    );
    register_taxonomy(
    	'social_hashtag_categories',
    	'social_hashtag',
    	array(
    	'labels' => array(
    		'name' => 'Social Hashtag Categories',
    		'singular_name' => 'Social Hashtag Categories',
    		'search_items' => 'Search Social Hashtag Categories',
    		'popular_items' => 'Popular Social Hashtag Categories',
    		'all_items' => 'All Social Hashtag Categories',
    		'parent_item' => 'Parent Social Hashtag Categories',
    		'parent_item_colon' => 'Parent Social Hashtag Categories:',
    		'edit_item' => 'Edit Social Hashtag Category',
    		'update_item' => 'Update Social Hashtag Category',
    		'add_new_item' => 'Add New Social Hashtag Category',
    		'new_item_name' => 'New Social Hashtag Category Name'
    	),
    		'hierarchical' => true,
    		'label' => 'Social Hashtag Category',
    		'show_ui' => true,
    		'rewrite' => array( 'slug' => 'social-categories' ),
    	)
    );
    register_taxonomy(
    	'social_hashtag_tags',
    	'social_hashtag',
    	array(
    	'labels' => array(
    		'name' => 'Social Hashtag Tags',
    		'singular_name' => 'Social Hashtag Tags',
    		'search_items' => 'Search Social Hashtag Tags',
    		'popular_items' => 'Popular Social Hashtag Tags',
    		'all_items' => 'All Social Hashtag Tags',
    		'parent_item' => 'Parent Social Hashtag Tags',
    		'parent_item_colon' => 'Parent Social Hashtag Tags:',
    		'edit_item' => 'Edit Social Hashtag Tag',
    		'update_item' => 'Update Social Hashtag Tag',
    		'add_new_item' => 'Add New Social Hashtag Tag',
    		'new_item_name' => 'New Social Hashtag Tag Name'
    	),
    		'hierarchical' => false,
    		'label' => 'Social Hashtag Tag',
    		'show_ui' => true,
    		'update_count_callback' => '_update_post_term_count',
    		'rewrite' => array( 'slug' => 'social-tags' ),
    	)
    );
    add_filter('archive_template', 'social_hashtag_custom_archive_template');
    function social_hashtag_custom_archive_template($template) {
        global $wp_query;
        if (is_post_type_archive('social_hashtag')) {
            $template = SOCIAL_HASHTAG_DIR . 'lib/archive-social_hashtag.php';
            //$template = social_hashtag_locate_plugin_template($templates);
        }
        return $template;
    }
  }
  
  function display_social_hashtag_pics( $defaults ) {
    
    // cool masonry / fancybox display
    //http://www.queness.com/post/8881/create-a-twitter-feed-with-attached-images-from-media-entities
    
    $paged = ( get_query_var( 'paged' ) ) ? get_query_var('paged') : 1;
    $args = array(
      'post_type' => 'social_hashtag',
      //'cat' => $cat,
      //'offset' => $offset,
      'posts_per_page' => ($defaults['rows'] * $defaults['cols']),
      'orderby' => 'date',
      'order' => 'DESC',
      'paged' => $paged
    );

    $get_posts = new WP_Query($args);
    
    if( count($get_posts->posts) ){
    print "<div style='margin:20px;display:block;min-height:20px'>";
?>
    		<div class="next"><?php next_posts_link('Older Entries &raquo;', $get_posts->max_num_pages) ?></div>
    		<div class="prev"><?php previous_posts_link('&laquo; Newer Entries', $get_posts->max_num_pages) ?></div>
<?php 
    print "</div>\n";
    print "\n<div style='width:900px'>";
    print "\n<ul class='social_hashtag_pics'>\n";
    foreach($get_posts->posts as $num => $post){
      $social_hashtag_userhandle = get_post_meta($post->ID, 'social_hashtag_userhandle', true);
      $social_hashtag_thumb_url = get_post_meta($post->ID, 'social_hashtag_thumb_url', true);
      $social_hashtag_full_url = get_post_meta($post->ID, 'social_hashtag_full_url', true);
      $social_hashtag_platform = get_post_meta($post->ID, 'social_hashtag_platform', true);
      $social_hashtag_thumb_imagesize = get_post_meta($post->ID, 'social_hashtag_thumb_imagesize', true);
      print "\n\t<li class='{$social_hashtag_platform} {$social_hashtag_thumb_imagesize}'><a href='{$social_hashtag_full_url}' target=_blank><img src='{$social_hashtag_thumb_url}' title='{$post->post_title}' alt='{$post->post_title}' /></a></li>";
      //if( ($num+1) % $defaults['cols'] == 0 ){ print "<br class='clear'>";}      
    }
    print "</ul></div>\n";
    }
 
  }
  
  
 function display_social_hashtag_map( $defaults ) {
   
?>   
<script type="text/javascript">
jQuery(document).ready(function() {
  jQuery('#map_canvas').gmap().bind('init', function(evt, map) {
  	jQuery('#map_canvas').gmap('getCurrentPosition', function(position, status) {
  		if ( status === 'OK' ) {
  			var clientPosition = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
  			jQuery('#map_canvas').gmap('addMarker', {'position': clientPosition, 'bounds': true});
  			jQuery('#map_canvas').gmap('addShape', 'Circle', { 
  				'strokeWeight': 0, 
  				'fillColor': "#008595", 
  				'fillOpacity': 0.25, 
  				'center': clientPosition, 
  				'radius': 5, 
  				'clickable': false 
  			});
  		}
  	});   
  });
});
</script>
<div id="map_canvas"></div>

<?php   
   
 }
 
  
    
}
<?php
/*
Plugin Name: FV Clone Screen Options 
Plugin URI: http://foliovision.com/seo-tools/wordpress/plugins/fv-clone-screen-options
Description: Simple plugin which lets you manage Screen Options of all the users on your blog.
Version: 0.2
Author URI: http://foliovision.com

Copyright (c) 2009 Foliovision (http://foliovision.com)

Changelog:
0.2 - cloning - select a user and his settings will be cloned to everybody else
0.1 - Save button sets the settings to all the users, works only for posts

*/

//	what will be actually cloned? It's meta keys from usermeta
$fv_screen_options_array = array( 'metaboxhidden_post', 'closedpostboxes_post', 'screen_layout_post', 'wp_metaboxorder_post', 								'metaboxhidden_dashboard', 'closedpostboxes_dashboard', 'screen_layout_dashboard', 'wp_metaboxorder_dashboard',
								'metaboxhidden_page', 'closedpostboxes_page', 'screen_layout_page', 'wp_metaboxorder_page',
								'wp_usersettings'
);

function fv_screen_options() {

}

function fv_screen_options_page()
{
	if (function_exists('add_options_page'))
	{
		add_management_page('Clone Screen Options', 'Clone Screen Options', 'edit_pages', 'fv_screen_options_manage', 'fv_screen_options_manage');
	}
}

add_action('admin_init', 'fv_screen_options_head');

/*
Here's where the options are cloned
*/
function fv_screen_options_head() {
  if(stripos($_SERVER['REQUEST_URI'],'/tools.php?page=fv_screen_options_manage')!==FALSE) {
    global  $user_ID;
    
    /*  All the default widgets from edit-form-advanced.php */
    /*add_meta_box('submitdiv', __('Publish'), 'post_submit_meta_box', 'post', 'side', 'core'); 
    //add_meta_box('tagsdiv-' . $tax_name, $label, 'post_tags_meta_box', 'post', 'side', 'core');
      add_meta_box('tagsdiv-post_tag', 'Post Tags', 'post_tags_meta_box', 'post', 'side', 'core');
    add_meta_box('categorydiv', __('Categories'), 'post_categories_meta_box', 'post', 'side', 'core');
    add_meta_box('postexcerpt', __('Excerpt'), 'post_excerpt_meta_box', 'post', 'normal', 'core');
    add_meta_box('trackbacksdiv', __('Send Trackbacks'), 'post_trackback_meta_box', 'post', 'normal', 'core');
    add_meta_box('postcustom', __('Custom Fields'), 'post_custom_meta_box', 'post', 'normal', 'core');
    add_meta_box('commentstatusdiv', __('Discussion'), 'post_comment_status_meta_box', 'post', 'normal', 'core');
    add_meta_box('commentsdiv', __('Comments'), 'post_comment_meta_box', 'post', 'normal', 'core');
    add_meta_box('slugdiv', __('Post Slug'), 'post_slug_meta_box', 'post', 'normal', 'core');
    add_meta_box('authordiv', __('Post Author'), 'post_author_meta_box', 'post', 'normal', 'core');
    add_meta_box('revisionsdiv', __('Post Revisions'), 'post_revisions_meta_box', 'post', 'normal', 'core');
    add_meta_box('revisionsdiv', __('Post Revisions'), 'post_revisions_meta_box', 'post', 'normal', 'core');
    
    do_action('do_meta_boxes', 'post', 'normal', $post);
    do_action('do_meta_boxes', 'post', 'advanced', $post);
    do_action('do_meta_boxes', 'post', 'side', $post);
    
    /*  If user clicked Save button  */
    if(isset($_POST['save_post_screen_options'])) {
      check_ajax_referer( 'screen-options-nonce', 'screenoptionnonce' );
      $source_user_ID = 10;
      
      $source_user_ID = (int) $_POST['source_user'];
      
      if(!isset($source_user_ID) || $source_user_ID == '' )
      	return;
      
      global $fv_screen_options_array;
      $fv_screen_options_tmp = array();
      
      foreach( $fv_screen_options_array AS $metakey ) {
      		$fv_screen_options_tmp[$metakey] = get_usermeta($source_user_ID, $metakey);
      }
      
      /*  get all the users IDs and save the new settings for each one of them  */
      global $wpdb;
      $users = $wpdb->get_col("SELECT ID FROM {$wpdb->users} ORDER BY display_name");
      foreach( $users AS $userid) {
      	$user_object = new WP_User($userid);
				$roles = $user_object->roles;
				$role = array_shift($roles);
				if( $role == 'subscriber' )
					continue;
				
				foreach( $fv_screen_options_array AS $metakey ) {
      		update_usermeta($userid, $metakey, $fv_screen_options_tmp[$metakey]);
      	}
				
      }

      //	store for future use
      foreach( $fv_screen_options_array AS $metakey ) {
      	if( $fv_screen_options_tmp[$metakey] != '' )
      		update_option('fv_screen_options_'.$metakey, $fv_screen_options_tmp[$metakey]);
      	else
      		delete_option('fv_screen_options_'.$metakey);
      }

      header("Location: ".$_SERVER['REQUEST_URI']);
    }
  }
}

add_action('admin_menu', 'fv_screen_options_page');

function fv_screen_options_manage()
{
  /*  Display */
?>
  <div class="wrap">
    <div id="icon-tools" class="icon32"><br /></div>
      <h2>Screen Opinion</h2>

  	<form id="adv-settings" action="" method="post">
  	<?php _e('Clone settings for every user from') ?>
	
	<select name="source_user">
	
	<?php
	global $wpdb;
	$users = $wpdb->get_col("SELECT ID FROM {$wpdb->users} ORDER BY display_name");

	foreach( $users AS $userid) {
		$user_object = new WP_User($userid);
		$roles = $user_object->roles;
		$role = array_shift($roles);
		if( $role == 'contributor' OR $role == 'subscriber' )
			continue;
	?>
		<option value="<?php echo $user_object->ID; ?>"><?php echo $user_object->display_name; ?></option>
	<? } ?>
	</select>

    <?php wp_nonce_field( 'screen-options-nonce', 'screenoptionnonce', false ); ?><input type="submit" value="Clone" name="save_post_screen_options" />
    
    </form>
    
    <?php
    
    echo '<!--<h4>Stored configuration</h4>';
    
    foreach( $fv_screen_options_array AS $metakey ) {
    	echo '<h5>'.$metakey.'</h5>';
    	var_dump( get_option('fv_screen_options_'.$metakey) );
    }
    
    echo '-->';
      
    ?>

</div>
<?php
}

/*
When new user is registered he gets all the stored Screen Options
*/
function fv_screen_options_user_register($user_ID) {
	global $fv_screen_options_array;
	
	foreach( $fv_screen_options_array AS $metakey ) {
    	update_usermeta( $user_ID, $metakey, get_option('fv_screen_options_'.$metakey) );
  }
	
	return;
}

add_action('user_register', 'fv_screen_options_user_register');

?>

<?php
if( ! class_exists( 'YVIL_AJAX_Actions' ) ){
	require_once YVIL_PATH . 'includes/admin/libs/class-cbc-admin-ajax.php';
}
include_once YVIL_PATH . 'includes/admin/libs/page.abstract.php';
include_once YVIL_PATH . 'includes/admin/libs/page.interface.php';

if( ! class_exists( 'YVIL_Manual_Import_Page' ) ){
	include_once YVIL_PATH . 'includes/admin/libs/manual-import.page.class.php';
}
if( ! class_exists( 'YVIL_Settings_Page' ) ){
	include_once YVIL_PATH . 'includes/admin/libs/settings.page.class.php';
}
if( ! class_exists( 'YVIL_About_Page' ) ){
	include_once YVIL_PATH . 'includes/admin/libs/about.page.class.php';
}
if( ! class_exists( 'YVIL_Video_List_Page' ) ){
	include_once YVIL_PATH . 'includes/admin/libs/video-list.page.class.php';
}
if( ! class_exists( 'YVIL_Review_Callout' ) ){
	include_once YVIL_PATH . 'includes/admin/libs/review-callout.class.php';
}
if( !class_exists( 'YVIL_GoPRO_Page' ) ){
    include_once YVIL_PATH . 'includes/admin/libs/pro.page.class.php';
}

/**
 * Admin class, implements all admin interface
 */
class YVIL_Admin{

	/**
	 * Stores help screens info
	 * 
	 * @var array
	 */
	private $help_screens = array();

	/**
	 * Store main class plugin reference passed to constructor
	 * 
	 * @var YVIL_YouTube_Videos
	 */
	private $main;

	/**
	 * Stores rference of currently loaded admin page
	 * 
	 * @var YVIL_Page_Init
	 */
	private $plugin_page;
	/**
	 * @var YVIL_AJAX_Actions
	 */
	private $ajax;

	/**
	 * Constructor, sets up all hooks and
	 * filters needed for the plugin functionality.
	 */
	public function __construct( YVIL_YouTube_Videos $main ){
		// store main object reference
		$this->main = $main;
		// store AJAX class reference
		$this->ajax = new YVIL_AJAX_Actions( $main );

		// help screens
		add_filter( 'contextual_help', array( 
				$this, 
				'contextual_help' 
		), 10, 3 );
		
		// add extra menu pages
		add_action( 'admin_menu', array( 
				$this, 
				'menu_pages' 
		), 1 );
		
		// create edit meta boxes
		add_action( 'admin_head', array( 
				$this, 
				'add_meta_boxes' 
		) );
		// post thumbnails
		add_filter( 'admin_post_thumbnail_html', array( 
				$this, 
				'post_thumbnail_meta_panel' 
		), 10, 2 );
		// enqueue scripts/styles on post edit screen
		add_action( 'admin_enqueue_scripts', array( 
				$this, 
				'post_edit_assets' 
		) );
		
		// save data from meta boxes
		add_action( 'save_post', array( 
				$this, 
				'save_post' 
		), 10, 2 );
		add_action( 'load-post-new.php', array( 
				$this, 
				'post_new_onload' 
		) );
		
		// for empty imported posts, skip $maybe_empty verification
		add_filter( 'wp_insert_post_empty_content', array( 
				$this, 
				'force_empty_insert' 
		), 999, 2 );
		
		// add columns to posts table
		add_filter( 'manage_edit-' . $this->main->get_post_type() . '_columns', array(
				$this, 
				'extra_columns' 
		) );
		add_action( 'manage_' . $this->main->get_post_type() . '_posts_custom_column', array(
				$this, 
				'output_extra_columns' 
		), 10, 2 );
		
		// alert if setting to import as post type post by default is set on all plugin pages
		add_action( 'admin_notices', array( 
				$this, 
				'admin_notices' 
		) );
		// mechanism to remove the alert above
		add_action( 'admin_init', array( 
				$this, 
				'dismiss_post_type_notice' 
		) );
		// privacy policy
		add_action( 'admin_init', array(
			$this,
			'privacy_policy'
		) );
		// add tinyMCE buttons to allow easy shortcode management by tinyMCE plugin
		add_action( 'admin_head', array( 
				$this, 
				'tinymce' 
		) );
		
		// enqueue scripts/styles on post edit screen to allow video options editing
		add_action( 'admin_print_styles-post.php', array( 
				$this, 
				'post_edit_styles' 
		) );
		add_action( 'admin_print_styles-post-new.php', array( 
				$this, 
				'post_edit_styles' 
		) );
		
		// enqueue scripts in WP Widgets page to implement the video widget functionality
		add_action( 'admin_print_scripts-widgets.php', array( 
				$this, 
				'widgets_scripts' 
		) );
		
		add_action( 'admin_init', array( 
				$this, 
				'review_callout' 
		) );
		
		add_filter( 'plugin_action_links_' . plugin_basename( YVIL_PATH . 'main.php' ), array(
				$this, 
				'action_links' 
		) );
		
		add_action( 'admin_init', array(
				$this,
				'activation_redirect'
		) );
	}

	/**
	 * Display contextual help on plugin pages
	 */
	public function contextual_help( $contextual_help, $screen_id, $screen ){
		// if not hooks page, return default contextual help
		if( ! is_array( $this->help_screens ) || ! array_key_exists( $screen_id, $this->help_screens ) ){
			return $contextual_help;
		}
		
		// current screen help screens
		$help_screens = $this->help_screens[ $screen_id ];
		
		// create help tabs
		foreach( $help_screens as $help_screen ){
			$screen->add_help_tab( $help_screen );
		}
	}

	/**
	 * Add subpages on our custom post type
	 */
	public function menu_pages(){
		// add to post type video menu
		$parent_slug = 'edit.php?post_type=' . $this->main->get_post_type();
		
		// bulk manual import menu page
		$page = new YVIL_Manual_Import_Page( $this->main, $this->ajax );
		$video_import = add_submenu_page( $parent_slug, __( 'Import videos', 'yt-video-importer-lite' ), __( 'Import videos', 'yt-video-importer-lite' ), 'edit_posts', 'cbc_import', array(
				$page, 
				'get_html' 
		) );
		add_action( 'load-' . $video_import, array( 
				$page, 
				'on_load' 
		) );
		
		$page = new YVIL_Settings_Page( $this->main );
		// plugin settings menu page
		$settings = add_submenu_page( $parent_slug, __( 'Settings', 'yt-video-importer-lite' ), __( 'Settings', 'yt-video-importer-lite' ), 'manage_options', 'cbc_settings', array(
				$page, 
				'get_html' 
		) );
		add_action( 'load-' . $settings, array( 
				$page, 
				'on_load' 
		) );

		$page = new YVIL_GoPRO_Page( $this->main );
		$gopro = add_submenu_page( $parent_slug, __( 'Go PRO!', 'yt-video-importer-lite' ), __( 'Go PRO!', 'yt-video-importer-lite' ), 'edit_posts', 'go-pro', array(
		        $page,
                'get_html'
        ) );
		add_action( 'load-' . $gopro, array(
		        $page,
                'on_load'
        ) );

		$page = new YVIL_Video_List_Page( $this->main );
		// video list page
		$videos_list = add_submenu_page( null, __( 'Videos', 'yt-video-importer-lite' ), __( 'Videos', 'yt-video-importer-lite' ), 'edit_posts', 'cbc_videos', array(
				$page, 
				'get_html' 
		) );
		add_action( 'load-' . $videos_list, array( 
				$page, 
				'on_load' 
		) );
		
		/**
		 * Plugin about page. Shown on plugin activation only
		 * @var unknown
		 */
		$page = new YVIL_About_Page( $this->main );
		$about_page = add_submenu_page( null , __( 'About', 'yt-video-importer-lite' ), __( 'About', 'yt-video-importer-lite' ), 'activate_plugins', 'cbc_about', array(
			$page,
			'get_html'
		));
		add_action( 'load-' . $about_page, array(
				$page,
				'on_load'
		) );
	}

	/**
	 * admin_head callback
	 * Add meta boxes on video post type
	 */
	public function add_meta_boxes(){
		global $post;
		if( ! $post ){
			return;
		}
		
		// add meta boxes to video posts, either default post type is imported as such or video post type
		if( $this->main->is_video() ){
			add_meta_box( 'cbc-video-settings', __( 'Video settings', 'yt-video-importer-lite' ), array(
					$this, 
					'post_video_settings_meta_box' 
			), $post->post_type, 'normal', 'high' );
			
			add_meta_box( 'cbc-show-video', __( 'Live video', 'yt-video-importer-lite' ), array(
					$this, 
					'post_show_video_meta_box' 
			), $post->post_type, 'normal', 'high' );
		}else{ // for all other post types add only the shortcode embed panel
			add_meta_box( 'cbc-add-video', __( 'Video shortcode', 'yt-video-importer-lite' ), array(
					$this, 
					'post_shortcode_meta_box' 
			), $post->post_type, 'side' );
		}
	}

	/**
	 * Meta box callback.
	 * Displays video settings when editing posts.
	 */
	public function post_video_settings_meta_box(){
		global $post;
		$settings = yvil_get_video_settings( $post->ID );
		include_once YVIL_PATH . 'views/metabox-post-video-settings.php';
	}

	/**
	 * Meta box callback.
	 * Display live video meta box when editing posts
	 */
	public function post_show_video_meta_box(){
		global $post;
		$video_obj = $this->main->get_post_video_data( $post );
		
		if( !$video_obj ){
			return;
		}
		
		?>
<script language="javascript">
;(function($){
	$(document).ready(function(){
		$('#ccb-video-preview').CCB_VideoPlayer({
			'video_id' 	: '<?php echo $video_obj->get_id();?>',
			'source'	: 'youtube'
		});
	})
})(jQuery);
</script>
<div id="ccb-video-preview"
	style="height: 315px; width: 560px; max-width: 100%;"></div>
<?php
	}

	/**
	 * Meta box callback
	 * Post add shortcode meta box output
	 */
	public function post_shortcode_meta_box(){
		?>
<p><?php _e('Add video/playlist into post.', 'yt-video-importer-lite');?></p>
<a class="button" href="#" id="cbc-shortcode-2-post"
	title="<?php esc_attr_e( 'Add shortcode', 'yt-video-importer-lite' );?>"><?php _e( 'Add video shortcode', 'yt-video-importer-lite' );?></a>
<?php
	}

	/**
	 * admin_scripts callback
	 * Add scripts to custom post edit page
	 * 
	 * @param string $hook
	 */
	public function post_edit_assets( $hook ){
		if( 'post.php' !== $hook && 'post-new.php' !== $hook ){
			return;
		}
		
		global $post;
		if( ! $post ){
			return;
		}
		
		$is_new_video = isset( $this->video_post ) && $this->video_post;
		
		// check for video id to see if it was imported using the plugin
		$video_id = get_post_meta( $post->ID, '__cbc_video_id', true );
		if( ! $video_id && ! $is_new_video ){
			return;
		}
		
		// some files are needed only on custom post type edit page
		if( $this->main->is_video() || $is_new_video ){
			// add video player for video preview on post
			yvil_enqueue_player();
			wp_enqueue_script( 'cbc-video-edit', YVIL_URL . 'assets/back-end/js/video-edit.js', array(
					'jquery' 
			), '1.0' );
		}
		
		// video thumbnail functionality
		wp_enqueue_script( 'cbc-video-thumbnail', YVIL_URL . 'assets/back-end/js/video-thumbnail.js', array(
				'jquery' 
		), '1.0' );
		
		wp_localize_script( 'cbc-video-thumbnail', 'YVIL_POST_DATA', array(
				'post_id' => $post->ID 
		) );
	}

	/**
	 * Manipulate output for featured image on custom post
	 * to allow importing of thumbnail as featured image
	 */
	public function post_thumbnail_meta_panel( $content, $post_id ){
		$post = get_post( $post_id );
		
		if( ! $post ){
			return $content;
		}
		
		$video_id = get_post_meta( $post->ID, '__cbc_video_id', true );
		if( ! $video_id ){
			return $content;
		}
		
		$content .= sprintf( '<a href="#" id="cbc-import-video-thumbnail" class="button primary">%s</a>', __( 'Import YouTube thumbnail', 'yt-video-importer-lite' ) );
		return $content;
	}

	/**
	 * save_post callback
	 * Save post data from meta boxes.
	 * Hooked to save_post
	 */
	public function save_post( $post_id, $post ){
		if( ! isset( $_POST[ 'cbc-video-nonce' ] ) ){
			return;
		}
		
		// check if post is the correct type
		if( ! $this->main->is_video() ){
			return;
		}
		// check if user can edit
		if( ! current_user_can( 'edit_post', $post_id ) ){
			return;
		}
		// check nonce
		check_admin_referer( 'cbc-save-video-settings', 'cbc-video-nonce' );
		yvil_update_video_settings( $post_id );
	}

	/**
	 * New post load action for videos.
	 * Will first display a form to query for the video.
	 */
	public function post_new_onload(){
		if( ! isset( $_REQUEST[ 'post_type' ] ) || $this->main->get_post_type() !== $_REQUEST[ 'post_type' ] ){
			return;
		}
		
		/**
		 * Filter that can be used to prevent the plugin from overriding the single video import process.
		 * This is useful in rare cases when WP theme uses the same post type as the plugin.
		 * 
		 * @var bool
		 */
		$allow_plugin_import = apply_filters( 'cbc_allow_single_video_import', true );
		if( ! $allow_plugin_import ){
			_yvil_debug_message( 'Single post creation disabled by filter "cbc_allow_single_video_import". New single post cannot by created by plugin.' );
			return;
		}
		// store video details
		$this->video_post = false;
		
		if( isset( $_POST[ 'wp_nonce' ] ) ){
			if( check_admin_referer( 'cbc_query_new_video', 'wp_nonce' ) ){
				
				$video_id = sanitize_text_field( $_POST[ 'cbc_video_id' ] );
				
				// search if video already exists
				$posts = get_posts( array( 
						'post_type' => $this->main->get_post_type(),
						'meta_key' => '__cbc_video_id', 
						'meta_value' => $video_id, 
						'post_status' => array( 
								'publish', 
								'pending', 
								'draft', 
								'future', 
								'private' 
						) 
				) );
				if( $posts ){
					// if video was already imported redirect to post edit page without importing it again
					wp_redirect( get_edit_post_link( $posts[ 0 ]->ID, 'raw' ) );
					die();
				}
				
				$video = yvil_yt_api_get_video( $video_id );
				if( $video && ! is_wp_error( $video ) ){
					$this->video_post = $video;
					
					// apply filters on title and description
					$import_in_theme = isset( $_POST[ 'single_theme_import' ] ) && $_POST[ 'single_theme_import' ] ? yvil_check_theme_support() : array();
					$this->video_post->set_description( apply_filters( 'cbc_video_post_content', $this->video_post->get_description(), $this->video_post->to_array(), $import_in_theme ) );
					$this->video_post->set_title( apply_filters( 'cbc_video_post_title', $this->video_post->get_title(), $this->video_post->to_array(), $import_in_theme ) );

					add_filter( 'default_content', array( 
							$this, 
							'default_content' 
					), 999, 2 );
					add_filter( 'default_title', array( 
							$this, 
							'default_title' 
					), 999, 2 );
					add_filter( 'default_excerpt', array( 
							$this, 
							'default_excerpt' 
					), 999, 2 );
					
					// add video player for video preview on post
					yvil_enqueue_player();
				}else{
					$message = __( 'Video not found.', 'yt-video-importer-lite' );
					if( is_wp_error( $video ) ){
						$message = sprintf( __( 'An error occured while trying to query YouTube API.<br />Error: %s (code: %s)', 'yt-video-importer-lite' ), $video->get_error_message(), $video->get_error_code() );
					}
					global $YVIL_NEW_VIDEO_NOTICE;
					$YVIL_NEW_VIDEO_NOTICE = $message;
					add_action( 'all_admin_notices', array( 
							$this, 
							'new_post_error_notice' 
					) );
				}
			}else{
				wp_die( 'Cheatin uh?' );
			}
		}
		// if video query not started, display the form
		if( ! $this->video_post ){
			wp_enqueue_script( 'cbc-new-video-js', YVIL_URL . 'assets/back-end/js/video-new.js', array(
					'jquery' 
			), '1.0' );
			
			$post_type_object = get_post_type_object( $this->main->get_post_type() );
			$title = $post_type_object->labels->add_new_item;
			
			include ABSPATH . 'wp-admin/admin-header.php';
			include YVIL_PATH . 'views/new_video.php';
			include ABSPATH . 'wp-admin/admin-footer.php';
			die();
		}
	}

	/**
	 * Callback function that displays admin error message when importing single videos.
	 * Action is set in function $this->post_new_onload()
	 */
	public function new_post_error_notice(){
		global $YVIL_NEW_VIDEO_NOTICE;
		if( $YVIL_NEW_VIDEO_NOTICE ){
			echo '<div class="error"><p>' . $YVIL_NEW_VIDEO_NOTICE . '</p></div>';
		}
	}

	/**
	 * Set video description on new post
	 * 
	 * @param string $post_content
	 * @param object $post
	 */
	public function default_content( $post_content, $post ){
		if( ! isset( $this->video_post ) || ! $this->video_post ){
			return;
		}
		
		return $this->video_post->get_description();
	}

	/**
	 * Set video title on new post
	 * 
	 * @param string $post_title
	 * @param object $post
	 */
	public function default_title( $post_title, $post ){
		if( ! isset( $this->video_post ) || ! $this->video_post ){
			return;
		}
		
		return $this->video_post->get_title();
	}

	/**
	 * Set video excerpt on new post, add taxonomies and save meta
	 * 
	 * @param string $post_excerpt
	 * @param object $post
	 */
	public function default_excerpt( $post_excerpt, $post ){
		if( ! isset( $this->video_post ) || ! $this->video_post ){
			return;
		}
		// set video ID on post meta
		update_post_meta( $post->ID, '__cbc_video_id', $this->video_post->get_id() );
		// needed by other plugins
		update_post_meta( $post->ID, '__cbc_video_url', 'https://www.youtube.com/watch?v=' . $this->video_post->get_id() );
		// save video data on post
		update_post_meta( $post->ID, '__cbc_video_data', $this->video_post->to_array() );
		
		// import video thumbnail as featured image
		$settings = yvil_get_settings();

		// action on post insert that allows setting of different meta on post
		do_action( 'cbc_before_post_insert', $this->video_post->to_array(), false );

		$plugin_taxonomy = $this->main->get_post_tax();

        // check if category exists
        $term = term_exists( $this->video_post->get_category(), $plugin_taxonomy );
        if( 0 == $term || null == $term ){
            // create the category
            $term = wp_insert_term( $this->video_post->get_category(), $plugin_taxonomy );
        }
        // add category to video
        wp_set_post_terms( $post->ID, array(
                $term[ 'term_id' ]
        ), $plugin_taxonomy );

        // on default imports, set post format to video
        set_post_format( $post->ID, 'video' );
		
		// action on post insert that allows setting of different meta on post
		// consistent with action on bulk import
		do_action( 'cbc_post_insert', $post->ID, $this->video_post->to_array(), false, $post->post_type );
		if( isset( $redirect ) ){
			wp_redirect( $redirect );
			die();
		}
	}

	/**
	 * When trying to insert an empty post, WP is running a filter.
	 * Given the fact that
	 * users are allowed to insert empty posts when importing, the filter will return
	 * false on maybe_empty to allow insertion of video.
	 * 
	 * @param bool $maybe_empty
	 * @param array $postarr
	 */
	public function force_empty_insert( $maybe_empty, $postarr ){
		if( $this->main->get_post_type() == $postarr[ 'post_type' ] ){
			return false;
		}
	}

	/**
	 * Extra columns in videos list table
	 * 
	 * @param array $columns
	 */
	public function extra_columns( $columns ){
		$cols = array();
		foreach( $columns as $c => $t ){
			$cols[ $c ] = $t;
			if( 'title' == $c ){
				$cols[ 'video_id' ] = __( 'Video ID', 'yt-video-importer-lite' );
				$cols[ 'duration' ] = __( 'Duration', 'yt-video-importer-lite' );
			}
		}
		return $cols;
	}

	/**
	 * Extra columns in videos list table output
	 * 
	 * @param string $column_name
	 * @param int $post_id
	 */
	public function output_extra_columns( $column_name, $post_id ){
		switch( $column_name ){
			case 'video_id':
				echo get_post_meta( $post_id, '__cbc_video_id', true );
			break;
			case 'duration':
				$video_obj = $this->main->get_post_video_data( $post_id );
				
				if( ! $video_obj ){
					echo '-';
				}else{
					echo $video_obj->get_human_duration();
				}
			break;
		}
	}

	/**
	 * Display an alert to user when he chose to import videos by default as regular posts
	 */
	public function admin_notices(){
		if( ! is_admin() || ! current_user_can( 'manage_options' ) ){
			return;
		}
		global $pagenow;
		if( ! 'edit.php' == $pagenow || ! isset( $_GET[ 'post_type' ] ) || $this->main->get_post_type() != $_GET[ 'post_type' ] ){
			return;
		}
		
		$php_version = phpversion();
		$show_version_warning = apply_filters( 'cbc_show_php_version_warning' , true );
		if( version_compare( '7.0' , $php_version, '>' ) && $show_version_warning ){
?>
<div class="error">
	<p>
		<?php printf( __('Please note that your server PHP version is %s and is currently not actively supported.', 'yt-video-importer-lite' ), $php_version );?>
		<?php _e( 'We strongly suggest that you (or your hosting provider) update PHP on your server to at least version 7.0.', 'yt-video-importer-lite' );?>
		<?php printf( __( 'More details on PHP support can be found %shere%s.', 'yt-video-importer-lite' ), '<a href="http://php.net/supported-versions.php" target="_blank">', '</a>' );?>
	</p>
</div>
<?php 
		}
		
		// alert user to insert his YouTube API Key.
		$oauth_token = yvil_get_oauth_token();
		
		if( is_wp_error( $oauth_token ) ){
			?>
<div class="error">
	<p>
		<?php _e( 'In order to be able to import YouTube videos using the plugin you must enter your YouTube OAuth credentials.', 'yt-video-importer-lite' );?><br />
		<?php _e( 'Please navigate to plugin <strong>Settings</strong> page, tab <strong>API & License</strong> and enter your <strong>Client ID and Secret</strong>.', 'yt-video-importer-lite' );?><br />
	</p>
	<p>
		<a href="<?php echo yvil_docs_link( 'getting-started/set-youtube-oauth-client-id-client-secret/' );?>" target="_blank"
			class="button button-primary"><?php _e( 'See tutorial' );?></a>
		<?php if( !is_a( $this->get_plugin_page() , 'YVIL_Settings_Page' ) ):?>
			<a class="button"
			href="<?php menu_page_url( 'cbc_settings', true );?>#cbc-settings-auth-options"><?php _e( 'Plugin Settings', 'yt-video-importer-lite' );?></a>
		<?php endif;?>
	</p>
</div>
<?php
			// stop all other messages if API key is missing
			return;
		} // close if
		  
		$settings = yvil_get_settings();
		if( isset( $settings[ 'show_quota_estimates' ] ) && $settings[ 'show_quota_estimates' ] ){
			echo '<div class="updated"><p>';
			yvil_yt_quota_message();
			echo '</p></div>';
		}
	}

	/**
	 * Dismiss regular post import notice
	 */
	public function dismiss_post_type_notice(){
		if( ! is_admin() ){
			return;
		}
		
		if( isset( $_GET[ 'cbc_dismiss_post_type_notice' ] ) && 1 == $_GET[ 'cbc_dismiss_post_type_notice' ] ){
			global $current_user;
			$user_id = $current_user->ID;
			add_user_meta( $user_id, 'cbc_ignore_post_type_notice', true );
		}
	}

	/**
	 * Privacy policy
	 */
	public function privacy_policy(){
		if( !function_exists( 'wp_add_privacy_policy_content' ) ){
			return;
		}

		$policy_content = sprintf(
			__( 'By using the embed feature of this plugin you will be agreeing to Google\'s privacy policy. More details can be found here: %s', 'yt-video-importer-lite' ),
			'http://www.google.com/intl/en-GB/policies/privacy/'
		);

		wp_add_privacy_policy_content( 'YouTube Video Importer Lite', $policy_content );
	}

	/**
	 * Add tinyce buttons to easily embed video playlists
	 */
	public function tinymce(){
		// Don't bother doing this stuff if the current user lacks permissions
		if( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) )
			return;
			
			// Don't load unless is post editing (includes post, page and any custom posts set)
		$screen = get_current_screen();
		
		if( ! $screen || 'post' != $screen->base || $this->main->is_video() ){
			return;
		}
		
		// Add only in Rich Editor mode
		if( get_user_option( 'rich_editing' ) == 'true' ){
			
			wp_enqueue_script( array( 
					'jquery-ui-dialog' 
			) );
			
			wp_enqueue_style( array( 
					'wp-jquery-ui-dialog' 
			) );
			
			add_filter( 'mce_external_plugins', array( 
					$this, 
					'tinymce_plugin' 
			) );
			add_filter( 'mce_buttons', array( 
					$this, 
					'register_buttons' 
			) );
		}
	}

	/**
	 * Register tinymce plugin
	 * 
	 * @param array $plugin_array
	 */
	public function tinymce_plugin( $plugin_array ){
		$plugin_array[ 'yvil_shortcode' ] = YVIL_URL . 'assets/back-end/js/tinymce/shortcode.js';
		return $plugin_array;
	}

	/**
	 * Register tinymce buttons
	 * 
	 * @param array $buttons
	 */
	public function register_buttons( $buttons ){
		array_push( $buttons, 'separator', 'yvil_shortcode' );
		return $buttons;
	}

	/**
	 * Load styling on post edit screen
	 */
	public function post_edit_styles(){
		global $post;
		if( ! $post || $this->main->is_video( $post ) ){
			return;
		}
		
		wp_enqueue_style( 'ccb-shortcode-modal', YVIL_URL . 'assets/back-end/css/shortcode-modal.css', false, '1.0' );
		
		wp_enqueue_script( 'ccb-shortcode-modal', YVIL_URL . 'assets/back-end/js/shortcode-modal.js', false, '1.0' );
		
		$messages = array( 
				'playlist_title' => __( 'Videos in playlist', 'yt-video-importer-lite' ),
				'no_videos' => __( 'No videos selected.<br />To create a playlist check some videos from the list on the right.', 'yt-video-importer-lite' ),
				'deleteItem' => __( 'Delete from playlist', 'yt-video-importer-lite' ),
				'insert_playlist' => __( 'Add shortcode into post', 'yt-video-importer-lite' )
		);
		
		wp_localize_script( 'ccb-shortcode-modal', 'YVIL_SHORTCODE_MODAL', $messages );
	}

	/**
	 * Enqueue some scripts on WP widgets page
	 */
	public function widgets_scripts(){
		$plugin_settings = yvil_get_settings();
		if( isset( $plugin_settings[ 'public' ] ) && ! $plugin_settings[ 'public' ] ){
			return;
		}
		
		wp_enqueue_script( 'cbc-video-edit', YVIL_URL . 'assets/back-end/js/video-edit.js', array(
				'jquery' 
		), '1.0' );
	}

	public function review_callout(){
		$m = "It's great to see that you've been using <strong>WordPress YouTube Hub</strong> plugin for a while now. Hopefully you're happy with it! <br>If so, would you consider leaving a positive review? It really helps to support the plugin and helps others to discover it too!";
		$user = new YVIL_User( 'cbc_ignore_review_nag', 'manage_options' );
		$message = new YVIL_Message( $m, 'https://wordpress.org/support/plugin/youtube-video-importer-lite/reviews/#new-post' );
		new YVIL_Review_Callout( 'cbc_plugin_review_callout', $message, $user );
	}

	/**
	 * Add various action links to plugin menu in Plugins page
	 * 
	 * @param array $links
	 */
	public function action_links( $links ){
		$template = '<a href="%s" target="%s">%s</a>';
		
		$extra = array( 
				sprintf( $template, menu_page_url( 'cbc_settings', false ), '_self', __( 'Settings', 'yt-video-importer-lite' ) ),
				sprintf( $template, yvil_docs_link( 'getting-started/' ), '_blank', __( 'Documentation', 'yt-video-importer-lite' ) ),
				sprintf( $template, yvil_link( 'video/wp-youtube-hub-plugin-installation-tutorial/' ), '_blank', __( 'First time installation', 'yt-video-importer-lite' ) )
		);
		
		return array_merge( $links, $extra );
	}

	/**
	 * Triggered on plugin activation
	 */
	public function plugin_activation(){
		set_transient( 'cbc_plugin_activation' , true, 30 );
	}
	
	/**
	 * Admin init callback, redirects to plugin Settings page after plugin activation.
	 */
	public function activation_redirect(){
		$t = get_transient( 'cbc_plugin_activation' );
		if( $t ){
			delete_transient( 'cbc_plugin_activation' );
			wp_redirect( str_replace( '#038;' , '&', menu_page_url( 'cbc_about', false ) ) );
			die();
		}
	}
	
	/**
	 * Set the currently loaded plugin page
	 * 
	 * @param YVIL_Page_Init $object
	 */
	public function __set_current_page( YVIL_Page_Init $object ){
		$this->plugin_page = $object;
	}

	/**
	 * Get currently loaded plugin page
	 * 
	 * @return YVIL_Page_Init
	 */
	public function get_plugin_page(){
		return $this->plugin_page;
	}
}
<?php

/**
 * Creates from a number of given seconds a readable duration ( HH:MM:SS )
 * 
 * @param int $seconds
 */
function yvil_human_time( $seconds ){
	$seconds = absint( $seconds );
	
	if( $seconds < 0 ){
		return;
	}
	
	$h = floor( $seconds / 3600 );
	$m = floor( $seconds % 3600 / 60 );
	$s = floor( $seconds % 3600 % 60 );
	
	return ( ( $h > 0 ? $h . ":" : "" ) . ( ( $m < 10 ? "0" : "" ) . $m . ":" ) . ( $s < 10 ? "0" : "" ) . $s );
}

/**
 * Utility function.
 * Checks if a given or current post is video created by the plugin
 * 
 * @param object $post
 */
function yvil_is_video( $post = false ){
	$obj = yvil_get_class_instance();
	return $obj->is_video( $post );
}

/**
 * @param WP_Post/integer $post
 *
 * @return YVIL_Video
 */
function yvil_get_video_data( $post ){
	$obj = yvil_get_class_instance();
	return $obj->get_post_video_data( $post );
}

/**
 * Adds video player script to page
 */
function yvil_enqueue_player(){
	wp_enqueue_script( 'ccb-video-player', YVIL_URL . 'assets/front-end/js/video-player.js', array(
			'jquery'
	), '1.0' );
	
	wp_enqueue_style( 'ccb-video-player', YVIL_URL . 'assets/front-end/css/video-player.css' );
}

/**
 * Utility function, returns plugin default settings
 */
function yvil_load_plugin_options(){
	
	if( !class_exists( 'YVIL_Plugin_Options' ) ){
		require_once YVIL_PATH . 'includes/libs/options.class.php';
	}
	
	$defaults = array( 
			'public' => true,  // post type is public or not
			'archives' => false,  // display video embed on archive pages
			'use_microdata' => NULL,  // put microdata on video pages ( more details on: http://schema.org )
            // rewrite
			'post_slug' => 'video', 
			'taxonomy_slug' => 'videos', 
			'tag_taxonomy_slug' => 'video-tag', 
			// bulk import
			'import_categories' => true,  // import categories from YouTube
			'import_tags' => false, 
			'max_tags' => 1,  // maximum number of tags to import
			'import_title' => true,  // import titles on custom posts
			'import_description' => 'post_content',  // import descriptions on custom posts
			'remove_after_text' => '',  // descriptions that have this content will be truncated up to this text
			'prevent_autoembed' => false,  // prevent autoembeds on video posts
			'make_clickable' => false,  // make urls pasted in content clickable
			'image_size' => 'standard',  // image size to set on posts
			'maxres' => false,  // when importing thumbnails, try to get the maximum resolution if available
			'import_results' => 100,  // default number of feed results to display
			'import_status' => 'draft',  // default import status of videos
            // automatic import
			'manual_import_per_page' => 20,
			// quota
			'show_quota_estimates' => true
	);
	
	$options = new YVIL_Plugin_Options( '_cbc_plugin_settings', $defaults );
	return $options;
}

/**
 * Utility function, returns plugin settings
 */
function yvil_get_settings(){
	$options = yvil_load_plugin_options();
	return $options->get_options();
}

/**
 * Global player settings defaults.
 */
function yvil_player_settings_defaults(){
	$defaults = array( 
			'controls' => 1,  // show player controls. Values: 0 or 1
			'autohide' => 0,  // 0 - always show controls; 1 - hide controls when playing; 2 - hide progress bar when playing
			'fs' => 1,  // 0 - fullscreen button hidden; 1 - fullscreen button displayed
			'theme' => 'dark',  // dark or light
			'color' => 'red',  // red or white
			
			'iv_load_policy' => 1,  // 1 - show annotations; 3 - hide annotations
			'modestbranding' => 1,  // 1 - small branding
			'rel' => 1,  // 0 - don't show related videos when video ends; 1 - show related videos when video ends
			'showinfo' => 0,  // 0 - don't show video info by default; 1 - show video info in player
			
			'autoplay' => 0,  // 0 - on load, player won't play video; 1 - on load player plays video automatically
			                 // 'loop' => 0, // 0 - video won't start again once finished; 1 - video will play again once finished
			
			'disablekb' => 0,  // 0 - allow keyboard controls; 1 - disable keyboard controls
			                  
			// extra settings
			'aspect_ratio' => '16x9', 
			'width' => 640, 
			'video_position' => 'below-content',  // in front-end custom post, where to display the video: above or below post content
			'volume' => 100 
	); // video default volume
	
	return $defaults;
}

/**
 * Get general player settings
 */
function yvil_get_player_settings(){
	$defaults = yvil_player_settings_defaults();
	$option = get_option( '_cbc_player_settings', $defaults );
	
	foreach( $defaults as $k => $v ){
		if( ! isset( $option[ $k ] ) ){
			$option[ $k ] = $v;
		}
	}
	
	// various player outputs may set their own player settings. Return those.
	global $YVIL_PLAYER_SETTINGS;
	if( $YVIL_PLAYER_SETTINGS ){
		foreach( $option as $k => $v ){
			if( isset( $YVIL_PLAYER_SETTINGS[ $k ] ) ){
				$option[ $k ] = $YVIL_PLAYER_SETTINGS[ $k ];
			}
		}
	}
	
	return $option;
}

/**
 * Calculate player height from given aspect ratio and width
 * 
 * @param string $aspect_ratio
 * @param int $width
 */
function yvil_player_height( $aspect_ratio, $width ){
	$width = absint( $width );
	$height = 0;
	switch( $aspect_ratio ){
		case '4x3':
			$height = ( $width * 3 ) / 4;
		break;
		case '16x9':
		default:
			$height = ( $width * 9 ) / 16;
		break;
	}
	return $height;
}


/**
 * Outputs the HTML for embedding videos on single posts.
 *
 * @return string
 */
function yvil_video_embed_html( $echo = true, $enqueue_scripts = true ){

	global $post;
	if( !$post ){
		return;
	}
	
	$obj = yvil_get_class_instance();
	
	$settings	= yvil_get_video_settings( $post->ID, true );
	$video		= $obj->get_post_video_data( $post );

	if( !$video instanceof YVIL_Video ){
		return;
	}

	$settings['video_id'] = $video->get_id();
	// player size
	$width = $settings[ 'width' ];
	$height = yvil_player_height( $settings[ 'aspect_ratio' ], $width );
	
	/**
	 * Filter that allows adding extra CSS classes on video container
	 * for styling.
	 * 
	 * @param array $classes - array of CSS classes
	 * @param WP_Post $post - the post object
	 */
	$class = apply_filters( 'cbc_embed_css_class', array(), $post );
	$extra_css = implode( ' ', $class );
	
	/**
	 * Filter that allows changing of embed settings before displaying the video
	 * @param array - embed settings
	 * @param WP_Post $post - the post object
	 * @param array $video - the video details
	 */
	$settings = apply_filters( 'cbc_video_post_embed_options', $settings, $post, $video->to_array() );
	
	// the video container
	$video_container = '<div class="ccb_single_video_player ' . $extra_css . '" ' . yvil_data_attributes( $settings ) . ' style="width:' . $width . 'px; height:' . $height . 'px; max-width:100%;"><!-- player container --></div>';
	
	/**
	 * Apply a filter on the video container to allow third party scripts to modify the output if needed
	 *
	 * @param string/HTML $video_container - the video container output
	 * @param WP_Post $post - the post object
	 * @param array $video - the video details
	 * @param array $settings - video options
	 */
	$video_container = apply_filters( 'cbc_embed_html_container', $video_container, $post, $video->to_array(), $settings );
	
	if( $enqueue_scripts ){
		yvil_enqueue_player();
	}
	
	if( $echo ){
		echo $video_container;
	}
	
	return $video_container;
}

/**
 * Single post default settings
 */
function yvil_post_settings_defaults(){
	// general player settings
	$plugin_defaults = yvil_get_player_settings();
	return $plugin_defaults;
}

/**
 * Returns playback settings set on a video post
 */
function yvil_get_video_settings( $post_id = false, $output = false ){
	if( ! $post_id ){
		global $post;
		if( ! $post || ! yvil_is_video( $post ) ){
			return false;
		}
		$post_id = $post->ID;
	}else{
		$post = get_post( $post_id );
		if( ! $post || ! yvil_is_video( $post ) ){
			return false;
		}
	}
	
	$defaults = yvil_post_settings_defaults();
	$option = get_post_meta( $post_id, '__cbc_playback_settings', true );
	if( !$option ){
		return $defaults;
	}
	foreach( $defaults as $k => $v ){
		if( ! isset( $option[ $k ] ) ){
			$option[ $k ] = $v;
		}
	}
	
	if( $output ){
		foreach( $option as $k => $v ){
			if( is_bool( $v ) ){
				$option[ $k ] = absint( $v );
			}
		}
	}
	
	return $option;
}

/**
 * Utility function, updates video settings
 */
function yvil_update_video_settings( $post_id ){
	if( ! $post_id ){
		return false;
	}
	
	$post = get_post( $post_id );
	if( ! $post || ! yvil_is_video( $post ) ){
		return false;
	}
	
	$defaults = yvil_post_settings_defaults();
	foreach( $defaults as $key => $val ){
		if( is_numeric( $val ) ){
			if( isset( $_POST[ $key ] ) ){
				$defaults[ $key ] = ( int ) $_POST[ $key ];
			}else{
				$defaults[ $key ] = 0;
			}
			continue;
		}
		if( is_bool( $val ) ){
			$defaults[ $key ] = isset( $_POST[ $key ] );
			continue;
		}
		
		if( isset( $_POST[ $key ] ) ){
			$defaults[ $key ] = $_POST[ $key ];
		}
	}
	
	update_post_meta( $post_id, '__cbc_playback_settings', $defaults );
}

/**
 * Set thumbnail as featured image for a given post ID
 * 
 * @param int $post_id
 */
function yvil_set_featured_image( $post_id, $video_meta = false ){
	if( ! $post_id ){
		return false;
	}
	
	$post = get_post( $post_id );
	if( ! $post ){
		return false;
	}
	
	// try to get video details
	if( ! $video_meta ){
		$obj = yvil_get_class_instance();
		$video_meta = $obj->get_post_video_data( $post_id );
		if( ! $video_meta ){
			// if meta isn't found, try to get video ID and retrieve the meta
			$video_id = get_post_meta( $post_id, '__cbc_video_id', true );
			// video ID not found, give up
			if( $video_id ){
				// query the video
				$video = yvil_yt_api_get_video( $video_id );
				if( $video && ! is_wp_error( $video ) ){
					$video_meta = $video;
				}
			}
		}
	}else if( !$video_meta instanceof YVIL_Video  ){
		$video_meta = new YVIL_Video( $video_meta );
	}
	
	// check that thumbnails exist to avoid issues
	if( !$video_meta instanceof YVIL_Video || ! $video_meta->get_thumbnails() ){
		return false;
	}
	
	// check if thumbnail was already imported
	$attachment = get_posts( array( 
			'post_type' => 'attachment', 
			'meta_key' => 'video_thumbnail', 
			'meta_value' => $video_meta->get_id()
	) );
	// if thumbnail exists, return it
	if( $attachment ){
		// set image as featured for current post
		set_post_thumbnail( $post_id, $attachment[ 0 ]->ID );
		return array( 
				'post_id' => $post_id, 
				'attachment_id' => $attachment[ 0 ]->ID 
		);
	}
	
	// get the thumbnail URL
	$settings = yvil_get_settings();
	$img_size = yvil_get_image_size();
	if( $video_meta->get_thumbnail_url( $img_size ) ){
		$thumb_url = $video_meta->get_thumbnail_url( $img_size );
	}else{
		$thumb_url = $video_meta->get_thumbnail_url();
	}
	
	// get max resolution image if available
	if( isset( $settings[ 'maxres' ] ) && $settings[ 'maxres' ] ){
		$maxres_url = 'http://img.youtube.com/vi/' . $video_meta->get_id() . '/maxresdefault.jpg';
		$maxres_result = wp_remote_get( $maxres_url, array( 
				'sslverify' => false,
				'timeout' 	=> apply_filters( 'cbc_image_import_timeout', 15 )
		) );
		if( ! is_wp_error( $maxres_result ) && 200 == wp_remote_retrieve_response_code( $maxres_result ) ){
			$response = $maxres_result;
		}
	}
	
	// if max resolution query wasn't successful, try to get the registered image size
	if( ! isset( $response ) ){
		$response = wp_remote_get( $thumb_url, array( 
				'sslverify' => false,
				'timeout' 	=> apply_filters( 'cbc_image_import_timeout', 15 )
		) );
		if( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ){
			return false;
		}
	}
	
	// set up image details
	$image_contents = $response[ 'body' ];
	$image_type = wp_remote_retrieve_header( $response, 'content-type' );
	$image_extension = false;
	switch( $image_type ){
		case 'image/jpeg':
			$image_extension = '.jpg';
		break;
		case 'image/png':
			$image_extension = '.png';
		break;
	}
	// no valid image extension, stop here
	if( ! $image_extension ){
		return;
	}
	
	// Construct a file name using post slug and extension
	$fname = urldecode( basename( get_permalink( $post_id ) ) );
	// make suffix optional
	$suffix_filename = apply_filters( 'cbc_apply_filename_suffix', true );
	$suffix = $suffix_filename ? '-youtube-thumbnail' : '';
	// construct new file name
	$new_filename = preg_replace( '/[^A-Za-z0-9\-]/', '', $fname ) . $suffix . $image_extension;
	
	// Save the image bits using the new filename
	$upload = wp_upload_bits( $new_filename, null, $image_contents );
	if( $upload[ 'error' ] ){
		return false;
	}
	
	$image_url = $upload[ 'url' ];
	$filename = $upload[ 'file' ];
	
	$wp_filetype = wp_check_filetype( basename( $filename ), null );
	$attachment = array( 
			'post_mime_type' => $wp_filetype[ 'type' ], 
			'post_title' => get_the_title( $post_id ), 
			'post_content' => '', 
			'post_status' => 'inherit', 
			'guid' => $upload[ 'url' ] 
	);
	$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
	// you must first include the image.php file
	// for the function wp_generate_attachment_metadata() to work
	require_once ( ABSPATH . 'wp-admin/includes/image.php' );
	$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
	wp_update_attachment_metadata( $attach_id, $attach_data );
	
	// Add field to mark image as a video thumbnail
	update_post_meta( $attach_id, 'video_thumbnail', $video_meta->get_id() );
	
	// set image as featured for current post
	update_post_meta( $post_id, '_thumbnail_id', $attach_id );
	
	return array( 
			'post_id' => $post_id, 
			'attachment_id' => $attach_id 
	);
}

/**
 * Returns size of image that should be imported
 */
function yvil_get_image_size(){
	// plugin settings
	$settings = yvil_get_settings();
	// allowed image sizes
	$sizes = array( 
			'default', 
			'medium', 
			'high', 
			'standard', 
			'maxres' 
	);
	// set default to standard
	$img_size = 'standard';
	
	if( isset( $settings[ 'image_size' ] ) ){
		if( in_array( $settings[ 'image_size' ], $sizes ) ){
			$img_size = $settings[ 'image_size' ];
		}else{
			// old sizes
			switch( $settings[ 'image_size' ] ){
				case 'mqdefault':
					$img_size = 'medium';
				break;
				case 'hqdefault':
					$img_size = 'high';
				break;
				case 'sddefault':
					$img_size = 'stadard';
				break;
			}
		}
	}
	return $img_size;
}

/**
 * Outputs a plugin playlist.
 * 
 * @param unknown_type $videos
 * @param unknown_type $results
 * @param unknown_type $theme
 * @param unknown_type $player_settings
 * @param unknown_type $taxonomy
 */
function yvil_output_playlist( $videos = 'latest', $results = 5, $theme = 'default', $player_settings = array(), $taxonomy = false ){
	$obj = yvil_get_class_instance();
	$args = array( 
			'post_type' => array( 
					$obj->get_post_type(), 
					'post' 
			), 
			'posts_per_page' => absint( $results ), 
			'numberposts' => absint( $results ), 
			'post_status' => 'publish', 
			'supress_filters' => true 
	);
	
	// taxonomy query
	if( ! is_array( $videos ) && isset( $taxonomy ) && ! empty( $taxonomy ) && ( ( int ) $taxonomy ) > 0 ){
		$term = get_term( $taxonomy, $obj->get_post_tax(), ARRAY_A );
		if( ! is_wp_error( $term ) ){
			$args[ $obj->get_post_tax() ] = $term[ 'slug' ];
		}
	}
	
	// if $videos is array, the function was called with an array of video ids
	if( is_array( $videos ) ){
		
		$ids = array();
		foreach( $videos as $video_id ){
			$ids[] = absint( $video_id );
		}
		$args[ 'include' ] = $ids;
		$args[ 'posts_per_page' ] = count( $ids );
		$args[ 'numberposts' ] = count( $ids );
	}elseif( is_string( $videos ) ){
		
		$found = false;
		switch( $videos ){
			case 'latest':
				$args[ 'orderby' ] = 'post_date';
				$args[ 'order' ] = 'DESC';
				$found = true;
			break;
		}
		if( ! $found ){
			return;
		}
	}else{ // if $videos is anything else other than array or string, bail out
		return;
	}
	
	// get video posts
	$posts = get_posts( $args );
	
	if( ! $posts ){
		return;
	}
	
	$videos = array();
	foreach( $posts as $post_key => $post ){
		
		if( ! yvil_is_video( $post ) ){
			continue;
		}
		
		if( isset( $ids ) ){
			$key = array_search( $post->ID, $ids );
		}else{
			$key = $post_key;
		}
		
		if( is_numeric( $key ) ){
			$videos[ $key ] = array( 
					'ID' => $post->ID, 
					'title' => $post->post_title, 
					// @todo - see how the video meta could be used here
					'video_data' => $obj->get_post_video_data( $post->ID ) 
			);
		}
	}
	ksort( $videos );
	
	ob_start();
	
	// set custom player settings if any
	global $YVIL_PLAYER_SETTINGS;
	if( $player_settings && is_array( $player_settings ) ){
		
		$YVIL_PLAYER_SETTINGS = $player_settings;
	}
	
	// This variable is populated from theme display.php with the current video post being processed in loop
	global $yvil_video;
	
	include ( YVIL_PATH . 'themes/default/player.php' );
	$content = ob_get_contents();
	ob_end_clean();
	
	yvil_enqueue_player();
	wp_enqueue_script( 'cbc-yt-player-default', YVIL_URL . 'themes/default/assets/script.js', array(
			'ccb-video-player' 
	), '1.0' );
	wp_enqueue_style( 'ccb-yt-player-default', YVIL_URL . 'themes/default/assets/stylesheet.css', false, '1.0' );
	
	// remove custom player settings
	$YVIL_PLAYER_SETTINGS = false;
	
	return $content;
}

/**
 * TEMPLATING
 */

/**
 * Outputs default player data
 */
function yvil_output_player_data( $echo = true ){
	$player = yvil_get_player_settings();
	$attributes = yvil_data_attributes( $player, $echo );
	return $attributes;
}

/**
 * Output video parameters as data-* attributes
 * 
 * @param array $array - key=>value pairs
 * @param bool $echo
 */
function yvil_data_attributes( $attributes, $echo = false ){
	$result = array();
	foreach( $attributes as $key => $value ){
		$result[] = sprintf( 'data-%s="%s"', $key, $value );
	}
	if( $echo ){
		echo implode( ' ', $result );
	}else{
		return implode( ' ', $result );
	}
}

/**
 * Outputs the default player size
 * 
 * @param string $before
 * @param string $after
 * @param bool $echo
 */
function yvil_output_player_size( $before = ' style="', $after = '"', $echo = true ){
	$player = yvil_get_player_settings();
	$height = yvil_player_height( $player[ 'aspect_ratio' ], $player[ 'width' ] );
	$output = 'width:' . $player[ 'width' ] . 'px; height:' . $height . 'px;';
	if( $echo ){
		echo $before . $output . $after;
	}
	
	return $before . $output . $after;
}

/**
 * Output width according to player
 * 
 * @param string $before
 * @param string $after
 * @param bool $echo
 */
function yvil_output_width( $before = ' style="', $after = '"', $echo = true ){
	$player = yvil_get_player_settings();
	if( $echo ){
		echo $before . 'width: ' . $player[ 'width' ] . 'px; ' . $after;
	}
	return $before . 'width: ' . $player[ 'width' ] . 'px; ' . $after;
}

/**
 * Output video thumbnail
 * 
 * @param string $before
 * @param string $after
 * @param bool $echo
 */
function yvil_output_thumbnail( $before = '', $after = '', $echo = true ){
	$yvil_video = yvil_get_current_video();
	$output = '';
	if( $yvil_video['video_data']->get_thumbnail_url('default') ){
		$output = sprintf( '<img src="%s" alt="" />', $yvil_video['video_data']->get_thumbnail_url('default') );
	}
	if( $echo ){
		echo $before . $output . $after;
	}
	return $before . $output . $after;
}

/**
 * Output video title
 * 
 * @param string $before
 * @param string $after
 * @param bool $echo
 */
function yvil_output_title( $include_duration = true, $before = '', $after = '', $echo = true ){
	$yvil_video = yvil_get_current_video();
	$output = '';
	if( isset( $yvil_video[ 'title' ] ) ){
		$output = $yvil_video[ 'title' ];
	}
	
	if( $include_duration ){
		$output .= ' <span class="duration">[' . $yvil_video[ 'video_data' ]->get_human_duration() . ']</span>';
	}
	
	if( $echo ){
		echo $before . $output . $after;
	}
	return $before . $output . $after;
}

/**
 * Outputs video data
 * 
 * @param string $before
 * @param string $after
 * @param bool $echo
 */
function yvil_output_video_data( $before = " ", $after = "", $echo = true ){
	$yvil_video = yvil_get_current_video();
	
	$video_settings = yvil_get_video_settings( $yvil_video[ 'ID' ] );
	$video_id = $yvil_video[ 'video_data' ]->get_id();
	$data = array( 
			'video_id' => $video_id, 
			'autoplay' => $video_settings[ 'autoplay' ], 
			'volume' => $video_settings[ 'volume' ] 
	);
	
	$output = yvil_data_attributes( $data );
	if( $echo ){
		echo $before . $output . $after;
	}
	
	return $before . $output . $after;
}

function yvil_video_post_permalink( $echo = true ){
	$yvil_video = yvil_get_current_video();
	
	$pl = get_permalink( $yvil_video[ 'ID' ] );
	
	if( $echo ){
		echo $pl;
	}
	
	return $pl;
}


function yvil_get_current_video(){
	global $yvil_video;
	if( !is_a( $yvil_video['video_data'], 'YVIL_Video' ) ){
		$yvil_video['video_data'] = new YVIL_Video( $yvil_video['video_data'] );
	}
	return $yvil_video;
}

/**
 * Themes compatibility layer
 */

/**
 * Check if theme is supported by the plugin.
 * Returns false or an array containing a mapping for custom post fields to store information on
 */
function yvil_check_theme_support(){
	return false;
}

/**
 * Returns all compatible themes details
 */
function yvil_get_compatible_themes(){
	// access the theme support function to create the class instance
	yvil_check_theme_support();
	return array();
}

/**
 * More efficient strip tags
 * 
 * @link http://www.php.net/manual/en/function.strip-tags.php#110280
 * @param string $string string to strip tags from
 * @return string
 */
function yvil_strip_tags( $string ){
	
	// ----- remove HTML TAGs -----
	$string = preg_replace( '/<[^>]*>/', ' ', $string );
	
	// ----- remove control characters -----
	$string = str_replace( "\r", '', $string ); // --- replace with empty space
	$string = str_replace( "\n", ' ', $string ); // --- replace with space
	$string = str_replace( "\t", ' ', $string ); // --- replace with space
	                                             
	// ----- remove multiple spaces -----
	$string = trim( preg_replace( '/ {2,}/', ' ', $string ) );
	
	return $string;
}

/**
 * Returns ISO duration from a given number of seconds
 * 
 * @param int $seconds
 */
function yvil_iso_duration( $seconds ){
	$return = 'PT';
	$seconds = absint( $seconds );
	if( $seconds > 3600 ){
		$hours = floor( $seconds / 3600 );
		$return .= $hours . 'H';
		$seconds = $seconds - ( $hours * 3600 );
	}
	if( $seconds > 60 ){
		$minutes = floor( $seconds / 60 );
		$return .= $minutes . 'M';
		$seconds = $seconds - ( $minutes * 60 );
	}
	if( $seconds > 0 ){
		$return .= $seconds . 'S';
	}
	return $return;
}

/**
 * Returns OAuth credentials registered by user
 */
function yvil_get_yt_oauth_details(){
	$defaults = array( 
			'client_id' => '', 
			'client_secret' => '', 
			'refresh_token' => '', 
			'token' => array( 
					'value' => '', 
					'valid' => 0, 
					'time' => time() 
			) 
	);
	
	$details = get_option( '_cbc_yt_oauth_details', $defaults );
	
	if( ! is_array( $details ) ){
		$details = $defaults;
	}

	// in case spaces were pasted accidentally, remove them to avoid invalid_client error
	$details['client_id'] = trim( $details['client_id'] );
	$details['client_secret'] = trim( $details['client_secret'] );

	return $details;
}

/**
 * Updates OAuth credentials
 * 
 * @param unknown_type $client_id
 * @param unknown_type $client_secret
 * @param unknown_type $token
 */
function yvil_update_yt_oauth( $client_id = false, $client_secret = false, $token = false, $refresh_token = false ){
	$details = yvil_get_yt_oauth_details();
	if( $client_id || ! is_bool( $client_id ) ){
		if( $client_id != $details[ 'client_id' ] ){
			$details[ 'token' ] = array( 
					'value' => '', 
					'valid' => 0, 
					'time' => time() 
			);
		}
		$details[ 'client_id' ] = trim( $client_id );
	}
	if( $client_secret || ! is_bool( $client_secret ) ){
		if( $client_secret != $details[ 'client_secret' ] ){
			$details[ 'token' ] = array( 
					'value' => '', 
					'valid' => 0, 
					'time' => time() 
			);
		}
		$details[ 'client_secret' ] = trim( $client_secret );
	}
	if( $token || ! is_bool( $token ) ){
		$details[ 'token' ] = $token;
	}
	
	if( $refresh_token || ! is_bool( $refresh_token ) ){
		$details[ 'refresh_token' ] = $refresh_token;
	}
	
	update_option( '_cbc_yt_oauth_details', $details );
}

/**
 * Refresh the access token
 */
function yvil_refresh_oauth_token(){
	$token = yvil_get_yt_oauth_details();
	if( empty( $token[ 'client_id' ] ) || empty( $token[ 'client_secret' ] ) ){
		return new WP_Error( 'cbc_token_refresh_missing_oauth_login', __( 'YouTube API OAuth credentials missing. Please visit plugin Settings page and enter your credentials.', 'yt-video-importer-lite' ) );
	}
	
	$endpoint = 'https://accounts.google.com/o/oauth2/token';
	$fields = array( 
			'client_id' => $token[ 'client_id' ], 
			'client_secret' => $token[ 'client_secret' ], 
			'refresh_token' => ( isset( $token[ 'refresh_token' ] ) ? $token[ 'refresh_token' ] : null ), 
			'grant_type' => 'refresh_token' 
	);
	$response = wp_remote_post( $endpoint, array( 
			'method' => 'POST', 
			'timeout' => 45, 
			'redirection' => 5, 
			'httpversion' => '1.0', 
			'blocking' => true, 
			'headers' => array(), 
			'body' => $fields, 
			'cookies' => array() 
	) );
	
	if( is_wp_error( $response ) ){
		return $response;
	}
	
	if( 200 != wp_remote_retrieve_response_code( $response ) ){
		$details = json_decode( wp_remote_retrieve_body( $response ), true );
		if( isset( $details[ 'error' ] ) ){
			return new WP_Error( 'cbc_invalid_yt_grant', sprintf( __( 'While refreshing the access token, YouTube returned error code <strong>%s</strong>. Please refresh tokens manually by revoking current access and granting new access.', 'yt-video-importer-lite' ), $details[ 'error' ] ), $details );
		}
		return new WP_Error( 'cbc_token_refresh_error', __( 'While refreshing the access token, YouTube returned an unknown error.', 'yt-video-importer-lite' ) );
	}
	
	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	$token = array( 
			'value' => $data[ 'access_token' ], 
			'valid' => $data[ 'expires_in' ], 
			'time' => time() 
	);
	yvil_update_yt_oauth( false, false, $token );
	return $token;
}

/**
 * Get the OAuth bearer token
 * 
 * @return WP_Error|string - the bearer token or WP_Error
 */
function yvil_get_oauth_token(){
	$oauth_details = yvil_get_yt_oauth_details();
	if( ! isset( $oauth_details[ 'token' ] ) ){
		return new WP_Error( 'cbc_oauth_token_missing', __( 'Please visit plugin Settings page and setup the OAuth details to grant permission for the plugin to your YouTube account.', 'yt-video-importer-lite' ) );
	}
	if( empty( $oauth_details[ 'client_id' ] ) || empty( $oauth_details[ 'client_secret' ] ) ){
		return new WP_Error( 'cbc_oauth_no_credentials', __( 'Please enter your OAuth credentials in order to be able to query your YouTube account.', 'yt-video-importer-lite' ) );
	}
	// the token details
	$token = $oauth_details[ 'token' ];
	if( is_wp_error( $token ) ){
		return $token;
	}
	if( empty( $token[ 'value' ] ) ){
		return new WP_Error( 'cbc_oauth_token_empty', __( 'Please grant permission for the plugin to access your YouTube account.', 'yt-video-importer-lite' ) );
	}
	
	$expired = time() >= ( $token[ 'valid' ] + $token[ 'time' ] );
	if( $expired ){
		$token = yvil_refresh_oauth_token();
	}
	
	if( is_wp_error( $token ) ){
		// remove the access token if refreshing returned error
		yvil_update_yt_oauth( false, false, '' );
		return $token;
	}
	
	return $token[ 'value' ];
}

/**
 * Checks if debug is on.
 * If on, the plugin will display various information in different admin areas
 */
function yvil_debug(){
	if( defined( 'YVIL_DEBUG' ) ){
		return ( bool ) YVIL_DEBUG;
	}
	return false;
}

/**
 * ***************************************
 * API query functions
 * ***************************************
 */

/**
 * Loads YouTube API query class
 */
function __yvil_load_youtube_api_class(){
	if( ! class_exists( 'YVIL_YouTube_API_Query' ) ){
		require_once YVIL_PATH . 'includes/libs/youtube-api-query.class.php';
	}
}

/**
 * Perform a YouTube search.
 * Arguments:
 * include_categories bool - when true, video categories will be retrieved, if false, they won't
 * query string - the search query
 * page_token - YT API 3 page token for pagination
 * order string - any of: date, rating, relevance, title, viewCount
 * duration string - any of: any, short, medium, long
 * 
 * @return array of videos or WP error
 */
function yvil_yt_api_search_videos( $args = array() ){
	$defaults = array( 
			// if false, YouTube categories won't be retrieved
			'include_categories' => true, 
			// the search query
			'query' => '', 
			// as of API 3, results pagination is done by tokens
			'page_token' => '', 
			// can be: date, rating, relevance, title, viewCount
			'order' => 'relevance', 
			// can be: any, short, medium, long
			'duration' => 'any', 
			// not used but into the script
			'embed' => 'any' 
	);
	
	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );
	$settings = yvil_get_settings();
	$per_page = $settings[ 'manual_import_per_page' ];
	
	__yvil_load_youtube_api_class();
	$q = new YVIL_YouTube_API_Query( $per_page, $include_categories );
	$videos = $q->search( $query, $page_token, array( 
			'order' => $order, 
			'duration' => $duration, 
			'embed' => $embed 
	) );
	$page_info = $q->get_list_info();
	
	return array( 
			'videos' => $videos, 
			'page_info' => $page_info 
	);
}

/**
 * Get videos for a given YouTube playlist.
 * Arguments:
 * include_categories bool - when true, video categories will be retrieved, if false, they won't
 * query string - the search query
 * page_token - YT API 3 page token for pagination
 * type string - auto or manual
 * 
 * @param array $args
 */
function yvil_yt_api_get_playlist( $args = array() ){
	$args[ 'playlist_type' ] = 'playlist';
	return yvil_yt_api_get_list( $args );
}

/**
 * Get videos for a given YouTube user.
 * Arguments:
 * include_categories bool - when true, video categories will be retrieved, if false, they won't
 * query string - the search query
 * page_token - YT API 3 page token for pagination
 * type string - auto or manual
 * 
 * @param array $args
 */
function yvil_yt_api_get_user( $args = array() ){
	$args[ 'playlist_type' ] = 'user';
	return yvil_yt_api_get_list( $args );
}

/**
 * Get videos for a given YouTube channel.
 * Arguments:
 * include_categories bool - when true, video categories will be retrieved, if false, they won't
 * query string - the search query
 * page_token - YT API 3 page token for pagination
 * type string - auto or manual
 * 
 * @param array $args
 */
function yvil_yt_api_get_channel( $args = array() ){
	$args[ 'playlist_type' ] = 'channel';
	return yvil_yt_api_get_list( $args );
}

/**
 * Get details about a single video ID
 * 
 * @param string $video_id - YouTube video ID
 */
function yvil_yt_api_get_video( $video_id ){
	__yvil_load_youtube_api_class();
	$q = new YVIL_YouTube_API_Query( 1, true );
	$video = $q->get_video( $video_id );
	return $video;
}

/**
 * Get details about multiple video IDs
 * 
 * @param string $video_ids - YouTube video IDs comma separated or array of video ids
 */
function yvil_yt_api_get_videos( $video_ids ){
	__yvil_load_youtube_api_class();
	$q = new YVIL_YouTube_API_Query( 50, true );
	$videos = $q->get_videos( $video_ids );
	return $videos;
}

/**
 * Returns a playlist feed.
 * include_categories bool - when true, video categories will be retrieved, if false, they won't
 * query string - the search query
 * page_token - YT API 3 page token for pagination
 * type string - auto or manual
 * playlist_type - one of the following: user, playlist or channel
 * 
 * @param array $args
 */
function yvil_yt_api_get_list( $args = array() ){
	$defaults = array( 
			'playlist_type' => 'playlist', 
			// can be auto or manual - will set pagination according to user settings
			'type' => 'manual', 
			// if false, YouTube categories won't be retrieved
			'include_categories' => true, 
			// the search query
			'query' => '', 
			// as of API 3, results pagination is done by tokens
			'page_token' => '' 
	);
	
	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );
	
	$types = array( 
			'user', 
			'playlist', 
			'channel' 
	);
	if( ! in_array( $playlist_type, $types ) ){
		trigger_error( __( 'Invalid playlist type. Use as playlist type one of the following: user, playlist or channel.', 'yt-video-importer-lite' ), E_USER_NOTICE );
		return;
	}
	
	$settings = yvil_get_settings();
	$per_page = $settings[ 'manual_import_per_page' ];

	__yvil_load_youtube_api_class();
	$q = new YVIL_YouTube_API_Query( $per_page, $include_categories );
	switch( $playlist_type ){
		case 'playlist':
			$videos = $q->get_playlist( $query, $page_token );
		break;
		case 'user':
			$videos = $q->get_user_uploads( $query, $page_token );
		break;
		case 'channel':
			$videos = $q->get_channel_uploads( $query, $page_token );
		break;
	}
	
	$page_info = $q->get_list_info();
	
	return array( 
			'videos' => $videos, 
			'page_info' => $page_info 
	);
}

/**
 * Checks whether variable is a WP error in first place
 * and second will verifyis the error has YouTube flag on it.
 */
function yvil_is_youtube_error( $thing ){
	if( ! is_wp_error( $thing ) ){
		return false;
	}
	
	$data = $thing->get_error_data();
	if( $data && isset( $data[ 'youtube_error' ] ) ){
		return true;
	}
	
	return false;
}

/**
 * Callback function that removes some filters and actions before doing bulk imports
 * either manually of automatically.
 * Useful in case EWW Image optimizer is intalled; it will take a lot longer to import videos
 * if it processes the images.
 */
function yvil_remove_actions_on_bulk_import(){
	// remove EWW Optimizer actions to improve autoimport time
	remove_filter( 'wp_handle_upload', 'ewww_image_optimizer_handle_upload' );
	remove_filter( 'add_attachment', 'ewww_image_optimizer_add_attachment' );
	remove_filter( 'wp_image_editors', 'ewww_image_optimizer_load_editor', 60 );
	remove_filter( 'wp_generate_attachment_metadata', 'ewww_image_optimizer_resize_from_meta_data', 15 );
}
add_action( 'cbc_before_auto_import', 'cbc_remove_actions_on_bulk_import' );
add_action( 'cbc_before_thumbnails_bulk_import', 'cbc_remove_actions_on_bulk_import' );
add_action( 'cbc_before_manual_bulk_import', 'cbc_remove_actions_on_bulk_import' );

/**
 * A simple debug function.
 * Doesn't do anything special, only triggers an
 * action that passes the information along the way.
 * For actual debug messages, extra functions that process and hook to this action
 * are needed.
 */
function _yvil_debug_message( $message, $separator = "\n", $data = false ){
	/**
	 * Fires a debug message action
	 */
	do_action( 'cbc_debug_message', $message, $separator, $data );
}

/**
 * Utility functions
 */

/**
 * Get post type object instance
 * 
 * @return YVIL_YouTube_Videos
 */
function yvil_get_class_instance(){
	global $YVIL_POST_TYPE;
	return $YVIL_POST_TYPE;
}

/**
 * Return registered video post type
 * 
 * @return string
 */
function yvil_get_post_type(){
	$obj = yvil_get_class_instance();
	return $obj->get_post_type();
}

/**
 * Return registered video category taxonomy
 * 
 * @return string
 */
function yvil_get_category_taxonomy(){
	$obj = yvil_get_class_instance();
	return $obj->get_post_tax();
}

/**
 * Return registered video tag taxonomy
 * 
 * @return string
 */
function yvil_get_tag_taxonomy(){
	$obj = yvil_get_class_instance();
	return $obj->get_post_tag_tax();
}
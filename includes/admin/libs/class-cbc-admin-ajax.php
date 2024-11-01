<?php
if( ! class_exists( 'YVIL_Video_Post_Type' ) ){
	require_once YVIL_PATH . 'includes/libs/custom-post-type.class.php';
}

/**
 * AJAX Actions management class
 * Extended by YVIL_Admin class
 */
class YVIL_AJAX_Actions{

	private $cpt;

	/**
	 * Constructor.
	 * Sets all registered ajax actions.
	 */
	public function __construct( YVIL_Video_Post_Type $post_type ){
		$this->cpt = $post_type;
		// get the actions
		$actions = $this->__actions();
		// add wp actions
		foreach( $actions as $action ){
			add_action( 'wp_ajax_' . $action[ 'action' ], $action[ 'callback' ] );
		}
	}

	/**
	 * AJAX Callback
	 * Queries a given playlist ID and returns the number of videos found into that playlist.
	 */
	public function callback_query_playlist(){
		if( empty( $_POST[ 'type' ] ) || empty( $_POST[ 'id' ] ) ){
			_e( 'Please enter a playlist ID.', 'yt-video-importer-lite' );
			die();
		}
		
		$args = array( 
				'playlist_type' => $_POST[ 'type' ], 
				'include_categories' => false, 
				'query' => $_POST[ 'id' ] 
		);
		$details = yvil_yt_api_get_list( $args );
		
		if( is_wp_error( $details[ 'videos' ] ) ){
			echo '<span style="color:red;">' . $details[ 'videos' ]->get_error_message() . '</span>';
		}else{
			printf( __( 'Playlist contains %d videos.', 'yt-video-importer-lite' ), $details[ 'page_info' ][ 'total_results' ] );
		}
		die();
	}

	/**
	 * Manual bulk import AJAX callback
	 */
	public function video_bulk_import(){
		// import videos
		$response = array( 
				'success' => false, 
				'error' => false 
		);
		
		$ajax_data = $this->__get_action_data( 'manual_video_bulk_import' );
		
		if( isset( $_POST[ $ajax_data[ 'nonce' ][ 'name' ] ] ) ){
			if( check_ajax_referer( $ajax_data[ 'nonce' ][ 'action' ], $ajax_data[ 'nonce' ][ 'name' ], false ) ){
				if( 'import' == $_POST[ 'action_top' ] || 'import' == $_POST[ 'action2' ] ){
					
					// increase time limit
					@set_time_limit( 300 );
					
					/**
					 * Action that runs before importing videos.
					 * Useful to remove actions and filters of third party plugins.
					 */
					do_action( 'cbc_before_manual_bulk_import' );
					
					$result = $this->import_videos();
					
					if( is_wp_error( $result ) ){
						$response[ 'error' ] = $result->get_error_message();
					}else if( $result ){
						$response[ 'success' ] = sprintf( __( '<strong>%d videos:</strong> %d imported; %d not found; %d skipped (already imported)', 'yt-video-importer-lite' ), $result[ 'total' ], $result[ 'imported' ], $result[ 'not_found' ], $result[ 'skipped' ] );
					}else{
						$response[ 'error' ] = __( 'No videos selected for importing. Please select some videos by checking the checkboxes next to video title.', 'yt-video-importer-lite' );
					}
				}else{
					$response[ 'error' ] = __( 'Please select an action.', 'yt-video-importer-lite' );
				}
			}else{
				$response[ 'error' ] = __( "Cheatin' uh?", 'yt-video-importer-lite' );
			}
		}else{
			$response[ 'error' ] = __( "Cheatin' uh?", 'yt-video-importer-lite' );
		}
		
		echo json_encode( $response );
		die();
	}

	/**
	 * Helper for $this->video_bulk_import().
	 * Will import all videos passed by user with the AJAX call.
	 * Import videos to WordPress
	 */
	private function import_videos(){
		if( ! isset( $_POST[ 'cbc_import' ] ) || ! $_POST[ 'cbc_import' ] ){
			return false;
		}
		
		// get options
		$options = yvil_get_settings();
		// set category
		$category = false;
		if( isset( $_REQUEST[ 'cat_top' ] ) && 'import' == $_REQUEST[ 'action_top' ] ){
			$category = $_REQUEST[ 'cat_top' ];
		}elseif( isset( $_REQUEST[ 'cat2' ] ) && 'import' == $_REQUEST[ 'action2' ] ){
			$category = $_REQUEST[ 'cat2' ];
		}
		// reset category if not set correctly
		if( - 1 == $category || 0 == $category ){
			$category = false;
		}
		
		// prepare array of video IDs
		$video_ids = array_reverse( ( array ) $_POST[ 'cbc_import' ] );
		// stores after import results
		$result = array( 
				'imported' => 0, 
				'skipped' => 0, 
				'not_found' => 0, 
				'total' => count( $video_ids ) 
		);
		
		// set post status
		$statuses = array( 
				'publish', 
				'draft', 
				'pending' 
		);
		$status = in_array( $options[ 'import_status' ], $statuses ) ? $options[ 'import_status' ] : 'draft';
		
		// set user
		$user = false;
		if( isset( $_REQUEST[ 'user_top' ] ) && $_REQUEST[ 'user_top' ] ){
			$user = ( int ) $_REQUEST[ 'user_top' ];
		}else if( isset( $_REQUEST[ 'user2' ] ) && $_REQUEST[ 'user2' ] ){
			$user = ( int ) $_REQUEST[ 'user2' ];
		}
		if( $user ){
			$user_data = get_userdata( $user );
			$user = ! $user_data ? false : $user_data->ID;
		}
		
		$videos = yvil_yt_api_get_videos( $video_ids );
		if( is_wp_error( $videos ) ){
			return $videos;
		}
		
		foreach( $videos as $video ){
			
			// search if video already exists
			$posts = get_posts( array( 
					'post_type' => $this->cpt->get_post_type(),
					'meta_key' => '__cbc_video_id', 
					'meta_value' => $video->get_id(), 
					'post_status' => array( 
							'publish', 
							'pending', 
							'draft', 
							'future', 
							'private' 
					) 
			) );
			
			// video already exists, don't do anything
			if( $posts ){
				$result[ 'skipped' ] += 1;
				continue;
			}
			
			$video_id = $video->get_id();
			if( isset( $_POST[ 'cbc_title' ][ $video_id ] ) ){
				$video->set_title( $_POST[ 'cbc_title' ][ $video_id ] );
			}
			if( isset( $_POST[ 'cbc_text' ][ $video_id ] ) ){
				$video->set_description( $_POST[ 'cbc_text' ][ $video_id ] );
			}
			
			$r = $this->cpt->import_video( array(
					'video' => $video,  // video details retrieved from YouTube
					'category' => $category,  // category name (if any); if false, it will create categories from YouTube
					'post_type' => $this->cpt->get_post_type(),  // what post type to import as
					'taxonomy' => $this->cpt->get_post_tax(),  // what taxonomy should be used
					'tag_taxonomy' => $this->cpt->get_post_tag_tax(),
					'user' => $user,  // save as a given user if any
					'post_format' => 'video',  // post format will default to video
					'status' => $status,  // post status
					'theme_import' => false
			) ); // to check in callbacks if importing as theme post
			
			if( $r ){
				$result[ 'imported' ] += 1;
			}
		}
		
		return $result;
	}

	/**
	 * AJAX Callback
	 * Import post thumbnail
	 */
	public function import_post_thumbnail(){
		if( ! isset( $_POST[ 'id' ] ) ){
			die();
		}
		
		$post_id = absint( $_POST[ 'id' ] );
		$thumbnail = yvil_set_featured_image( $post_id );
		
		if( ! $thumbnail ){
			die();
		}
		
		$response = _wp_post_thumbnail_html( $thumbnail[ 'attachment_id' ], $thumbnail[ 'post_id' ] );
		wp_send_json_success( $response );
		
		die();
	}

	/**
	 * Callback function to change manual bulk import view from grid to list and viceversa
	 */
	public function change_import_view(){
		$action = $this->__get_action_data( 'import_view' );
		check_ajax_referer( $action[ 'nonce' ][ 'action' ], $action[ 'nonce' ][ 'name' ] );
		
		$view = 'grid';
		if( isset( $_POST[ 'view' ] ) ){
			$view = 'list' == $_POST[ 'view' ] ? 'list' : 'grid';
		}
		
		$uid = get_current_user_id();
		if( $uid ){
			update_user_option( $uid, 'cbc_video_import_view', $view );
		}
		
		die();
	}
	
	/**
	 * Stores all ajax actions references.
	 * This is where all ajax actions are added.
	 */
	private function __actions(){
		$actions = array( 
				/**
				 * Query for playlist details.
				 * Used on automatic playlists to list statistics about playlists
				 */
				'playlist_query' => array( 
						'action' => 'cbc_check_playlist', 
						'callback' => array( 
								$this, 
								'callback_query_playlist' 
						), 
						'nonce' => array( 
								'name' => 'cbc-ajax-nonce', 
								'action' => 'cbc-playlist-query' 
						) 
				), 
				/**
				 * Manual bulk import video AJAX callback
				 */
				'manual_video_bulk_import' => array( 
						'action' => 'cbc_import_videos', 
						'callback' => array( 
								$this, 
								'video_bulk_import' 
						), 
						'nonce' => array( 
								'name' => 'cbc_import_nonce', 
								'action' => 'cbc-import-videos-to-wp' 
						) 
				), 
				/**
				 * Post thumbnail import
				 */
				'import_post_thumbnail' => array( 
						'action' => 'cbc_import_video_thumbnail', 
						'callback' => array( 
								$this, 
								'import_post_thumbnail' 
						), 
						'nonce' => array( 
								'name' => 'cbc_nonce', 
								'action' => 'cbc-thumbnail-post-import' 
						) 
				), 
				
				'import_view' => array( 
						'action' => 'cbc_import_list_view', 
						'callback' => array( 
								$this, 
								'change_import_view' 
						), 
						'nonce' => array( 
								'name' => 'cbc_nonce', 
								'action' => 'cbc-change-manual-import-list-view' 
						) 
				),
		);
		
		return $actions;
	}

	/**
	 * Gets all details of a given action from registered actions
	 * 
	 * @param string $key
	 */
	public function __get_action_data( $key ){
		$actions = $this->__actions();
		if( array_key_exists( $key, $actions ) ){
			return $actions[ $key ];
		}else{
			trigger_error( sprintf( __( 'Action %s not found.' ), $key ), E_USER_WARNING );
		}
	}
}
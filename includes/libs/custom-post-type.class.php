<?php

/**
 * Video post type class.
 * Registers post type and sets up some filters and
 * actions needed in front-end to display video post type correctly according
 * to user settings.
 */
abstract class YVIL_Video_Post_Type{

	/**
	 * Video custom post type name
	 * 
	 * @var string
	 */
	protected $post_type = 'video';

	/**
	 * Video custom post type taxonomy
	 * 
	 * @var string
	 */
	protected $taxonomy = 'videos';

	/**
	 * Video custom post type tag taxonomy
	 * 
	 * @var string
	 */
	protected $tag_taxonomy = 'video_tag';
	/**
	 * @var YVIL_Video_Data_Cache
	 */
	protected $post_data_cache;

	/**
	 * Constructor, registers post types and sets different actions and filters
	 * needed in front-end.
	 */
	public function __construct(){
		$this->post_data_cache = new YVIL_Video_Data_Cache( $this );

		// custom post type registration and messages
		add_action( 'init', array( 
				$this, 
				'register_post' 
		), 10 );
		// custom post type messages
		add_filter( 'post_updated_messages', array( 
				$this, 
				'updated_messages' 
		) );
		
		// plugin filters
		add_filter( 'cbc_video_post_content', array( 
				$this, 
				'format_description' 
		), 999, 3 );
	}

	/**
	 * Register video post type and taxonomies
	 */
	public function register_post(){
		$labels = array( 
				'name' => _x( 'Videos', 'Videos', 'yt-video-importer-lite' ),
				'singular_name' => _x( 'Video', 'Video', 'yt-video-importer-lite' ),
				'add_new' => _x( 'Add new', 'Add new video', 'yt-video-importer-lite' ),
				'add_new_item' => __( 'Add new video', 'yt-video-importer-lite' ),
				'edit_item' => __( 'Edit video', 'yt-video-importer-lite' ),
				'new_item' => __( 'New video', 'yt-video-importer-lite' ),
				'all_items' => __( 'All videos', 'yt-video-importer-lite' ),
				'view_item' => __( 'View', 'yt-video-importer-lite' ),
				'search_items' => __( 'Search', 'yt-video-importer-lite' ),
				'not_found' => __( 'No videos found', 'yt-video-importer-lite' ),
				'not_found_in_trash' => __( 'No videos in trash', 'yt-video-importer-lite' ),
				'parent_item_colon' => '', 
				'menu_name' => __( 'Videos', 'yt-video-importer-lite' )
		);
		
		$options = yvil_get_settings();
		$is_public = $options[ 'public' ];
		
		$args = array( 
				'labels' => $labels, 
				'public' => $is_public, 
				'exclude_from_search' => ! $is_public, 
				'publicly_queryable' => $is_public, 
				'show_in_nav_menus' => $is_public, 
				
				'show_ui' => true, 
				'show_in_menu' => true, 
				'menu_position' => 5, 
				'menu_icon' => YVIL_URL . 'assets/back-end/images/video.png',
				
				'query_var' => true, 
				'capability_type' => 'post', 
				'has_archive' => true, 
				'hierarchical' => false, 
				
				// REST support
				'show_in_rest' => true, 
				
				'rewrite' => array( 
						'slug' => $options[ 'post_slug' ], 
						/**
						 * Allow with_front attribute to be modified by themes/plugins
						 * 
						 * @param $with_front - default true
						 */
						'with_front' => ( bool ) apply_filters( 'cbc_cpt_with_front', true ) 
				), 
				'supports' => array( 
						'title', 
						'editor', 
						'author', 
						'thumbnail', 
						'excerpt', 
						'trackbacks', 
						'custom-fields', 
						'comments', 
						'revisions', 
						'post-formats' 
				) 
		);
		
		register_post_type( $this->post_type, $args );
		
		// Add new taxonomy, make it hierarchical (like categories)
		$cat_labels = array( 
				'name' => _x( 'Video categories', 'video', 'yt-video-importer-lite' ),
				'singular_name' => _x( 'Video category', 'video', 'yt-video-importer-lite' ),
				'search_items' => __( 'Search video category', 'yt-video-importer-lite' ),
				'all_items' => __( 'All video categories', 'yt-video-importer-lite' ),
				'parent_item' => __( 'Video category parent', 'yt-video-importer-lite' ),
				'parent_item_colon' => __( 'Video category parent:', 'yt-video-importer-lite' ),
				'edit_item' => __( 'Edit video category', 'yt-video-importer-lite' ),
				'update_item' => __( 'Update video category', 'yt-video-importer-lite' ),
				'add_new_item' => __( 'Add new video category', 'yt-video-importer-lite' ),
				'new_item_name' => __( 'Video category name', 'yt-video-importer-lite' ),
				'menu_name' => __( 'Video categories', 'yt-video-importer-lite' )
		);
		
		register_taxonomy( $this->taxonomy, array( 
				$this->post_type 
		), array( 
				'public' => $is_public, 
				'show_ui' => true, 
				'show_in_nav_menus' => $is_public, 
				'show_admin_column' => true, 
				'hierarchical' => true, 
				// REST support
				'show_in_rest' => true, 
				
				'rewrite' => array( 
						'slug' => $options[ 'taxonomy_slug' ], 
						/**
						 * Allow with_front attribute to be modified by themes/plugins
						 * 
						 * @param $with_front - default true
						 */
						'with_front' => ( bool ) apply_filters( 'cbc_taxonomy_with_front', true ) 
				), 
				'capabilities' => array( 
						'edit_posts' 
				), 
				'labels' => $cat_labels, 
				'query_var' => true 
		) );
		
		$tag_labels = array( 
				'name' => _x( 'Video tags', 'video', 'yt-video-importer-lite' ),
				'singular_name' => _x( 'Video tag', 'video', 'yt-video-importer-lite' ),
				'search_items' => __( 'Search video tag', 'yt-video-importer-lite' ),
				'all_items' => __( 'All video tags', 'yt-video-importer-lite' ),
				'parent_item' => __( 'Video tag parent', 'yt-video-importer-lite' ),
				'parent_item_colon' => __( 'Video tag parent:', 'yt-video-importer-lite' ),
				'edit_item' => __( 'Edit video tag', 'yt-video-importer-lite' ),
				'update_item' => __( 'Update video tag', 'yt-video-importer-lite' ),
				'add_new_item' => __( 'Add new video tag', 'yt-video-importer-lite' ),
				'new_item_name' => __( 'Video tag name', 'yt-video-importer-lite' ),
				'menu_name' => __( 'Video tags', 'yt-video-importer-lite' )
		);
		
		register_taxonomy( $this->tag_taxonomy, array( 
				$this->post_type 
		), array( 
				'public' => $is_public, 
				'show_ui' => true, 
				'show_in_nav_menus' => $is_public, 
				'show_admin_column' => true, 
				'hierarchical' => false, 
				// REST support
				'show_in_rest' => true, 
				
				'rewrite' => array( 
						'slug' => $options[ 'tag_taxonomy_slug' ], 
						/**
						 * Allow with_front attribute to be modified by themes/plugins
						 * 
						 * @param $with_front - default true
						 */
						'with_front' => ( bool ) apply_filters( 'cbc_tag_taxonomy_with_front', true ) 
				), 
				'capabilities' => array( 
						'edit_posts' 
				), 
				'labels' => $tag_labels, 
				'query_var' => true 
		) );
	}

	/**
	 * Custom post type messages on edit, update, create, etc.
	 * 
	 * @param array $messages
	 */
	public function updated_messages( $messages ){
		global $post, $post_ID;
		
		$messages[ 'video' ] = array( 
				0 => '',  // Unused. Messages start at index 1.
				1 => sprintf( __( 'Video updated <a href="%s">See video</a>', 'yt-video-importer-lite' ), esc_url( get_permalink( $post_ID ) ) ),
				2 => __( 'Custom field updated.', 'yt-video-importer-lite' ),
				3 => __( 'Custom field deleted.', 'yt-video-importer-lite' ),
				4 => __( 'Video updated.', 'yt-video-importer-lite' ),
	   		/* translators: %s: date and time of the revision */
	    	5 => isset( $_GET[ 'revision' ] ) ? sprintf( __( 'Video restored to version %s', 'yt-video-importer-lite' ), wp_post_revision_title( ( int ) $_GET[ 'revision' ], false ) ) : false,
				6 => sprintf( __( 'Video published. <a href="%s">See video</a>', 'yt-video-importer-lite' ), esc_url( get_permalink( $post_ID ) ) ),
				7 => __( 'Video saved.', 'yt-video-importer-lite' ),
				8 => sprintf( __( 'Video saved. <a target="_blank" href="%s">See video</a>', 'yt-video-importer-lite' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
				9 => sprintf( __( 'Video will be published at: <strong>%1$s</strong>. <a target="_blank" href="%2$s">See video</a>', 'yt-video-importer-lite' ),
						// translators: Publish box date format, see http://php.net/date
						date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ), 
				10 => sprintf( __( 'Video draft saved. <a target="_blank" href="%s">See video</a>', 'yt-video-importer-lite' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
				
				101 => __( 'Please select a source', 'yt-video-importer-lite' )
		);
		
		return $messages;
	}

	/**
	 * Import a single video based on the passed data
	 */
	public function import_video( $args = array() ){
		$defaults = array( 
				'video' => array(),  // video details retrieved from YouTube
				'category' => false,  // category name (if any) - will be created if category_id is false
				'post_type' => false,  // what post type to import as
				'taxonomy' => false,  // what taxonomy should be used
				'tag_taxonomy' => false,  // the tag taxonomy to import YouTube tags in
				'user' => false,  // save as a given user if any
				'post_format' => 'video',  // post format will default to video
				'status' => 'draft',  // post status
				'theme_import' => false 
		);
		
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		// if no video details or post type, bail out
		if( ! $video || ! $post_type ){
			_yvil_debug_message( 'Video post not created because it is missing video details or post type.' );
			return false;
		}
		
		/**
		 * Filter that allows video imports.
		 * Can be used to prevent importing of
		 * videos.
		 * 
		 * @param $video - video details array
		 * @param $post_type - post type that should be created from the video details
		 * @param $theme_import - if video should be imported as theme compatible post, holds theme details array
		 */
		$allow_import = apply_filters( 'cbc_allow_video_import', true, $video->to_array(), $post_type, false );
		if( ! $allow_import ){
			_yvil_debug_message( 'Video not imported because filter cbc_allow_video_import is in effect and prevents imports.' );
			return false;
		}
		
		// plugin settings
		$options = yvil_get_settings();
		
		/**
		 * Import category if not set to an existing one
		 */
		if( ! $category && $options[ 'import_categories' ] && $video->get_category() ){
			$cat = term_exists( $video->get_category(), $taxonomy );
			// if not existing, create it
			if( 0 == $cat || null == $cat ){
				$cat = wp_insert_term( $video->get_category(), $taxonomy );
			}
			// set category to newly inserted term
			if( isset( $cat[ 'term_id' ] ) ){
				$category = $cat[ 'term_id' ];
			}
		}
		
		/**
		 * Filter on video description.
		 * Useful to modify video description globally, for all subsequent
		 * filters and actions from this point forward.
		 * 
		 * @param string - video description
		 * @param bool - import description value as set by the user in plugin settings
		 */
		$video->set_description( apply_filters( 'cbc_video_description', $video->get_description(), $options[ 'import_description' ] ) );
		
		// post content
		$post_content = '';
		if( 'content' == $options[ 'import_description' ] || 'content_excerpt' == $options[ 'import_description' ] ){
			$post_content = $video->get_description();
		}
		// post excerpt
		$post_excerpt = '';
		if( 'excerpt' == $options[ 'import_description' ] || 'content_excerpt' == $options[ 'import_description' ] ){
			$post_excerpt = $video->get_description();
		}
		
		/**
		 * Filter on video title.
		 * Useful to modify video title globally, for all subsequent filters and
		 * actions from this point forward.
		 * 
		 * @param string - video title
		 * @param bool - import title value as set by user in plugin settings
		 */
		$video->set_title( apply_filters( 'cbc_video_title', $video->get_title(), $options[ 'import_title' ] ) );
		$post_title = $options[ 'import_title' ] ? $video->get_title() : '';
		
		/**
		 * Action triggered before the video post is inserted into the database.
		 * 
		 * @param array $video - the video details
		 * @param array $theme_import - if importing for compatible theme, the array will contain the theme details
		 * @param string $post_type - the post type that the newly created post will have
		 */
		do_action( 'cbc_before_post_insert', $video->to_array(), false, $post_type );
		
		// set post data
		$post_data = array( 
				/**
				 * Filter on post title
				 * 
				 * @param string - the post title
				 * @param array - the video details
				 * @param bool/array - false if not imported as theme, array if imported as theme and theme is active
				 */
				'post_title' => apply_filters( 'cbc_video_post_title', $post_title, $video->to_array(), false ),
				/**
				 * Filter on post content
				 * 
				 * @param string - the post content
				 * @param array - the video details
				 * @param bool/array - false if not imported as theme, array if imported as theme and theme is active
				 */
				'post_content' => apply_filters( 'cbc_video_post_content', $post_content, $video->to_array(), false ),
				/**
				 * Filter on post excerpt
				 * 
				 * @param string - the post excerpt
				 * @param array - the video details
				 * @param bool/array - false if not imported as theme, array if imported as theme and theme is active
				 */
				'post_excerpt' => apply_filters( 'cbc_video_post_excerpt', $post_excerpt, $video->to_array(), false ),
				'post_type' => $post_type, 
				'post_status' => apply_filters( 'cbc_video_post_status', $status, $video->to_array(), false ),
				/**
				 * Comment and ping status
				 */
				'comment_status' => apply_filters( 'cbc_video_post_comment_status', get_default_comment_status( $post_type, 'comment' ), $video->to_array(), false ),
				'ping_status' => apply_filters( 'cbc_video_post_ping_status', get_default_comment_status( $post_type, 'pingback' ), $video->to_array(), false )
		);
		
		// set user
		if( $user ){
			$post_data[ 'post_author' ] = $user;
		}

		$post_id = wp_insert_post( $post_data, true );
		
		if( is_wp_error( $post_id ) ){
			_yvil_debug_message( sprintf( 'Post insert returned error %s. MySQL error is: %s', $post_id->get_error_message(), print_r( $post_id->get_error_data( 'db_insert_error' ), true ) ) );
		}
		
		// check if post was created
		if( ! is_wp_error( $post_id ) ){
			// set post format
			if( $post_format ){
				set_post_format( $post_id, $post_format );
			}
			
			// set post category
			if( $category ){
				$ret = wp_set_post_terms( $post_id, array( 
						$category 
				), $taxonomy );
				// log error if any
				if( is_wp_error( $ret ) ){
					_yvil_debug_message( sprintf( "Setup of category having taxonomy '%s' on post ID %d returned error: %s.", $taxonomy, $post_id, $ret->get_error_message() ) );
				}
			}
			
			if( isset( $options[ 'import_tags' ] ) && $options[ 'import_tags' ] && $tag_taxonomy ){
				if( is_array( $video->get_tags() ) ){
					$tags = array();
					$count = absint( $options[ 'max_tags' ] );
					$tags = array_slice( $video->get_tags(), 0, 1 );
					if( $tags ){
						$ret = wp_set_post_terms( $post_id, $tags, $tag_taxonomy, true );
						// log error if any
						if( is_wp_error( $ret ) ){
							_yvil_debug_message( sprintf( "Setup of tag(s) taxonomy '%s' on post ID %d returned error: %s.", $tag_taxonomy, $post_id, $ret->get_error_message() ) );
						}
					}
				}
			}
			
			/**
			 * Action triggered after the post is inserted into the database
			 * 
			 * @param int $post_id - the ID of the newly created post
			 * @param array $video - the video details retrieved from YouTube
			 * @param array $theme_import - if video is imported for compatible WP theme this array will contain the theme details
			 * @param string $post_type - the post type of the newly created post
			 */
			do_action( 'cbc_post_insert', $post_id, $video->to_array(), false, $post_type );
			
			// set video ID meta to identify the video as imported
			update_post_meta( $post_id, '__cbc_video_id', $video->get_id() );
			// set video URL; most likely it will be needed by other plugins
			update_post_meta( $post_id, '__cbc_video_url', 'https://www.youtube.com/watch?v=' . $video->get_id() );
			// store the video data for later use
			update_post_meta( $post_id, '__cbc_video_data', $video->to_array() );
			
			return $post_id;
		}else{ // end checking if not wp error on post insert
		       // post is wp error, send error for logging
			_yvil_debug_message( sprintf( 'While trying to create the video post, following error was returned: %s.', $post_id->get_error_message() ) );
		}
		return false;
	}

	/**
	 * Removes extra text if set in Settings page to check descriptions and if found in
	 * imported description
	 * Callback function for filter 'cbc_video_post_content' set in class constructor
	 * 
	 * @param $content
	 * @param $video
	 * @param $theme_import
	 */
	public function format_description( $content, $video, $theme_import ){
		$settings = yvil_get_settings();
		
		// trim description based on given string delimiter
		$delimiter = false;
		if( isset( $settings[ 'remove_after_text' ] ) ){
			$delimiter = trim( esc_attr( yvil_strip_tags( $settings[ 'remove_after_text' ] ) ) );
		}
		if( $delimiter && ! empty( $delimiter ) ){
			$position = strpos( $content, $delimiter );
			if( false != $position ){
				$content = substr( $content, 0, $position );
			}
		}
		
		// make url's clickable if set
		if( isset( $settings[ 'make_clickable' ] ) && $settings[ 'make_clickable' ] ){
			$content = make_clickable( $content );
		}
		
		return $content;
	}

	/**
	 * Helper function.
	 * Checks is current post is a video post.
	 * Also verifies regular post type and looks for flag variable '__yvil_is_video'
	 */
	public function is_video( $post = false ){
		if( ! $post ){
			global $post;
		}
		if( is_numeric( $post ) ){
			get_post( $post );
		}
		if( ! $post ){
			return false;
		}
		
		if( $this->post_type == $post->post_type ){
			$is_video = $this->post_data_cache->get_post_data( $post->ID );
			if( $is_video ){
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns video data for a given post object/ID
	 * @param int/WP_Post $post
	 * @return YVIL_Video
	 */
	public function get_post_video_data( $post ){
		if( $post instanceof WP_Post ){
			$post_id = $post->ID;
		}else{
			$post_id = absint( $post );
		}

		$video_data = $this->post_data_cache->get_post_data( $post_id );
		return $video_data;
	}
	
	/**
	 * Return post type
	 */
	public function get_post_type(){
		return $this->post_type;
	}

	/**
	 * Return taxonomy
	 */
	public function get_post_tax(){
		return $this->taxonomy;
	}

	/**
	 * Return tag taxonomy
	 */
	public function get_post_tag_tax(){
		return $this->tag_taxonomy;
	}
}
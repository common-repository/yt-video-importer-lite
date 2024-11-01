<?php
/*
 * Plugin Name: YouTube Video Importer Lite
 * Plugin URI: https://wpythub.com
 * Description: Import YouTube videos directly into WordPress and display them as posts or embedded in existing posts and/or pages as single videos or playlists.
 * Author: Constantin Boiangiu
 * Version: 1.0.3
 */
define( 'YVIL_PATH', plugin_dir_path( __FILE__ ) );
define( 'YVIL_URL', plugin_dir_url( __FILE__ ) );
define( 'YVIL_VERSION', '1.0.3' );
define( 'YVIL_DEBUG', false ); // if true, will display various information in various admin areas

include_once YVIL_PATH . 'includes/functions.php';
include_once YVIL_PATH . 'includes/libs/youtube-api-query.class.php';
include_once YVIL_PATH . 'includes/libs/data-cache.class.php';
include_once YVIL_PATH . 'includes/libs/custom-post-type.class.php';
include_once YVIL_PATH . 'includes/libs/rest-api.class.php';
include_once YVIL_PATH . 'includes/libs/load-times.class.php';

/**
 * Main class, implements all plugin functionality
 */
class YVIL_YouTube_Videos extends YVIL_Video_Post_Type{

	/**
	 * Holds the number of units used by various YouTube API requests.
	 * 
	 * @var int - number of units
	 */
	private $yt_units = 0;

	/**
	 * Fail safe check to see if widgets were already registered.
	 * Encountered situations when third party plugins were registering
	 * do_action('widgets_init') hook.
	 */
	private $widgets_init = false;

	/**
	 * Store admin object reference
	 * 
	 * @var YVIL_Admin
	 */
	private $admin;

	/**
	 *
	 * @var YVIL_Load_Timers
	 */
	private $load_timers;

	/**
	 * Constructor, sets up various actions and filters
	 */
	public function __construct(){
		// check for PRO version
		if( defined( 'CBC_PATH' ) ){
			return;
		}

		parent::__construct();
		$options = yvil_get_settings();

		// start REST API compatibility
		new YVIL_REST_API( $this );

		// register a timers collection
		$this->load_timers = new YVIL_Load_Timers();
		
		// allows differential loading on front-end and back-end
		add_action( 'init', array( 
				$this, 
				'on_init' 
		), - 99999 );
		
		// enqueue video player script on video pages
		add_action( 'wp_print_scripts', array( 
				$this, 
				'print_scripts' 
		) );
		
		// first post content filter, removes WP autoembed if video descriptions contain youtube links
		add_filter( 'the_content', array( 
				$this, 
				'content_filter' 
		), 1 );

		// activation hook
		register_activation_hook( __FILE__, array( 
				$this, 
				'on_activation' 
		) );
		
		// fire up widgets
		add_action( 'widgets_init', array( 
				$this, 
				'init_widgets' 
		) );
		
		// hook to update the number of used YouTube units by various YouTube API requests
		add_action( 'cbc_yt_api_query', array( 
				$this, 
				'add_yt_units' 
		), 10, 2 );
		add_action( 'shutdown', array( 
				$this, 
				'store_yt_units' 
		), 9999 );
		add_action( 'shutdown', array( 
				$this, 
				'log_timers_information' 
		), 999999 );
	}

	/**
	 * Init callback, loads different stuff differentially
	 * on front and back-end
	 */
	public function on_init(){

		// second post content filter, embeds the video
		add_filter( 'the_content', array(
			$this,
			'embed_video'
		), apply_filters( 'cbc_video_embed_filter_priority', 1 ) );

		// front-end
		if( ! is_admin() ){
			// start custom post type class
			include_once YVIL_PATH . 'includes/libs/shortcodes.class.php';
			new YVIL_Shortcodes();
		}
		
		// add administration resources
		if( is_admin() ){
			// load administration related functions
			require_once YVIL_PATH . 'includes/admin/functions.php';
			// add administration class
			require_once YVIL_PATH . 'includes/admin/libs/class-cbc-admin.php';
			$this->admin = new YVIL_Admin( $this );
		}
	}

	/**
	 * wp_print_scripts callback
	 * Enqueue video player scripts in front-end
	 */
	public function print_scripts(){
		if( is_admin() || ! is_singular( parent::get_post_type() ) ){
			return;
		}
		
		yvil_enqueue_player();
	}

	/**
	 * Process the post content to remove autoembeds if needed
	 * 
	 * @param string $content
	 */
	public function content_filter( $content ){
		if( is_admin() || ! yvil_is_video() ){
			return $content;
		}
		
		$settings = yvil_get_settings();
		if( isset( $settings[ 'prevent_autoembed' ] ) && $settings[ 'prevent_autoembed' ] ){
			// remove the autoembed filter
			remove_filter( 'the_content', array( 
					$GLOBALS[ 'wp_embed' ], 
					'autoembed' 
			), 8 );
		}
		
		return $content;
	}

	/**
	 * the_content callback function
	 * Embeds video in post content
	 * 
	 * @param string $content
	 */
	public function embed_video( $content ){
		// plugin settings
		$plugin_settings = yvil_get_settings();
		$is_visible = is_singular( parent::get_post_type() );
		
		if( is_admin() || ! $is_visible || ! yvil_is_video() ){
			return $content;
		}
		
		global $post;
		$settings = yvil_get_video_settings( $post->ID, true );
		$video = parent::get_post_video_data( $post );
		
		if( ! $video instanceof YVIL_Video ){
			return $content;
		}
		
		/**
		 * Filter that allows prevention of automatically embedding
		 * videos on post/video post type pages.
		 * 
		 * @var bool - allow or deny embedding
		 * @deprecated 1.3 Use yvil_embed_allowed instead
		 */
		$allow_embed = apply_filters( 'ccb_embed_videos', true, $post, $video->to_array() );
		
		/**
		 * Filter that allows prevention of automatically embedding
		 * videos on post/video post type pages.
		 * 
		 * @var bool - allow or deny embedding
		 */
		$allow_embed = apply_filters( 'cbc_embed_allowed', $allow_embed, $post, $video->to_array() );
		
		if( ! $allow_embed ){
			return $content;
		}
		
		$settings[ 'video_id' ] = $video->get_id();
		// player size
		$width = $settings[ 'width' ];
		$height = yvil_player_height( $settings[ 'aspect_ratio' ], $width );
		
		/**
		 * Filter that allows adding extra CSS classes on video container
		 * for styling.
		 * 
		 * @var array - array of classes
		 * @deprecated 1.3 Use cbc_embed_css_class
		 */
		$class = apply_filters( 'ccb_video_post_css_class', array(), $post );
		
		/**
		 * Filter that allows adding extra CSS classes on video container
		 * for styling.
		 * 
		 * @param array $classes - array of CSS classes
		 * @param WP_Post $post - the post object
		 */
		$class = apply_filters( 'cbc_embed_css_class', $class, $post );
		$extra_css = implode( ' ', $class );
		
		/**
		 * Filter that allows changing of embed settings before displaying the video
		 * 
		 * @param array - embed settings
		 * @param WP_Post $post - the post object
		 * @param array $video - the video details
		 */
		$settings = apply_filters( 'cbc_video_post_embed_options', $settings, $post, $video->to_array() );
		
		$embed_html = '<!-- player container -->';
		$css_class = 'ccb_single_video_player';
		$js_embed = apply_filters( 'cbc_js_embed', true, $post, $video->to_array() );
		if( true !== $js_embed ){
			$embed_url = 'https://www.youtube.com/embed/' . $video->get_id() . '?' . http_build_query( $settings, '', '&' );
			$css_class = 'ccb_iframe_embed';
			$embed_html = '<iframe src="' . $embed_url . '" width="100%" height="100%" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
		}
		
		// the video container
		$video_container = '<div class="' . $css_class . ' ' . $extra_css . '" ' . yvil_data_attributes( $settings ) . ' style="width:' . $width . 'px; height:' . $height . 'px; max-width:100%;">' . $embed_html . '</div>';
		
		/**
		 * Apply a filter on the video container to allow third party scripts to modify the output
		 * if needed
		 * 
		 * @param string/HTML $video_container - the video container output
		 * @param WP_Post $post - the post object
		 * @param array $video - the video details
		 * @param array $settings - video options
		 * @deprecated 1.3 Use cbc_embed_html_container
		 */
		$video_container = apply_filters( 'ccb_html_video_container', $video_container, $post, $video->to_array(), $settings );
		
		/**
		 * Apply a filter on the video container to allow third party scripts to modify the output if needed
		 * 
		 * @param string/HTML $video_container - the video container output
		 * @param WP_Post $post - the post object
		 * @param array $video - the video details
		 * @param array $settings - video options
		 */
		$video_container = apply_filters( 'cbc_embed_html_container', $video_container, $post, $video->to_array(), $settings );
		
		/**
		 * Filter that can display content before the video output
		 * 
		 * @var string - HTML
		 * @param $post - post object
		 * @param $video - video array
		 * @deprecated 1.3 Use cbc_embed_before
		 */
		$before_video = apply_filters( 'cbc_before_video_embed', '', $post, $video->to_array() );
		
		/**
		 * Filter that can display content before the video output
		 * 
		 * @var string - HTML
		 * @param $post - post object
		 * @param $video - video array
		 */
		$before_video = apply_filters( 'cbc_embed_before', $before_video, $post, $video->to_array() );
		
		/**
		 * Filter that can display content after the video output
		 * 
		 * @var string - HTML
		 * @param $post - post object
		 * @param $video - video array
		 * @deprecated 1.3 Use cbc_embed_after
		 */
		$after_video = apply_filters( 'cbc_after_video_embed', '', $post, $video->to_array() );
		
		/**
		 * Filter that can display content after the video output
		 * 
		 * @var string - HTML
		 * @param $post - post object
		 * @param $video - video array
		 */
		$after_video = apply_filters( 'cbc_embed_after', $after_video, $post, $video->to_array() );
		
		if( true === $js_embed ){
			yvil_enqueue_player();
		}
		
		// put the filter back for other posts; remove in function 'yvil_first_content_filter'
		add_filter( 'the_content', array( 
				$GLOBALS[ 'wp_embed' ], 
				'autoembed' 
		), 8 );
		
		/**
		 * Change embed position globally for all video posts
		 * 
		 * @var boolean - embed below post content (true) or above it (false)
		 * @param $post - post object
		 * @param $video - video array
		 */
		$embed_below = apply_filters( 'cbc_video_post_embed_below_content', ( 'below-content' == $settings[ 'video_position' ] ), $post, $video->to_array() );

		if( true === $embed_below ){
			return $content . $before_video . $video_container . $after_video;
		}else{
			return $before_video . $video_container . $after_video . $content;
		}
	}

	/**
	 * Plugin activation hook callback
	 */
	public function on_activation(){
		// register custom post
		parent::register_post();
		// create rewrite ( soft )
		flush_rewrite_rules( false );
		
		$this->on_init();
		if( $this->admin ){
			$this->admin->plugin_activation();
		}
	}

	/**
	 * Initialize plugin widgets
	 */
	public function init_widgets(){
		// check if widgets weren't already initialized
		if( $this->widgets_init ){
			return;
		}
		$this->widgets_init = true;
		// check if posts are public
		$options = yvil_get_settings();
		if( ! isset( $options[ 'public' ] ) || ! $options[ 'public' ] ){
			return;
		}
		// widget caching class
		include_once YVIL_PATH . 'includes/libs/cache.class.php';
		// latest videos widget
		include_once YVIL_PATH . 'includes/libs/latest-videos-widget.class.php';
		register_widget( 'YVIL_Latest_Videos_Widget' );
		// videos taxonomy widget
		include_once YVIL_PATH . 'includes/libs/videos-taxonomy-widget.class.php';
		register_widget( 'YVIL_Videos_Taxonomy_Widget' );
	}

	/**
	 * Store the number of units used on a page display.
	 * 
	 * @param string $endpoint
	 * @param int $units
	 */
	public function add_yt_units( $endpoint, $units ){
		$this->yt_units += $units;
	}

	/**
	 * Store any consumed units into plugin option
	 */
	public function store_yt_units(){
		if( 0 == $this->yt_units ){
			return;
		}
		
		// set timezone to PST
		date_default_timezone_set( 'America/Los_Angeles' );
		$day = date( 'z' );
		$stats = get_option( 'cbc_daily_yt_units', array( 
				'day' => - 1, 
				'count' => 0 
		) );
		
		// no units used, no reset needed, no need to do anything
		if( 0 == $this->yt_units && $day == $stats[ 'day' ] ){
			return;
		}
		
		// reset count if day changed
		if( $day != $stats[ 'day' ] ){
			$stats[ 'count' ] = 0;
			$stats[ 'day' ] = $day;
		}
		// update count
		$stats[ 'count' ] += $this->yt_units;
		// update option
		update_option( 'cbc_daily_yt_units', $stats );
	}

	/**
	 * Returns admin object reference
	 * 
	 * @return YVIL_Admin
	 */
	public function __get_admin(){
		return $this->admin;
	}

	/**
	 * Returns the load timers collection
	 * 
	 * @return YVIL_Load_Timers
	 */
	public function __get_load_timers(){
		return $this->load_timers;
	}

	/**
	 * Generates log entries for script loading timers
	 */
	public function log_timers_information(){
		$timers = $this->__get_load_timers();
		
		$report = $timers->generate_report();
		if( $report ){
			_yvil_debug_message( $timers->generate_report() );
		}
	}
}
global $YVIL_POST_TYPE;
$YVIL_POST_TYPE = new YVIL_YouTube_Videos();
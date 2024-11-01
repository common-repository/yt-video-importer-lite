<?php

/**
 * Latest videos widget
 */
class YVIL_Latest_Videos_Widget extends WP_Widget{

	/**
	 * stores cache object reference
	 * 
	 * @var YVIL_Widget_Cache
	 */
	private $cache;
	
	/**
	 * @var YVIL_Video_Post_Type
	 */
	private $obj;
	
	/**
	 * Constructor
	 */
	public function __construct(){
		/* Widget settings. */
		$widget_options = array( 
				'classname' => 'cbc-latest-videos', 
				'description' => __( 'The most recent videos on your site.', 'yt-video-importer-lite' )
		);
		
		/* Widget control settings. */
		$control_options = array( 
				'id_base' => 'cbc-latest-videos-widget' 
		);
		
		/* Create the widget. */
		parent::__construct( 'cbc-latest-videos-widget', __( 'Recent videos', 'yt-video-importer-lite' ), $widget_options, $control_options );
		
		$this->cache = new YVIL_Cache( 'cbc_latest_videos_widget' );
		$this->obj = yvil_get_class_instance();
	}

	/**
	 * Determines if widget is allowed to be cached and returns the cached data in case it is.
	 * 
	 * @param array $instance
	 */
	private function output_cached_widget( $instance ){
		// if widget shouldn't be cached, stop
		if( ! isset( $instance[ 'cbc_cache_widget' ] ) || ! $instance[ 'cbc_cache_widget' ] ){
			return false;
		}
		
		// get cached widget if available
		$cached = $this->cache->get_cached_item( $this->id );
		// display cached version if found
		if( $cached ){
			echo '<!-- cbc_youtube : begin cached widget output -->';
			// display widget output
			echo $this->cache->get_cached_item_output( $this->id );
			// get widget data
			$data = $this->cache->get_cached_item_data( $this->id );
			// if displaying a JS playlist enqueue neccessary scripts and styles
			if( 'playlist' == $data[ 'type' ] ){
				yvil_enqueue_player();
				wp_enqueue_script( 'cbc-yt-player-default', YVIL_URL . 'themes/default/assets/script.js', array( 
						'ccb-video-player' 
				), '1.0' );
				wp_enqueue_style( 'ccb-yt-player-default', YVIL_URL . 'themes/default/assets/stylesheet.css', false, '1.0' );
			}
			echo '<!-- cbc_youtube : end cached widget output -->';
			return true;
		}
		
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * 
	 * @see WP_Widget::widget()
	 */
	function widget( $args, $instance ){
		// display cached version if available and enabled
		$cached = $this->output_cached_widget( $instance );
		if( $cached ){
			return;
		}
		
		extract( $args );
		$posts = absint( $instance[ 'cbc_posts_number' ] );
		
		$widget_title = '';
		if( isset( $instance[ 'cbc_widget_title' ] ) && ! empty( $instance[ 'cbc_widget_title' ] ) ){
			$widget_title = $before_title . apply_filters( 'widget_title', $instance[ 'cbc_widget_title' ] ) . $after_title;
		}
		
		// start output buffering
		ob_start();
		
		// if setting to display player is set, show it
		if( isset( $instance[ 'cbc_show_playlist' ] ) && $instance[ 'cbc_show_playlist' ] ){
			$player_settings = array( 
					'width' => $instance[ 'width' ], 
					'aspect_ratio' => $instance[ 'aspect_ratio' ], 
					'volume' => $instance[ 'volume' ] 
			);
			
			echo $before_widget;
			echo $widget_title;
			echo yvil_output_playlist( 'latest', $posts, 'default', $player_settings, $instance[ 'cbc_posts_tax' ] );
			echo $after_widget;
			
			// add output to cache
			$output = $this->cache->add_to_cache( $this->id, ob_get_contents(), array( 
					'type' => 'playlist' 
			) );
			
			ob_end_clean();
			
			echo $output;
			return;
		}
		
		$args = array( 
				'numberposts' => $posts, 
				'posts_per_page' => $posts, 
				'orderby' => 'post_date', 
				'order' => 'DESC', 
				'post_type' => $this->obj->get_post_type(), 
				'post_status' => 'publish', 
				'suppress_filters' => true 
		);
		
		if( isset( $instance[ 'cbc_posts_tax' ] ) && ! empty( $instance[ 'cbc_posts_tax' ] ) && ( ( int ) $instance[ 'cbc_posts_tax' ] ) > 0 ){
			$term = get_term( $instance[ 'cbc_posts_tax' ], $this->obj->get_post_tax(), ARRAY_A );
			if( ! is_wp_error( $term ) ){
				$args[ $this->obj->get_post_tax() ] = $term[ 'slug' ];
			}
		}
		
		// display a list of video posts
		$posts = get_posts( $args );
		if( ! $posts ){
			return;
		}
		
		echo $before_widget;
		
		if( ! empty( $instance[ 'cbc_widget_title' ] ) ){
			echo $before_title . apply_filters( 'widget_title', $instance[ 'cbc_widget_title' ] ) . $after_title;
		}
		?>
<ul class="cbc-recent-videos-widget">
			<?php foreach($posts as $post):?>
			<?php
			$thumbnail = '';
			if( $instance[ 'cbc_yt_image' ] ){
				$video_obj = $this->obj->get_post_video_data( $post );
				$thumbnails = $video_obj->get_thumbnails();
				$thumbnail_url = is_array( $thumbnails ) ? reset( $thumbnails ) : false;
				if( isset( $thumbnail_url[ 'url' ] ) ){
					$thumbnail = sprintf( '<img src="%s" alt="%s" />', $thumbnail_url[ 'url' ], apply_filters( 'the_title', $post->post_title ) );
				}
			}
			?>
			<li><a href="<?php echo get_permalink($post->ID);?>"
		title="<?php echo apply_filters('the_title', $post->post_title);?>"><?php echo $thumbnail;?> <?php echo apply_filters('post_title', $post->post_title);?></a></li>
			<?php endforeach;?>
		</ul>
<?php
		echo $after_widget;
		// store output to cache
		$output = $this->cache->add_to_cache( $this->id, ob_get_contents(), array( 
				'type' => 'regular' 
		) );
		ob_end_clean();
		
		echo $output;
	}

	/**
	 * (non-PHPdoc)
	 * 
	 * @see WP_Widget::update()
	 */
	function update( $new_instance, $old_instance ){
		$instance = $old_instance;
		$instance[ 'cbc_widget_title' ] = $new_instance[ 'cbc_widget_title' ];
		$instance[ 'cbc_posts_number' ] = ( int ) $new_instance[ 'cbc_posts_number' ];
		$instance[ 'cbc_posts_tax' ] = ( int ) $new_instance[ 'cbc_posts_tax' ];
		$instance[ 'cbc_yt_image' ] = ( bool ) $new_instance[ 'cbc_yt_image' ];
		$instance[ 'cbc_show_playlist' ] = ( bool ) $new_instance[ 'cbc_show_playlist' ];
		$instance[ 'aspect_ratio' ] = $new_instance[ 'aspect_ratio' ];
		$instance[ 'width' ] = absint( $new_instance[ 'width' ] );
		$instance[ 'volume' ] = absint( $new_instance[ 'volume' ] );
		$instance[ 'cbc_cache_widget' ] = isset($new_instance[ 'cbc_cache_widget' ]) ? ( bool ) $new_instance[ 'cbc_cache_widget' ] : false;
		
		$this->cache->unset_cached_item( $this->id );
		
		return $instance;
	}

	/**
	 * (non-PHPdoc)
	 * 
	 * @see WP_Widget::form()
	 */
	function form( $instance ){
		$defaults = $this->get_defaults();
		;
		$options = wp_parse_args( ( array ) $instance, $defaults );
		
		?>
<div class="cbc-player-settings-options">
	<p>
		<label for="<?php echo  $this->get_field_id('cbc_widget_title');?>"><?php _e('Title', 'yt-video-importer-lite');?>: </label>
		<input type="text"
			name="<?php echo  $this->get_field_name('cbc_widget_title');?>"
			id="<?php echo  $this->get_field_id('cbc_widget_title');?>"
			value="<?php echo $options['cbc_widget_title'];?>" class="widefat" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id('cbc_posts_number');?>"><?php _e('Number of videos to show', 'yt-video-importer-lite');?>: </label>
		<input type="text"
			name="<?php echo $this->get_field_name('cbc_posts_number');?>"
			id="<?php echo $this->get_field_id('cbc_posts_number');?>"
			value="<?php echo $options['cbc_posts_number'];?>" size="3" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id('cbc_posts_tax');?>"><?php _e('Category', 'yt-video-importer-lite');?>: </label>
			<?php
		$args = array( 
				'show_option_all' => false, 
				'show_option_none' => __( 'All categories', 'yt-video-importer-lite' ),
				'orderby' => 'NAME', 
				'order' => 'ASC', 
				'show_count' => true, 
				'hide_empty' => false, 
				'selected' => $options[ 'cbc_posts_tax' ], 
				'hierarchical' => true, 
				'name' => $this->get_field_name( 'cbc_posts_tax' ), 
				'id' => $this->get_field_id( 'cbc_posts_tax' ), 
				'taxonomy' => $this->obj->get_post_tax(), 
				'hide_if_empty' => true 
		);
		$select = wp_dropdown_categories( $args );
		if( ! $select ){
			_e( 'Nothing found.', 'yt-video-importer-lite' );
			?>
					<input type="hidden"
			name="<?php echo $this->get_field_name('cbc_posts_tax');?>"
			id="<?php echo $this->get_field_id('cbc_posts_tax');?>" value="" />
					<?php
		}
		?>
		</p>
	<p class="cbc-widget-show-yt-thumbs"
		<?php if( $options['cbc_show_playlist'] ):?> style="display: none;"
		<?php endif;?>>
		<input class="checkbox" type="checkbox"
			name="<?php echo $this->get_field_name('cbc_yt_image')?>"
			id="<?php echo $this->get_field_id('cbc_yt_image');?>"
			<?php yvil_check( (bool)$options['cbc_yt_image'] );?> /> <label
			for="<?php echo $this->get_field_id('cbc_yt_image');?>"><?php _e('Display YouTube thumbnails?', 'yt-video-importer-lite');?></label>
	</p>
	<p>
		<input class="checkbox cbc-show-as-playlist-widget" type="checkbox"
			name="<?php echo $this->get_field_name('cbc_show_playlist');?>"
			id="<?php echo $this->get_field_id('cbc_show_playlist')?>"
			<?php yvil_check((bool)$options['cbc_show_playlist']);?> /> <label
			for="<?php echo $this->get_field_id('cbc_show_playlist')?>"><?php _e('Show as video playlist', 'yt-video-importer-lite');?></label>
	</p>
	<div class="cbc-recent-videos-playlist-options"
		<?php if( !$options['cbc_show_playlist'] ):?> style="display: none;"
		<?php endif;?>>

		<p>
			<label for="cbc_aspect_ratio"><?php _e('Aspect');?> :</label>
				<?php
		$args = array( 
				'options' => array( 
						'4x3' => '4x3', 
						'16x9' => '16x9' 
				), 
				'name' => $this->get_field_name( 'aspect_ratio' ), 
				'id' => $this->get_field_id( 'aspect_ratio' ), 
				'class' => 'cbc_aspect_ratio', 
				'selected' => $options[ 'aspect_ratio' ] 
		);
		yvil_select( $args );
		?><br /> <label for="<?php echo $this->get_field_id('width')?>"><?php _e('Width', 'yt-video-importer-lite');?> :</label>
			<input type="text" class="cbc_width"
				name="<?php echo $this->get_field_name('width');?>"
				id="<?php echo $this->get_field_id('width')?>"
				value="<?php echo $options['width'];?>" size="2" />px
				| <?php _e('Height', 'yt-video-importer-lite');?> : <span class="cbc_height"
				id="<?php echo $this->get_field_id('cbc_calc_height')?>"><?php echo yvil_player_height( $options['aspect_ratio'], $options['width'] );?></span>px
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('volume');?>"><?php _e('Volume', 'yt-video-importer-lite');?></label>
			: <input type="text"
				name="<?php echo $this->get_field_name('volume');?>"
				id="<?php echo $this->get_field_id('volume');?>"
				value="<?php echo $options['volume'];?>" size="1" maxlength="3" /> <label
				for="<?php echo $this->get_field_id('volume');?>"><span
				class="description"><?php _e('number between 0 (mute) and 100 (max)', 'yt-video-importer-lite');?></span></label>

		</p>
	</div>
	<p>
		<input type="checkbox"
			name="<?php echo $this->get_field_name( 'cbc_cache_widget' );?>"
			id="<?php echo $this->get_field_id( 'cbc_cache_widget' );?>"
			value="1" <?php checked( (bool)$options['cbc_cache_widget'] );?> /> <label
			for="<?php echo $this->get_field_id( 'cbc_cache_widget' );?>"><?php _e( 'Enable widget caching (5 minutes interval)?', 'yt-video-importer-lite' );?></label>
	</p>
</div>
<?php
	}

	/**
	 * Default widget values
	 */
	private function get_defaults(){
		$player_defaults = yvil_get_player_settings();
		$defaults = array( 
				'cbc_widget_title' => '', 
				'cbc_posts_number' => 5, 
				'cbc_yt_image' => false, 
				'cbc_show_playlist' => false, 
				'cbc_posts_tax' => - 1, 
				'aspect_ratio' => $player_defaults[ 'aspect_ratio' ], 
				'width' => $player_defaults[ 'width' ], 
				'volume' => $player_defaults[ 'volume' ], 
				'cbc_cache_widget' => false 
		);
		return $defaults;
	}
}
<?php

/**
 * Custom post type taxonomy widget
 */
class YVIL_Videos_Taxonomy_Widget extends WP_Widget{

	/**
	 * Stores cache object reference
	 * 
	 * @var YVIL_Cache
	 */
	private $cache;
	/**
	 * 
	 * @var YVIL_Video_Post_Type
	 */
	private $obj;
	
	/**
	 * Constructor
	 */
	public function __construct(){
		/* Widget settings. */
		$widget_options = array( 
				'classname' => 'widget_categories', 
				'description' => __( 'Video categories.', 'yt-video-importer-lite' )
		);
		
		/* Widget control settings. */
		$control_options = array( 
				'id_base' => 'cbc-taxonomy-video-widget' 
		);
		
		/* Create the widget. */
		parent::__construct( 'cbc-taxonomy-video-widget', __( 'Video Categories', 'yt-video-importer-lite' ), $widget_options, $control_options );
		
		$this->cache = new YVIL_Cache( 'cbc_videos_taxonomy_widget' );
		$this->obj = yvil_get_class_instance();
	}

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
		$widget_title = '';
		if( isset( $instance[ 'cbc_widget_title' ] ) && ! empty( $instance[ 'cbc_widget_title' ] ) ){
			$widget_title = $before_title . apply_filters( 'widget_title', $instance[ 'cbc_widget_title' ] ) . $after_title;
		}
		// start output buffer
		ob_start();
		
		$args = array( 
				'show_option_all' => false, 
				'orderby' => 'name', 
				'order' => 'ASC', 
				'style' => 'list', 
				'show_count' => ( bool ) $instance[ 'cbc_post_count' ], 
				'hide_empty' => true, 
				'use_desc_for_title' => true, 
				'hierarchical' => ( bool ) $instance[ 'cbc_hierarchy' ], 
				'title_li' => false, 
				'show_option_none' => __( 'No video categories', 'yt-video-importer-lite' ),
				'number' => null, 
				'echo' => 1, 
				'depth' => 0, 
				'current_category' => 0, 
				'pad_counts' => 0, 
				'taxonomy' => $this->obj->get_post_tax() 
		);
		
		echo $before_widget;
		
		if( ! empty( $instance[ 'cbc_widget_title' ] ) ){
			echo $before_title . apply_filters( 'widget_title', $instance[ 'cbc_widget_title' ] ) . $after_title;
		}
		?>
<ul>
			<?php wp_list_categories( $args );?>
		</ul>
<?php
		echo $after_widget;
		
		$output = $this->cache->add_to_cache( $this->id, ob_get_contents() );
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
		$instance[ 'cbc_post_count' ] = ( bool ) $new_instance[ 'cbc_post_count' ];
		$instance[ 'cbc_hierarchy' ] = ( bool ) $new_instance[ 'cbc_hierarchy' ];
		$instance[ 'cbc_cache_widget' ] = ( bool ) $new_instance[ 'cbc_cache_widget' ];
		// reset the cache
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
		<input class="checkbox cbc_post_count" type="checkbox"
			name="<?php echo $this->get_field_name('cbc_post_count');?>"
			id="<?php echo $this->get_field_id('cbc_post_count')?>"
			<?php yvil_check((bool)$options['cbc_post_count']);?> /> <label
			for="<?php echo $this->get_field_id('cbc_post_count')?>"><?php _e('Show video counts', 'yt-video-importer-lite');?></label>
	</p>
	<p>
		<input class="checkbox cbc_hierarchy" type="checkbox"
			name="<?php echo $this->get_field_name('cbc_hierarchy');?>"
			id="<?php echo $this->get_field_id('cbc_hierarchy')?>"
			<?php yvil_check((bool)$options['cbc_hierarchy']);?> /> <label
			for="<?php echo $this->get_field_id('cbc_hierarchy')?>"><?php _e('Show hierarchy', 'yt-video-importer-lite');?></label>
	</p>
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
				'cbc_widget_title' => __( 'Video categories', 'yt-video-importer-lite' ),
				'cbc_post_count' => false, 
				'cbc_hierarchy' => false, 
				'cbc_cache_widget' => false 
		);
		return $defaults;
	}
}
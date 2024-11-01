<?php

class YVIL_Video_List{

	private $items = array();

	private $feed_errors = false;

	private $total_items = 0;

	private $next_token = '';

	private $prev_token = '';

	public function display(){
		if( ! $this->items ){
			$this->no_items();
			return;
		}
		// show top navigation
		$this->navigation( 'top' );
		
		echo '<div class="cbc-video-import-list">';
		foreach( $this->items as $item ){
			$this->display_item( $item );
		}
		echo '</div>';
		
		// show bottom navigation
		$this->navigation( 'bottom' );
	}
	
	/**
	 * 
	 * @param YVIL_Video $item
	 */
	private function display_item( YVIL_Video $item ){
		$thumbnail = $item->get_thumbnail_url( 'medium' );
		$view_class = get_user_option( 'cbc_video_import_view' );
		if( ! $view_class ){
			$view = 'grid';
		}
		?>
<div class="cbc-video-item <?php echo $view_class;?>">
	<div class="item-title">
		<input type="text" name="cbc_title[<?php echo $item->get_id();?>]"
			value="<?php echo esc_attr( $item->get_title() );?>" class="item-title" />
	</div>
	<div class="cbc-left">
		<img src="<?php echo $thumbnail?>" class="item-image" />
	</div>
	<div class="cbc-right">
		<textarea name="cbc_text[<?php echo $item->get_id();?>]"><?php echo $item->get_description();?></textarea>
	</div>
	<div class="controls">
		<strong class="duration"><?php echo $item->get_human_duration();?></strong> / <?php printf( _n( '1 view', '%s views', $item->get_views_count() ), number_format_i18n( $item->get_views_count() ) );?> 
		<a
			href="https://www.youtube.com/watch?v=<?php echo $item->get_id();?>"
			target="_blank"><span class="dashicons dashicons-admin-links"></span></a><br />
		<strong><?php printf( __('Published on %s', 'yt-video-importer-lite'), date('D, M d, Y', strtotime( $item->get_publish_date() ) ) );?></strong><br />
		<hr />
		<label><?php printf( '<input type="checkbox" name="cbc_import[]" value="%1$s" id="cbc_video_%1$s" class="cbc-item-check" />', $item->get_id() );?>
		<?php _e( 'Import this video', 'yt-video-importer-lite' );?>
		</label>
	</div>
</div>
<?php
	}

	/**
	 * Display navigation
	 * 
	 * @param string $which
	 */
	private function navigation( $which = 'top' ){
		$which = 'bottom' == $which ? 'bottom' : 'top';
		$suffix = 'top' == $which ? '_top' : '2';
		
		// plugin options
		$options = yvil_get_settings();
		// set selected category
		$selected = false;
		if( isset( $_GET[ 'cat' ] ) ){
			$selected = $_GET[ 'cat' ];
		}
		// dropdown arguments
		$args = array( 
				'show_count' => 1, 
				'hide_empty' => 0, 
				'taxonomy' => 'videos', 
				'name' => 'cat' . $suffix, 
				'id' => 'cbc_video_categories' . $suffix, 
				'selected' => $selected, 
				'hide_if_empty' => true, 
				'echo' => false 
		);
		if( isset( $options ) && $options[ 'import_categories' ] ){
			$args[ 'show_option_all' ] = __( 'Create categories from YouTube', 'yt-video-importer-lite' );
		}else{
			$args[ 'show_option_all' ] = __( 'Select category (optional)', 'yt-video-importer-lite' );
		}
		// get dropdown output
		$categ_select = wp_dropdown_categories( $args );
		// users dropdown
		$users = wp_dropdown_users( array( 
				'show_option_all' => __( 'Current user', 'yt-video-importer-lite' ),
				'echo' => false, 
				'name' => 'user' . $suffix, 
				'id' => 'cbc_video_user' . $suffix, 
				'hide_if_only_one_author' => true 
		) );
		?>
<div class="tablenav <?php echo $which;?>">
	<label class="sel_all"><input type="checkbox" value="1"
		name="select_all" id="select_all" /> <?php _e('Select all', 'yt-video-importer-lite');?></label>

	<input type="hidden" name="action<?php echo $suffix?>" value="import" />
		
    <?php if( $categ_select ):?>
    	<label for="cbc_video_categories<?php echo $suffix;?>"><?php _e('Import into category', 'yt-video-importer-lite');?> :</label>
		<?php echo $categ_select;?>
	<?php endif;?>
	
	<?php if( $users ):?>
		<label for="cbc_video_user<?php echo $suffix;?>"><?php _e('Import as user', 'yt-video-importer-lite');?> :</label>
		<?php echo $users;?>
	<?php endif;?>
	
	<?php submit_button( __( 'Import videos' ), 'action', false, false, array( 'id' => "doaction$suffix" ) );?>		
    <span class="cbc-ajax-response"></span>
    	
	<?php $this->pagination();?>
</div>
<?php
	}

	/**
	 * Display pagination
	 */
	private function pagination(){
		$current_url = set_url_scheme( 'http://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ] );
		$current_url = remove_query_arg( array( 
				'hotkeys_highlight_last', 
				'hotkeys_highlight_first' 
		), $current_url );
		$disable_first = empty( $this->prev_token ) ? ' disabled' : false;
		$disable_last = empty( $this->next_token ) ? ' disabled' : false;
		
		$prev_page = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>", 'prev-page' . $disable_first, esc_attr__( 'Go to the previous page', 'yt-video-importer-lite' ), esc_url( add_query_arg( 'token', $this->prev_token, $current_url ) ), '&lsaquo;' );
		
		$view = get_user_option( 'cbc_video_import_view' );
		if( ! $view ){
			$view = 'grid';
		}
		?>
<div class="tablenav-pages">
	<span class="displaying-num"><?php printf( _n( '1 item', '%s items', $this->total_items ), number_format_i18n( $this->total_items ) );?></span>
	<span class="pagination-links">
		<?php
		// prev page
		printf( "<a class='%s' title='%s' href='%s'>%s</a>", 'prev-page' . $disable_first, esc_attr__( 'Go to the previous page', 'yt-video-importer-lite' ), esc_url( add_query_arg( 'token', $this->prev_token, $current_url ) ), '&lsaquo;' );
		?>
		<?php
		// next page
		printf( "<a class='%s' title='%s' href='%s'>%s</a>", 'prev-page' . $disable_last, esc_attr__( 'Go to the next page', 'yt-video-importer-lite' ), esc_url( add_query_arg( 'token', $this->next_token, $current_url ) ), '&rsaquo;' );
		?>
	</span>
</div>
<div class="view-switch">
	<a id="view-switch-grid"
		class="cbc-view view-grid<?php if( 'grid' == $view ):?> current<?php endif;?>"
		href="#" data-view="grid"><span class="screen-reader-text"><?php _e( 'Grid View', 'yt-video-importer-lite' );?></span></a>
	<a id="view-switch-list"
		class="cbc-view view-list<?php if( 'list' == $view ):?> current<?php endif;?>"
		href="#" data-view="list"><span class="screen-reader-text"><?php _e('List View', 'yt-video-importer-lite');?></span></a>
</div>
<?php
	}

	/**
	 * Displays a message if playlist is empty
	 */
	public function no_items(){
		_e( 'YouTube feed is empty.', 'yt-video-importer-lite' );
		if( is_wp_error( $this->feed_errors ) ){
			echo '<br />';
			printf( __( ' <strong>API error (code: %s)</strong>: %s', 'yt-video-importer-lite' ), $this->feed_errors->get_error_code(), $this->feed_errors->get_error_message() );
		}
	}

	/**
	 * Makes YouTube API query for videos and populates vlass variables
	 */
	public function prepare_items(){
		$videos = array();
		$token = isset( $_GET[ 'token' ] ) ? $_GET[ 'token' ] : '';
		
		switch( $_GET[ 'cbc_feed' ] ){
			case 'user':
			case 'playlist':
			case 'channel':
				$args = array( 
						'type' => 'manual', 
						'query' => $_GET[ 'cbc_query' ], 
						'page_token' => $token, 
						'include_categories' => true, 
						'playlist_type' => $_GET[ 'cbc_feed' ] 
				);
				
				$q = yvil_yt_api_get_list( $args );
				
				$videos = $q[ 'videos' ];
				$list_stats = $q[ 'page_info' ];
			break;
			// perform a search query
			case 'query':
				$args = array( 
						'query' => $_GET[ 'cbc_query' ], 
						'page_token' => $token, 
						'order' => $_GET[ 'cbc_order' ], 
						'duration' => $_GET[ 'cbc_duration' ] 
				);
				$q = yvil_yt_api_search_videos( $args );
				$videos = $q[ 'videos' ];
				$list_stats = $q[ 'page_info' ];
			
			break;
		}
		
		if( is_wp_error( $videos ) ){
			$this->feed_errors = $videos;
			$videos = array();
		}
		
		$this->items = $videos;
		$this->total_items = $list_stats[ 'total_results' ];
		$this->next_token = $list_stats[ 'next_page' ];
		$this->prev_token = $list_stats[ 'prev_page' ];
	}
}
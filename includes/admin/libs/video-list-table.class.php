<?php
/**
 * Load WP_List_Table class
 */
if( ! class_exists( 'WP_List_Table' ) ){
	require_once ( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class YVIL_Video_List_Table extends WP_List_Table{
	
	/**
	 * 
	 * @var YVIL_Video_Post_Type
	 */
	private $obj;
	/**
	 * 
	 * @param YVIL_Video_Post_Type $cpt
	 */
	function __construct( YVIL_Video_Post_Type $cpt ){
		$this->obj = $cpt;
		parent::__construct( array( 
				'singular' => 'video', 
				'plural' => 'videos', 
				'screen' => isset( $args[ 'screen' ] ) ? $args[ 'screen' ] : null 
		) );
	}

	/**
	 * Default column
	 * 
	 * @param array $item
	 * @param string $column
	 */
	function column_default( $item, $column ){
		return $item[ $column ];
	}

	/**
	 * Title
	 * 
	 * @param array $item
	 */
	function column_post_title( $item ){
		$video_obj = $this->obj->get_post_video_data( $item['ID'] );
		$label = sprintf( '<label for="cbc-video-%1$s" id="title%1$s" class="cbc_video_label">%2$s</label>', $item[ 'ID' ], $item[ 'post_title' ] );
		
		$settings = yvil_get_video_settings( $item[ 'ID' ] );
		
		$form = '<div class="single-video-settings" id="single-video-settings-' . $item[ 'ID' ] . '">';
		$form .= '<h4>' . $item[ 'post_title' ] . ' (' . $video_obj->get_human_duration() . ')</h4>';
		$form .= '<label for="cbc_volume' . $item[ 'ID' ] . '">' . __( 'Volume', 'yt-video-importer-lite' ) . '</label> <input size="3" type="text" name="volume[' . $item[ 'ID' ] . ']" id="cbc_volume' . $item[ 'ID' ] . '" value="' . $settings[ 'volume' ] . '" /><br />';
		$form .= '<label for="cbc_width' . $item[ 'ID' ] . '">' . __( 'Width', 'yt-video-importer-lite' ) . '</label> <input size="3" type="text" name="width[' . $item[ 'ID' ] . ']" id="cbc_width' . $item[ 'ID' ] . '" value="' . $settings[ 'width' ] . '" /><br />';
		
		$aspect_select = yvil_select( array(
				'options' => array( 
						'4x3' => '4x3', 
						'16x9' => '16x9' 
				), 
				'name' => 'aspect_ratio[' . $item[ 'ID' ] . ']', 
				'id' => 'cbc_aspect_ratio' . $item[ 'ID' ], 
				'selected' => $settings[ 'aspect_ratio' ] 
		), false );
		$form .= '<label for="cbc_aspect_ratio' . $item[ 'ID' ] . '">' . __( 'Aspect ratio', 'yt-video-importer-lite' ) . '</label> ' . $aspect_select . '<br />';
		$form .= '<input type="checkbox" name="autoplay[' . $item[ 'ID' ] . ']" id="cbc_autoplay' . $item[ 'ID' ] . '" value="1"' . yvil_check( ( bool ) $settings[ 'autoplay' ], false ) . ' /> <label class="inline" for="cbc_autoplay' . $item[ 'ID' ] . '">' . __( 'Auto play', 'yt-video-importer-lite' ) . '</label><br />';
		$form .= '<input type="checkbox" name="controls[' . $item[ 'ID' ] . ']" id="cbc_controls' . $item[ 'ID' ] . '" value="1"' . yvil_check( ( bool ) $settings[ 'controls' ], false ) . ' /> <label class="inline" for="cbc_controls' . $item[ 'ID' ] . '">' . __( 'Show player controls', 'yt-video-importer-lite' ) . '</label><br />';
		$form .= '<input type="button" id="shortcode' . $item[ 'ID' ] . '" value="' . __( 'Insert shortcode', 'yt-video-importer-lite' ) . '" class="button cbc-insert-shortcode" />';
		$form .= '<input type="button" id="cancel' . $item[ 'ID' ] . '" value="' . __( 'Cancel', 'yt-video-importer-lite' ) . '" class="button cbc-cancel-shortcode" />';
		$form .= '<div style="width:100%; display:block; clear:both"></div>';
		$form .= '</div>';
		
		// row actions
		$actions = array( 
				'shortcode' => sprintf( '<a href="#" id="cbc-embed-%1$s" class="cbc-show-form">%2$s</a>' . $form, $item[ 'ID' ], __( 'Get video shortcode', 'yt-video-importer-lite' ) )
		);
		
		return sprintf( '%1$s %2$s', $label, $this->row_actions( $actions ) );
	}

	/**
	 * Checkbox column
	 * 
	 * @param array $item
	 */
	function column_cb( $item ){
		return sprintf( '<input type="checkbox" name="%1$s" value="%2$s" id="%3$s" class="cbc-video-checkboxes">', 'cbc_video[]', $item[ 'ID' ], 'cbc-video-' . $item[ 'ID' ] );
	}

	/**
	 * YouTube video ID column
	 * 
	 * @param array $item
	 */
	function column_video_id( $item ){
		$video_obj = $this->obj->get_post_video_data( $item['ID'] );
		return $video_obj->get_id();
	}

	/**
	 * Video duration column
	 * 
	 * @param array $item
	 */
	function column_duration( $item ){
		$video_obj = $this->obj->get_post_video_data( $item['ID'] );
		return '<span id="duration' . $video_obj->get_id() . '">' . $video_obj->get_human_duration() . '</span>';
	}

	/**
	 * Display video categories
	 * 
	 * @param array $item
	 */
	function column_category( $item ){
		$taxonomy = $this->_get_taxonomy_view();
		if( $terms = get_the_terms( $item[ 'ID' ], $taxonomy ) ){
			$out = array();
			foreach( $terms as $t ){
				$url = add_query_arg( array( 
						'pt' => $this->_get_post_type_view(), 
						'page' => 'cbc_videos', 
						'cat' => $t->term_id 
				), 'edit.php' );
				
				$out[] = sprintf( '<a href="%s">%s</a>', $url, $t->name );
			}
			return implode( ', ', $out );
		}else{
			return '&#8212;';
		}
	}

	/**
	 * Returns the post type associated with the current view
	 */
	private function _get_post_type_view(){
		return $this->obj->get_post_type();
	}

	/**
	 * Returns the post type taxonomy associated with the current view
	 */
	private function _get_taxonomy_view(){
		$post_type = $this->_get_post_type_view();
		return $this->obj->get_post_tax();
	}

	/**
	 * Date column
	 * 
	 * @param array $item
	 */
	function column_post_date( $item ){
		$output = sprintf( '<abbr title="%s">%s</abbr><br />', $item[ 'post_date' ], mysql2date( __( 'Y/m/d' ), $item[ 'post_date' ] ) );
		$output .= 'publish' == $item[ 'post_status' ] ? __( 'Published', 'yt-video-importer-lite' ) : '';
		return $output;
	}

	function extra_tablenav( $which ){
		if( 'top' !== $which ){
			return;
		}
		
		$selected = false;
		if( isset( $_GET[ 'cat' ] ) ){
			$selected = $_GET[ 'cat' ];
		}
		
		$taxonomy = $this->obj->get_post_tax();
		$args = array(
				'show_option_all' => __( 'All categories', 'yt-video-importer-lite' ),
				'show_count' => 1, 
				'taxonomy' => $taxonomy, 
				'name' => 'cat', 
				'id' => 'cbc_video_categories', 
				'selected' => $selected, 
				'hide_if_empty' => true, 
				'echo' => false 
		);
		$categories_select = wp_dropdown_categories( $args );
		if( ! $categories_select ){
			return;
		}
		?>
<label for="cbc_video_categories"><?php _e('Categories', 'yt-video-importer-lite');?> :</label>
<?php echo $categories_select;?>
		<?php submit_button( __( 'Filter', 'yt-video-importer-lite' ), 'button-secondary apply', 'filter_videos', false );?>
		<?php
	}

	/**
	 * (non-PHPdoc)
	 * 
	 * @see WP_List_Table::get_columns()
	 */
	function get_columns(){
		$columns = array( 
				'cb' => '<input type="checkbox" class="ccb-video-list-select-all" />', 
				'post_title' => __( 'Title', 'yt-video-importer-lite' ),
				'video_id' => __( 'Video ID', 'yt-video-importer-lite' ),
				'duration' => __( 'Duration', 'yt-video-importer-lite' ),
				'category' => __( 'Category', 'yt-video-importer-lite' ),
				'post_date' => __( 'Date', 'yt-video-importer-lite' )
		);
		return $columns;
	}

	/**
	 * (non-PHPdoc)
	 * 
	 * @see WP_List_Table::prepare_items()
	 */
	function prepare_items(){
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		
		$this->_column_headers = array( 
				$columns, 
				$hidden, 
				$sortable 
		);
		
		$per_page = 20;
		$current_page = $this->get_pagenum();
		
		$search_for = '';
		if( isset( $_REQUEST[ 's' ] ) ){
			$search_for = esc_attr( stripslashes( $_REQUEST[ 's' ] ) );
		}
		
		$category = false;
		if( isset( $_GET[ 'cat' ] ) && $_GET[ 'cat' ] ){
			$category = ( int ) $_GET[ 'cat' ];
		}
		
		$args = array(
				'post_type' => $this->obj->get_post_type(),
				'orderby' => 'post_date', 
				'order' => 'DESC', 
				'posts_per_page' => $per_page, 
				'offset' => ( $current_page - 1 ) * $per_page, 
				'post_status' => 'publish', 
				's' => $search_for 
		);
		
		if( $category ){
			$args[ 'tax_query' ] = array(
                    array(
                            'taxonomy' => 'videos',
                            'field' => 'id',
                            'terms' => $category
                    )
            );
		}
		// remove filters to prevent other plugin from adding any other post types
		remove_all_filters( 'pre_get_posts' );
		$query = new WP_Query( $args );
		
		$data = array();
		if( $query->posts ){
			foreach( $query->posts as $k => $item ){
				$data[ $k ] = ( array ) $item;
			}
		}
		
		$total_items = $query->found_posts;
		$this->items = $data;
		
		$this->set_pagination_args( array( 
				'total_items' => $total_items, 
				'per_page' => $per_page, 
				'total_pages' => ceil( $total_items / $per_page ) 
		) );
	}
}
<?php

/**
 * Class YVIL_Video_Data_Cache
 * Cahes post video data for multiple uses
 */
class YVIL_Video_Data_Cache{
	/**
	 * @var array
	 */
	private $cached = array();
	/**
	 * @var YVIL_Video_Post_Type
	 */
	private $post_type;

	/**
	 * YVIL_Video_Data_Cache constructor.
	 *
	 * @param YVIL_Video_Post_Type $video_post_type
	 */
	public function __construct( YVIL_Video_Post_Type $video_post_type ) {
		$this->post_type = $video_post_type;
	}

	/**
	 * @param $post_id
	 *
	 * @return bool|YVIL_Video
	 */
	public function get_post_data( $post_id ){
		if( array_key_exists( $post_id, $this->cached ) ){
			return $this->cached[ $post_id ];
		}

		$video_data = $this->_get_post_meta( $post_id );
		if( $video_data ) {
			$this->cached[ $post_id ] = $video_data;
		}
		return $video_data;
	}

	/**
	 * @param $post_id
	 *
	 * @return bool|YVIL_Video
	 */
	private function _get_post_meta( $post_id ){
		$video_data = get_post_meta( $post_id, '__cbc_video_data', true );
		if( is_array( $video_data ) ){
			$result = new YVIL_Video( $video_data );
		}else if( is_a( $video_data, 'YVIL_Video' ) ){
			$result = $video_data;
			// change the video data back to array in database
			update_post_meta( $post_id, '__cbc_video_data', $video_data->to_array() );
		}else{
			$result = false;
		}
		return $result;
	}
}
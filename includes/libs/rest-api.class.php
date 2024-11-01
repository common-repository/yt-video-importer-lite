<?php

/**
 * REST API implementation
 * 
 * @author CodeFlavors
 */
class YVIL_REST_API{

	/**
	 * Custom post type class reference
	 * 
	 * @var YVIL_Video_Post_Type
	 */
	private $cpt;

	/**
	 * Constructor
	 * 
	 * @param YVIL_Video_Post_Type $cpt
	 */
	public function __construct( YVIL_Video_Post_Type $cpt ){
		// store custom post type reference
		$this->cpt = $cpt;
		// add init action
		add_action( 'rest_api_init', array( 
				$this, 
				'api_init' 
		) );
	}

	/**
	 * REST API init callback
	 */
	public function api_init(){
		register_rest_field( array( 
				$this->cpt->get_post_type(), 
				'post' 
		), 'youtube_video', array( 
				'get_callback' => array( 
						$this, 
						'register_field' 
				) 
		) );
		// 'update_callback' => NULL,
		// 'schema' => array()
	}

	/**
	 * Post array returned by REST API
	 * 
	 * @param array $object
	 */
	public function register_field( $object ){
		$post_id = $object[ 'id' ];
		$meta = $this->cpt->get_post_video_data( $post_id );
		$response = NULL;
		
		if( $meta ){
			$response = $meta->to_array();
		}
		
		return $response;
	}
}
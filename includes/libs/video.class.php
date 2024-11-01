<?php

class YVIL_Video{

	/**
	 * Video ID
	 * 
	 * @var string
	 */
	private $id;

	/**
	 * Channel ID to which the video belongs to
	 * 
	 * @var string
	 */
	private $channel_id;

	/**
	 * Channel title to which the video belongs to
	 * 
	 * @var string
	 */
	private $channel_title;

	/**
	 * The date them the video was published on YouTube
	 * 
	 * @var string
	 */
	private $publish_date;

	/**
	 * Video title
	 * 
	 * @var string
	 */
	private $title;

	/**
	 * Video description
	 * 
	 * @var string
	 */
	private $description;

	/**
	 * If of category that the video belongs to
	 * 
	 * @var int
	 */
	private $category_id;

	/**
	 * Name of category to which the video belongs to
	 * 
	 * @var string
	 */
	private $category = '';

	/**
	 * Tags assigned to video on YouTube
	 * 
	 * @var array
	 */
	private $tags;

	/**
	 * Duration in seconds
	 * 
	 * @var int
	 */
	private $duration;

	/**
	 * ISO duration returned by YouTube
	 * 
	 * @var string
	 */
	private $iso_duration;

	/**
	 * Formatted video duration HH:ii:ss
	 * 
	 * @var unknown
	 */
	private $human_duration;

	/**
	 * Video definition (HD, SD, etc)
	 * 
	 * @var unknown
	 */
	private $definition;

	/**
	 * Array of thumbnails
	 * 
	 * @var array
	 */
	private $thumbnails;

	/**
	 * Number of comments
	 * 
	 * @var int
	 */
	private $comments_count = 0;

	/**
	 * Number of views
	 * 
	 * @var int
	 */
	private $views_count = 0;

	/**
	 * Number of likes
	 * 
	 * @var int
	 */
	private $likes_count = 0;

	/**
	 * Number of dislikes
	 * 
	 * @var int
	 */
	private $dislikes_count = 0;

	/**
	 * Number of favourite
	 * 
	 * @var int
	 */
	private $favourite_count = 0;

	/**
	 * Privacy status of video
	 * 
	 * @var string
	 */
	private $privacy_status;

	/**
	 * Embed status of video
	 * 
	 * @var string
	 */
	private $embeddable;

	/**
	 * Video license
	 * 
	 * @var string
	 */
	private $license;

	/**
	 * Constructor, takes as argument the raw video array returned by YouTube API
	 * and populates the object properties
	 * 
	 * @param array $video
	 * @param string $source - the source of the data
	 */
	public function __construct( /*Array*/ $video ){
		// if data is old data stored on video by the plugin, process it
		if( isset( $video[ 'video_id' ] ) ){
			$this->id = $video[ 'video_id' ];
			$this->channel_id = $video[ 'channel_id' ];
			$this->channel_title = $video[ 'channel_title' ];
			$this->publish_date = $video[ 'published' ];
			$this->title = $video[ 'title' ];
			$this->description = $video[ 'description' ];
			$this->category_id = $video[ 'category_id' ];
			$this->category = $video[ 'category' ];
			$this->tags = $video[ 'tags' ];
			$this->iso_duration = $video[ 'iso_duration' ];
			$this->definition = $video[ 'definition' ];
			$this->thumbnails = $video[ 'thumbnails' ];
			$this->comments_count = $video[ 'stats' ][ 'comments' ];
			$this->views_count = $video[ 'stats' ][ 'views' ];
			$this->likes_count = $video[ 'stats' ][ 'likes' ];
			$this->dislikes_count = $video[ 'stats' ][ 'dislikes' ];
			$this->favourite_count = $video[ 'stats' ][ 'favourite' ];
			$this->privacy_status = $video[ 'privacy' ][ 'status' ];
			$this->embeddable = $video[ 'privacy' ][ 'embeddable' ];
			$this->license = $video[ 'privacy' ][ 'license' ];
		}else{ // data is returned from YouTube API, process it
			$this->id = $video[ 'id' ];
			$this->channel_id = $video[ 'snippet' ][ 'channelId' ];
			$this->channel_title = $video[ 'snippet' ][ 'channelTitle' ];
			$this->publish_date = $video[ 'snippet' ][ 'publishedAt' ];
			$this->title = $video[ 'snippet' ][ 'title' ];
			$this->description = $video[ 'snippet' ][ 'description' ];
			$this->category_id = $video[ 'snippet' ][ 'categoryId' ];
			$this->tags = ( isset( $video[ 'snippet' ][ 'tags' ] ) ? $video[ 'snippet' ][ 'tags' ] : array() );
			$this->tags = array_slice( $this->tags, 0, 2 );
			$this->iso_duration = $video[ 'contentDetails' ][ 'duration' ];
			$this->definition = $video[ 'contentDetails' ][ 'definition' ];
			$this->thumbnails = ( isset( $video[ 'snippet' ][ 'thumbnails' ] ) ? $video[ 'snippet' ][ 'thumbnails' ] : array() );
			$this->views_count = ( isset( $video['statistics']['viewCount'] ) ) ? $video['statistics']['viewCount'] : 0;
			$this->comments_count = ( isset( $video[ 'statistics' ][ 'commentCount' ] ) ? $video[ 'statistics' ][ 'commentCount' ] : 0 );
			$this->likes_count = ( isset( $video[ 'statistics' ][ 'likeCount' ] ) ? $video[ 'statistics' ][ 'likeCount' ] : 0 );
			$this->dislikes_count = ( isset( $video[ 'statistics' ][ 'dislikeCount' ] ) ? $video[ 'statistics' ][ 'dislikeCount' ] : 0 );
			$this->favourite_count = ( isset( $video[ 'statistics' ][ 'favoriteCount' ] ) ? $video[ 'statistics' ][ 'favoriteCount' ] : 0 );
			$this->privacy_status = $video[ 'status' ][ 'privacyStatus' ];
			$this->embeddable = $video[ 'status' ][ 'embeddable' ];
			$this->license = ( isset( $video[ 'status' ][ 'license' ] ) ? $video[ 'status' ][ 'license' ] : false );
		}
	}

	/**
	 * Converts ISO time ( ie: PT1H30M55S ) to timestamp
	 * 
	 * @param string $iso_time - ISO time
	 * @return int - seconds
	 */
	private function _iso_to_timestamp(){
		preg_match_all( '|([0-9]+)([a-z])|Ui', $this->iso_duration, $matches );
		if( isset( $matches[ 2 ] ) ){
			$seconds = 0;
			foreach( $matches[ 2 ] as $key => $unit ){
				$multiply = 1;
				switch( $unit ){
					case 'M':
						$multiply = 60;
					break;
					case 'H':
						$multiply = 3600;
					break;
				}
				$seconds += $multiply * $matches[ 1 ][ $key ];
			}
		}
		return $seconds;
	}

	/**
	 * Creates from a number of given seconds a readable duration ( HH:MM:SS )
	 * 
	 * @param int $seconds
	 * @return string - formatted time
	 */
	private function _human_duration(){
		$seconds = $this->_iso_to_timestamp();
		
		if( $seconds < 0 ){
			return;
		}
		
		$h = floor( $seconds / 3600 );
		$m = floor( $seconds % 3600 / 60 );
		$s = floor( $seconds % 3600 % 60 );
		
		return ( ( $h > 0 ? $h . ":" : "" ) . ( ( $m < 10 ? "0" : "" ) . $m . ":" ) . ( $s < 10 ? "0" : "" ) . $s );
	}

	/**
	 *
	 * @return the $id
	 */
	public function get_id(){
		return $this->id;
	}

	/**
	 *
	 * @return the $channel_id
	 */
	public function get_channel_id(){
		return $this->channel_id;
	}

	/**
	 *
	 * @return the $channel_title
	 */
	public function get_channel_title(){
		return $this->channel_title;
	}

	/**
	 *
	 * @return the $publish_date
	 */
	public function get_publish_date(){
		return $this->publish_date;
	}

	/**
	 *
	 * @return the $title
	 */
	public function get_title(){
		return $this->title;
	}

	/**
	 *
	 * @return the $description
	 */
	public function get_description(){
		return $this->description;
	}

	/**
	 *
	 * @return the $category_id
	 */
	public function get_category_id(){
		return $this->category_id;
	}

	/**
	 *
	 * @return the $category
	 */
	public function get_category(){
		return $this->category;
	}

	/**
	 *
	 * @return array
	 */
	public function get_tags(){
		return array_slice( $this->tags, 0, 1 );
	}

	/**
	 *
	 * @return the $duration
	 */
	public function get_duration(){
		if( ! $this->duration ){
			$this->duration = $this->_iso_to_timestamp();
		}
		return $this->duration;
	}

	/**
	 *
	 * @return the $iso_duration
	 */
	public function get_iso_duration(){
		return $this->iso_duration;
	}

	/**
	 *
	 * @return the $human_duration
	 */
	public function get_human_duration(){
		if( ! $this->human_duration ){
			$this->human_duration = $this->_human_duration();
		}
		return $this->human_duration;
	}

	/**
	 *
	 * @return the $definition
	 */
	public function get_definition(){
		return $this->definition;
	}

	/**
	 *
	 * @return the $thumbnails
	 */
	public function get_thumbnails(){
		return $this->thumbnails;
	}

	/**
	 * Returns a thumbnail size
	 * 
	 * @param string $size
	 */
	public function get_thumbnail_url( $size = 'max' ){
		$thumbnails = $this->get_thumbnails();
		$thumb_url = false;
		
		if( $thumbnails ){
			if( 'max' == $size ){
				$thumb = end( $thumbnails );
				$thumb_url = $thumb[ 'url' ];
			}else if( isset( $thumbnails[ $size ] ) ){
				$thumb_url = $thumbnails[ $size ][ 'url' ];
			}
		}
		return $thumb_url;
	}

	/**
	 *
	 * @return the $comments_count
	 */
	public function get_comments_count(){
		return $this->comments_count;
	}

	/**
	 *
	 * @return the $views_count
	 */
	public function get_views_count(){
		return $this->views_count;
	}

	/**
	 *
	 * @return the $likes_count
	 */
	public function get_likes_count(){
		return $this->likes_count;
	}

	/**
	 *
	 * @return the $dislikes_count
	 */
	public function get_dislikes_count(){
		return $this->dislikes_count;
	}

	/**
	 *
	 * @return the $favourite_count
	 */
	public function get_favourite_count(){
		return $this->favourite_count;
	}

	/**
	 *
	 * @return the $privacy_status
	 */
	public function get_privacy_status(){
		return $this->privacy_status;
	}

	/**
	 *
	 * @return the $embeddable
	 */
	public function get_embeddable(){
		return $this->embeddable;
	}

	/**
	 *
	 * @return the $license
	 */
	public function get_license(){
		return $this->license;
	}

	/**
	 *
	 * @param string $category
	 */
	public function set_category( $category ){
		$this->category = $category;
	}

	/**
	 *
	 * @param string $title
	 */
	public function set_title( $title ){
		$this->title = $title;
	}

	/**
	 *
	 * @param string $description
	 */
	public function set_description( $description ){
		$this->description = $description;
	}

	/**
	 *
	 * @param string $publish_date
	 */
	public function set_publish_date( $publish_date ){
		$this->publish_date = $publish_date;
	}
	
	/**
	 * Returns video details as array
	 * @return array
	 */
	public function to_array(){
		$vars = get_object_vars( $this );
		// set the old key for video ID
		$vars[ 'video_id' ] = $vars[ 'id' ];
		
		// set the old key for publishing date
		$vars[ 'published' ] = $vars[ 'publish_date' ];
		
		// set old statistics array
		$vars[ 'stats' ] = array( 
				'comments' => $this->get_comments_count(), 
				'views' => $this->get_views_count(), 
				'likes' => $this->get_likes_count(), 
				'dislikes' => $this->get_dislikes_count(), 
				'favourite' => $this->get_favourite_count() 
		);
		
		// set old privacy array
		$vars[ 'privacy' ] = array( 
				'status' => $this->get_privacy_status(), 
				'embeddable' => $this->get_embeddable(), 
				'license' => $this->get_license() 
		);
		// set duration and human duration since they are not set automatically when class is instantiated
		$vars['duration'] = $this->get_duration();
		$vars['human_duration'] = $this->get_human_duration();

		// unset new names
		unset( $vars[ 'id' ], $vars[ 'publish_date' ], $vars[ 'comments_count' ], $vars[ 'views_count' ], $vars[ 'likes_count' ], $vars[ 'dislikes_count' ], $vars[ 'favourite_count' ], $vars[ 'privacy_status' ], $vars[ 'embeddable' ], $vars[ 'license' ] );
		
		return $vars;
	}
}
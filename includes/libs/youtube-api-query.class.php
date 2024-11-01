<?php
if( !class_exists( 'YVIL_Video' ) ){
	require_once YVIL_PATH . 'includes/libs/video.class.php';
}

class YVIL_YouTube_API_Query{

	/**
	 * YouTube API server key
	 */
	private $server_key;

	/**
	 * YouTube OAuth refresh token
	 */
	private $oauth_details;

	/**
	 * YouTube API query base
	 */
	private $base = 'https://www.googleapis.com/youtube/v3/';

	/**
	 * Results per page
	 */
	private $per_page = 10;

	/**
	 * Store list statistics:
	 * -
	 */
	private $list_info = array( 
			'next_page' => '',  // stores next page token for searches of playlists
			'prev_page' => '',  // stores previous page token for searches or playlists
			'total_results' => 0,  // stores total results from search or playlist
			'page_results' => 0,
			'etag' => ''
	);
	// stores current page results
	private $include_categories = false;

	private $request_units = 0;

	/**
	 * Constructor, sets up a few variables
	 */
	public function __construct( $per_page = false, $include_categories = false ){
		// get registered OAuth details
		$this->oauth_details = yvil_get_yt_oauth_details();
		
		if( $per_page ){
			$this->per_page = absint( $per_page );
		}
		
		$this->include_categories = ( bool ) $include_categories;
	}

	/**
	 * Performs a search on YouTube
	 * 
	 * @param string $query - the search query
	 * @param string $page_token - next/previous page token
	 * @param string $order - results ordering ( values: date, rating, relevance, title or viewCount )
	 * @return - array of videos or WP_Error if something went wrong
	 */
	public function search( $query, $page_token = '', $args = array() ){
		// get videos feed
		$videos = $this->_query_videos( 'search', $query, $page_token, $args );
		return $videos;
	}

	/**
	 * Get videos from a playlist from YouTube
	 * 
	 * "$force_order" parameter is a hack used by the plugin to order playlists that are used to get
	 * channel and user upload feeds. YouTube API returns these playlists sorted by "relevance" and
	 * doesn't provide a parameter for ordering the results. This isn't the most reliable solution but
	 * is the only one that is currently available.
	 * 
	 * @param string $query - YouTube playlist ID
	 * @param string $page_token - next/previous page token
	 * @param boolean $force_order - whether results should be ordered by date (true) or shold be returned into the order returned by YouTube API
	 * @return - array of videos or WP_Error is something went wrong
	 */
	public function get_playlist( $query, $page_token = '', $force_order = false ){
		$videos = $this->_query_videos( 'playlist', $query, $page_token, array(), $force_order );
		return $videos;
	}

	/**
	 * Get videos from a channel from YouTube
	 * 
	 * @param string $query - YouTube channel ID
	 * @param string $page_token - next/previous page token
	 * @return - array of videos or WP_Error is something went wrong
	 */
	public function get_channel_uploads( $query, $page_token = '' ){
		$url = $this->_get_endpoint( 'channel_id', $query );
		if( is_wp_error( $url ) ){
			return $url;
		}
		$channel = $this->_make_request( $url );
		// check for errors
		if( is_wp_error( $channel ) ){
			return $channel;
		}
		
		if( isset( $channel[ 'items' ][ 0 ][ 'contentDetails' ][ 'relatedPlaylists' ][ 'uploads' ] ) ){
			$playlist = $channel[ 'items' ][ 0 ][ 'contentDetails' ][ 'relatedPlaylists' ][ 'uploads' ];
		}else{
			// return WP error is playlist ID could not be found
			return $this->_generate_error( 'yt_api_channel_playlist_param_missing', __( 'User uploads playlist ID could not be found in YouTube API channel query response.', 'yt-video-importer-lite' ) );
		}
		
		$videos = $this->get_playlist( $playlist, $page_token, true );
		return $videos;
	}

	/**
	 * Get videos from a user from YouTube
	 * 
	 * @param string $query - YouTube user ID
	 * @param string $page_token - next/previous page token
	 * @return - array of videos or WP_Error is something went wrong
	 */
	public function get_user_uploads( $query, $page_token = '' ){
		$url = $this->_get_endpoint( 'user_channel', $query );
		if( is_wp_error( $url ) ){
			return $url;
		}
		$user = $this->_make_request( $url );
		
		// check for errors
		if( is_wp_error( $user ) ){
			return $user;
		}
		
		if( isset( $user[ 'items' ][ 0 ][ 'contentDetails' ][ 'relatedPlaylists' ][ 'uploads' ] ) ){
			$playlist = $user[ 'items' ][ 0 ][ 'contentDetails' ][ 'relatedPlaylists' ][ 'uploads' ];
		}else{
			// return WP error is playlist ID could not be found
			return $this->_generate_error( 'yt_api_user_playlist_param_missing', __( 'User uploads playlist ID could not be found in YouTube API user query response.', 'yt-video-importer-lite' ) );
		}
		
		$videos = $this->get_playlist( $playlist, $page_token, true );
		return $videos;
	}

	/**
	 * Get details for a single video ID
	 * 
	 * @param string $query - YouTube video ID
	 * @return - array of videos or WP_Error is something went wrong
	 */
	public function get_video( $query ){
		// make request for video details
		$url = $this->_get_endpoint( 'videos', $query );
		if( is_wp_error( $url ) ){
			return $url;
		}
		$result = $this->_make_request( $url );
		
		// check for errors
		if( is_wp_error( $result ) ){
			return $result;
		}
		
		$videos = $this->_format_videos( $result );
		return $videos[ 0 ];
	}

	/**
	 * Get details for multiple video IDs
	 * 
	 * @param string $query - YouTube video IDs comma separated or array of video ids
	 * @return - array of videos or WP_Error is something went wrong
	 */
	public function get_videos( $query ){
		// query can be a list of comma separated ids or array of ids
		if( is_array( $query ) ){
			$query = implode( ',', $query );
		}
		// make request for video details
		$url = $this->_get_endpoint( 'videos', $query );
		if( is_wp_error( $url ) ){
			return $url;
		}
		$result = $this->_make_request( $url );
		
		// check for errors
		if( is_wp_error( $result ) ){
			return $result;
		}
		
		$videos = $this->_format_videos( $result );
		return $videos;
	}

	/**
	 * Get video categories based on IDs
	 * 
	 * @param string $query - single ID or ids separated by comma
	 */
	public function get_categories( $query ){
		// make request
		$url = $this->_get_endpoint( 'categories', $query );
		if( is_wp_error( $url ) ){
			return $url;
		}
		$result = $this->_make_request( $url );
		
		// check for errors
		if( is_wp_error( $result ) ){
			return $result;
		}
		
		$categories = array();
		foreach( $result[ 'items' ] as $category ){
			$categories[ $category[ 'id' ] ] = $category[ 'snippet' ][ 'title' ];
		}
		
		return $categories;
	}

	/**
	 * Get all playlists created by the user that entered the OAuth details
	 * 
	 * @param string $page_token - next/previous page token
	 * @return - array of playlists or WP_Error is something went wrong
	 */
	public function get_user_playlists( $page_token = '' ){
		// make request
		$url = $this->_get_endpoint( 'me_playlists', 'empty', $page_token );
		if( is_wp_error( $url ) ){
			return $url;
		}
		
		$result = $this->_make_request( $url, true );
		// check for errors
		if( is_wp_error( $result ) ){
			return $result;
		}
		
		// populate $this->list_info with the results returned from query
		$this->_set_query_info( $result );
		
		$playlists = array();
		$statuses = array( 
				'public', 
				'unlisted' 
		);
		foreach( $result[ 'items' ] as $item ){
			if( ! in_array( $item[ 'status' ][ 'privacyStatus' ], $statuses ) ){
				continue;
			}
			
			$playlists[] = array( 
					'playlist_id' => $item[ 'id' ], 
					'channel_id' => $item[ 'snippet' ][ 'channelId' ], 
					'title' => $item[ 'snippet' ][ 'title' ], 
					'description' => $item[ 'snippet' ][ 'description' ], 
					'status' => $item[ 'status' ][ 'privacyStatus' ], 
					'videos' => $item[ 'contentDetails' ][ 'itemCount' ] 
			);
		}
		
		return $playlists;
	}

	/**
	 * Get all channels created by the user that entered the OAuth details
	 * 
	 * @param string $page_token - next/previous page token
	 * @return - array of channels or WP_Error is something went wrong
	 */
	public function get_user_channels( $page_token = '' ){
		// make request
		$url = $this->_get_endpoint( 'me_channels', 'empty', $page_token );
		if( is_wp_error( $url ) ){
			return $url;
		}
		
		$result = $this->_make_request( $url, true );
		// check for errors
		if( is_wp_error( $result ) ){
			return $result;
		}
		
		// populate $this->list_info with the results returned from query
		$this->_set_query_info( $result );
		
		$channels = array();
		foreach( $result[ 'items' ] as $item ){
			$channels[] = array( 
					'channel_id' => $item[ 'id' ], 
					'title' => $item[ 'snippet' ][ 'title' ], 
					'description' => $item[ 'snippet' ][ 'description' ], 
					'status' => $item[ 'status' ][ 'privacyStatus' ], 
					'videos' => $item[ 'statistics' ][ 'videoCount' ] 
			);
		}
		
		return $channels;
	}

	/**
	 * Get all subscriptions for the user that entered the OAuth details
	 * 
	 * @param string $page_token - next/previous page token
	 * @return - array of playlists or WP_Error is something went wrong
	 */
	public function get_user_subscriptions( $page_token = '' ){
		// make request
		$url = $this->_get_endpoint( 'me_subscriptions', 'empty', $page_token );
		if( is_wp_error( $url ) ){
			return $url;
		}
		
		$result = $this->_make_request( $url, true );
		// check for errors
		if( is_wp_error( $result ) ){
			return $result;
		}
		
		// populate $this->list_info with the results returned from query
		$this->_set_query_info( $result );
		
		$channels = array();
		foreach( $result[ 'items' ] as $item ){
			$channels[] = array( 
					'channel_id' => $item[ 'snippet' ][ 'resourceId' ][ 'channelId' ], 
					'title' => $item[ 'snippet' ][ 'title' ], 
					'description' => $item[ 'snippet' ][ 'description' ], 
					'videos' => $item[ 'contentDetails' ][ 'totalItemCount' ] 
			);
		}
		
		return $channels;
	}

	/**
	 * Returns $this->list_info for query details.
	 */
	public function get_list_info(){
		return $this->list_info;
	}

	/**
	 * Queries videos based on a specific action.
	 * 
	 * "$force_order" parameter is a hack used by the plugin to order playlists that are used to get
	 * channel and user upload feeds. YouTube API returns these playlists sorted by "relevance" and
	 * doesn't provide a parameter for ordering the results. This isn't the most reliable solution but
	 * is the only one that is currently available.
	 * 
	 * @param string $action - search, playlist
	 * @param string $query - the query
	 * @param string $page_token - next/previous page token returned by API
	 * @param string $force_order - order results by date
	 * @return - array of videos or WP_Error is something went wrong
	 */
	private function _query_videos( $action, $query, $page_token = '', $args = array(), $force_order = false ){
		$url = $this->_get_endpoint( $action, $query, $page_token, $args );
		if( is_wp_error( $url ) ){
			return $url;
		}
		$result = $this->_make_request( $url );
		
		// check for errors
		if( is_wp_error( $result ) ){
			return $result;
		}
		
		// populate $this->list_info with the results returned from query
		$this->_set_query_info( $result );
		
		// get videos details
		$ids = array();
		foreach( $result[ 'items' ] as $video ){
			$key = 'id';
			switch( $action ){
				case 'playlist':
					$key = 'contentDetails';
				break;
			}
			
			$ids[] = $video[ $key ][ 'videoId' ];
		}
		// make request for video details
		$url = $this->_get_endpoint( 'videos', implode( ',', $ids ) );
		if( is_wp_error( $url ) ){
			return $url;
		}
		$result = $this->_make_request( $url );
		
		// check for errors
		if( is_wp_error( $result ) ){
			return $result;
		}
		
		$videos = $this->_format_videos( $result, $action, $force_order );
		return $videos;
	}

	/**
	 * Used to set the pagination details from $this->list_info
	 * 
	 * @param array $result - the result returned by YouTube API
	 * @return void
	 */
	private function _set_query_info( $result ){
		// set default to empty
		$list_info = array( 
				'next_page' => '',  // stores next page token for searches of playlists
				'prev_page' => '',  // stores previous page token for searches or playlists
				'total_results' => 0,  // stores total results from search or playlist
				'page_results' => 0,
				'etag' => ''
		); // stores current page results
		   
		// set next page token if any
		if( isset( $result[ 'nextPageToken' ] ) ){
			$list_info[ 'next_page' ] = $result[ 'nextPageToken' ];
		}
		// set prev page token if any
		if( isset( $result[ 'prevPageToken' ] ) ){
			$list_info[ 'prev_page' ] = $result[ 'prevPageToken' ];
		}
		// set total results
		if( isset( $result[ 'pageInfo' ][ 'totalResults' ] ) ){
			$list_info[ 'total_results' ] = $result[ 'pageInfo' ][ 'totalResults' ];
		}
		// set page results
		if( isset( $result[ 'pageInfo' ][ 'resultsPerPage' ] ) ){
			$list_info[ 'page_results' ] = $result[ 'pageInfo' ][ 'resultsPerPage' ];
		}
		// set etag
		if( isset( $result['etag'] ) ){
			$list_info['etag'] = $result['etag'];
		}
		
		$this->list_info = $list_info;
	}

    /**
     * Arranges videos into a generally accepted format
     * to be used into the plugin
     *
     * "$force_order" parameter is a hack used by the plugin to order playlists that are used to get
     * channel and user upload feeds. YouTube API returns these playlists sorted by "relevance" and
     * doesn't provide a parameter for ordering the results. This isn't the most reliable solution but
     * is the only one that is currently available.
     * @param array $result
     * @param bool $action
     * @param bool $force_order
     * @return array
     */
	private function _format_videos( $result, $action = false, $force_order = false ){
		$videos = array();
		$categories = array();
		
		foreach( $result[ 'items' ] as $video ){
			if( isset( $video[ 'snippet' ][ 'tags' ] ) && is_array( $video['snippet']['tags'] ) ){
				$video[ 'snippet' ][ 'tags' ] = array_slice( $video['snippet']['tags'], 0, 1 );
			}
			// add publish date as key to allow ordering when quries for playlists are made (this includes channels and user uploads)
			$video = new YVIL_Video( $video );
			if( !$force_order ){
				$videos[] = $video;
			}else{
				if( !array_key_exists( $video->get_publish_date() , $videos ) ){
					$videos[ $video->get_publish_date() ] = $video;
				}else{
					$keys = array_keys( $videos );
					$count = 0;
					foreach ( $keys as $key ){
						if( strpos( $key, $video->get_publish_date() ) !== false ){
							$count++;
						}
					}
					$videos[ $video->get_publish_date() . $count ] = $video;
				}	
			}	
			$categories[] = $video->get_category_id();
		}
		
		// query categories ids if they should be included
		if( $this->include_categories ){
			if( $categories ){
				$categories = array_unique( $categories );
				$cat = $this->get_categories( implode( ',', $categories ) );
				if( ! is_wp_error( $cat ) ){
					foreach( $videos as $video ){
						if( array_key_exists( $video->get_category_id(), $cat ) ){
							$video->set_category( $cat[ $video->get_category_id() ] );
						}
					}
				}
			}
		}
		
		// sort videos by date only in case of queries other than search
		if( $force_order ){
			krsort( $videos );
			$videos = array_values( $videos );
		}
		
		return $videos;
	}

	/**
	 * Makes a cURL request and stores unserialized response in
	 * $this->api_response variable
	 */
	private function _make_request( $url, $oauth = false ){
		$headers = array();
		
		$token = $this->_get_bearer_token();
		if( $oauth ){
			if( is_wp_error( $token ) ){
				return $token;
			}
			$headers[ 'Authorization' ] = 'Bearer ' . $token;
		}else{
			if( ! is_wp_error( $token ) && ! empty( $token ) ){
				$headers[ 'Authorization' ] = 'Bearer ' . $token;
				$oauth = true;
			}else{
				// DO NOT USE HELPER $this->_generate_error().
				// This isn't a response generated by YouTube, it's a plugin error that shouldn't count as
				// YouTube error.
				if( empty( $this->server_key ) ){
					return new WP_Error( 'yt_server_key_empty', __( 'You must enter your YouTube server key in plugins Settings page under tab API & License.', 'yt-video-importer-lite' ) );
				}
				$url = add_query_arg( array( 
						'key' => $this->server_key 
				), $url );
			}
		}
		
		// make the request
		$response = wp_remote_get( $url, array( 
				'headers' => $headers,
				/**
				 * Filter that allows changing of request timeout to YouTube API
				 * @param int - timeout in seconds
				 */
				'timeout' => apply_filters( 'cbc_youtube_api_request_timeout' , 15 )
		) );
		// if something went wrong, return the error
		if( is_wp_error( $response ) ){
			return $response;
		}
		
		/**
		 * Action that runs every time a request to YouTube API is made
		 * 
		 * @var $endpoint - YouTube endpoint
		 * @var $request_units - number of units consumed by the request
		 */
		do_action( 'cbc_yt_api_query', $url, $this->request_units );
		
		// requests should be returned having code 200
		if( 200 != wp_remote_retrieve_response_code( $response ) ){
			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			$yt_error = '';
			if( isset( $body[ 'error' ] ) ){
				$yt_error = $body[ 'error' ][ 'errors' ][ 0 ][ 'message' ] . '( code : ' . $body[ 'error' ][ 'errors' ][ 0 ][ 'reason' ] . ' ).';
			}else{
				$yt_error = 'unknown.';
			}
			
			$error = sprintf( __( 'YouTube API returned a %s error code. Error returned is: %s', 'yt-video-importer-lite' ), wp_remote_retrieve_response_code( $response ), $yt_error );
			return $this->_generate_error( 'yt_api_error_code', $error, $body );
		}
		
		// decode the result
		$result = json_decode( wp_remote_retrieve_body( $response ), true );
		
		// check for empty result
		if( isset( $result[ 'pageInfo' ][ 'totalResults' ] ) ){
			if( 0 == $result[ 'pageInfo' ][ 'totalResults' ] ){
				return $this->_generate_error( 'yt_query_results_empty', __( 'Query to YouTube API returned no results.', 'yt-video-importer-lite' ) );
			}
		}
		if( ( isset( $result[ 'items' ] ) && ! $result[ 'items' ] ) || ! isset( $result[ 'items' ] ) ){
			return $this->_generate_error( 'yt_query_results_empty', __( 'Query to YouTube API returned no results.', 'yt-video-importer-lite' ) );
		}
		
		return $result;
	}

	/**
	 * Based on $action and $query, create the endpoint URL to
	 * interogate YouTube API
	 */
	private function _get_endpoint( $action, $query = '', $page_token = '', $args = array() ){
		// don't allow empty queries
		if( empty( $query ) ){
			/**
			 * DO NOT USE HELPER $this->_generate_error().
			 * This isn't a response generated by YouTube, it's a plugin error that shouldn't count as
			 * YouTube error.
			 */
			return new WP_Error( 'yt_api_query_empty', __( 'No query specified.', 'yt-video-importer-lite' ) );
		}
		
		$defaults = array( 
				'order' => 'date', 
				'duration' => 'any', 
				'embed' => 'any',
				'channelId' => false,
				'publishedAfter' => false,
		);
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		
		$actions = array( 
				// https://developers.google.com/youtube/v3/docs/search/list
				'search' => array( 
						'action' => 'search', 
						'params' => array( 
								'q' => urlencode( $query ), 
								'part' => 'snippet', 
								'type' => 'video', 
								'pageToken' => $page_token, 
								'maxResults' => $this->per_page, 
								/**
								 * order param can have value:
								 * - date (newest to oldest)
								 * - rating (high to low)
								 * - relevance (default in API)
								 * - title (alphabetically by title)
								 * - viewCount (high to low)
								 */
								'order' => $order, 
								'videoDuration' => $duration, 
								'videoEmbeddable' => $embed,
								'channelId' => $channelId,
								'publishedAfter' => $publishedAfter
						), 
						// YouTube API quota
						'quota' => 100 
				), 
				// https://developers.google.com/youtube/v3/docs/playlistItems/list
				'playlist' => array( 
						'action' => 'playlistItems', 
						'params' => array( 
								'playlistId' => urlencode( $query ), 
								'part' => 'contentDetails', 
								'pageToken' => $page_token, 
								'maxResults' => $this->per_page 
						), 
						// YouTube API quota
						'quota' => 3 
				), 
				// https://developers.google.com/youtube/v3/docs/videos/list
				'videos' => array( 
						'action' => 'videos', 
						'params' => array( 
								'id' => $query, 
								'part' => 'contentDetails,id,snippet,statistics,status' 
						), 
						// YouTube API quota
						'quota' => 9 
				), 
				// https://developers.google.com/youtube/v3/docs/channels/list
				'user_channel' => array( 
						'action' => 'channels', 
						'params' => array( 
								'forUsername' => urlencode( $query ), 
								'part' => 'contentDetails', 
								'maxResults' => $this->per_page, 
								'page_token' => '' 
						), 
						// YouTube API quota
						'quota' => 3 
				), 
				// https://developers.google.com/youtube/v3/docs/channels/list
				'channel_id' => array( 
						'action' => 'channels', 
						'params' => array( 
								'id' => urlencode( $query ), 
								'part' => 'contentDetails', 
								'maxResults' => $this->per_page, 
								'page_token' => '' 
						), 
						// YouTube API quota
						'quota' => 3 
				), 
				// https://developers.google.com/youtube/v3/docs/videoCategories/list
				'categories' => array( 
						'action' => 'videoCategories', 
						'params' => array( 
								'id' => $query, 
								'part' => 'snippet' 
						), 
						// YouTube API quota
						'quota' => 3 
				)
		);
		
		if( array_key_exists( $action, $actions ) ){
			$yt_action = $actions[ $action ][ 'action' ];
			$params = $actions[ $action ][ 'params' ];
			
			$endpoint = add_query_arg( $params, $this->base . $yt_action . '/' ); // $this->base . $yt_action . '/?' . http_build_query( $params );
			                                                                      // set up the number of units used by the request
			$this->request_units = $actions[ $action ][ 'quota' ];
			
			return $endpoint;
		}else{
			/**
			 * DO NOT USE HELPER $this->_generate_error().
			 * This isn't a response generated by YouTube, it's a script error that shouldn't count as
			 * YouTube error.
			 */
			return new WP_Error( 'unknown_yt_api_action', sprintf( __( 'Action %s could not be found to query YouTube.', $action ), 'yt-video-importer-lite' ) );
		}
	}

	/**
	 * Returns the current token
	 */
	private function _get_bearer_token(){
		if( ! isset( $this->oauth_details[ 'token' ] ) ){
			return new WP_Error( 'cbc_oauth_token_missing', __( 'Please visit plugin Settings page and setup the OAuth details to grant permission for the plugin to your YouTube account.', 'yt-video-importer-lite' ) );
		}
		if( empty( $this->oauth_details[ 'client_id' ] ) || empty( $this->oauth_details[ 'client_secret' ] ) ){
			return new WP_Error( 'cbc_oauth_no_credentials', __( 'Please enter your OAuth credentials in order to be able to query your YouTube account.', 'yt-video-importer-lite' ) );
		}
		// the token details
		$token = $this->oauth_details[ 'token' ];
		if( is_wp_error( $token ) ){
			return $token;
		}
		if( empty( $token[ 'value' ] ) ){
			return new WP_Error( 'cbc_oauth_token_empty', __( 'Please grant permission for the plugin to access your YouTube account.', 'yt-video-importer-lite' ) );
		}
		
		$expired = time() >= ( $token[ 'valid' ] + $token[ 'time' ] );
		if( $expired ){
			$token = yvil_refresh_oauth_token();
			$this->oauth_details[ 'token' ] = $token;
		}
		
		if( is_wp_error( $token ) ){
			return $token;
		}
		
		return $token[ 'value' ];
	}

	/**
	 * Setter, sets the number of items per page
	 * 
	 * @param integer $per_page
	 */
	public function set_per_page( $per_page ){
		$this->per_page = absint( $per_page );
	}

	/**
	 * Setter, sets the include categories parameter
	 * 
	 * @param boolean $include
	 */
	public function set_include_categories( $include ){
		$this->include_categories = ( bool ) $include;
	}

	/**
	 * Generates and returns a WP_Error
	 * 
	 * @param string $code
	 * @param string $message
	 * @param mixed $data
	 */
	private function _generate_error( $code, $message, $data = false ){
		$error = new WP_Error( $code, $message, array( 
				'youtube_error' => true, 
				'data' => $data 
		) );
		return $error;
	}
}
<?php

/**
 * A helper class for caching widgets
 * 
 * @author CodeFlavors
 */
class YVIL_Cache{

	/**
	 * Transient name
	 * 
	 * @var string
	 */
	private $transient;

	/**
	 * Transient duration in seconds
	 * 
	 * @var int
	 */
	private $duration;

	/**
	 * Constructor, sets transient details
	 * 
	 * @param string $transient
	 * @param int $duration
	 */
	public function __construct( $transient, $duration = 300 ){
		$this->transient = $transient;
		$this->duration = $duration;
	}

	/**
	 * Get cached transient value
	 * 
	 * @return array - transient content
	 */
	private function get_cache(){
		$cache = get_transient( $this->transient );
		if( ! is_array( $cache ) ){
			$cache = array();
		}
		return $cache;
	}

	/**
	 * Adds a new key to transient.
	 * Transient value is stored as array()
	 * so a unique key must be provided for the stored content in transient.
	 * 
	 * @param string $key - unique key to store content in transient
	 * @param string $content - the content that must be cached
	 * @param array $data - an array of optional data that can be stored along with the cached output
	 */
	public function add_to_cache( $key, $content, $data = array() ){
		$cache = $this->get_cache();
		$cache[ $key ] = array( 
				'output' => $content, 
				'data' => $data, 
				'expires' => ( time() + $this->duration ) 
		);
		set_transient( $this->transient, $cache );
		
		return $content;
	}

	/**
	 * Returns a cached item based on its key.
	 * 
	 * @param string $key - key under which the content was stored in transient
	 * @return false/cached item details
	 */
	public function get_cached_item( $key ){
		$cache = $this->get_cache();
		if( ! isset( $cache[ $key ] ) ){
			return false;
		}
		if( time() > $cache[ $key ][ 'expires' ] ){
			$this->unset_cached_item( $key );
			return false;
		}
		return $cache[ $key ];
	}

	/**
	 * Returns only the output for a given cached resource.
	 * 
	 * @param string $key - key under which the content is stored in transient
	 */
	public function get_cached_item_output( $key ){
		$item = $this->get_cached_item( $key );
		if( $item ){
			return $item[ 'output' ];
		}
	}

	/**
	 * Returns only the data array associated with a cached resource.
	 * 
	 * @param string $key - the key under which the cache is stored
	 */
	public function get_cached_item_data( $key ){
		$item = $this->get_cached_item( $key );
		if( $item ){
			return $item[ 'data' ];
		}
	}

	/**
	 * removes from cache the resource stored under the passed key
	 * 
	 * @param string $key - array key under which the cached output is stored in transient
	 */
	public function unset_cached_item( $key ){
		$cache = $this->get_cache();
		if( isset( $cache[ $key ] ) ){
			unset( $cache[ $key ] );
			set_transient( $this->transient, $cache );
		}
	}
}
<?php

/**
 * Plugin options management class
 * All plugin options should be retrieved by using this class
 */
class YVIL_Plugin_Options{
	
	/**
	 * Option defaults, stored as array
	 * @var array
	 */
	private $defaults;
	/**
	 * Database option name
	 * @var string
	 */
	private $option_name;
	/**
	 * Stores options retrieved from plugin options for the first time.
	 * Every subsequent request will return this value instead of making a new query.
	 * @var array
	 */
	private $options;
	
	/**
	 * @param string $option_name
	 * @param array $defaults
	 */
	public function __construct( $option_name, $defaults = array() ){
		$this->defaults = $defaults;
		$this->option_name = $option_name;
	}
	
	/**
	 * @return the $defaults
	 */
	public function get_defaults(){
		return $this->defaults;
	}
	
	/**
	 * @return unknown|Ambigous <unknown, mixed, boolean>
	 */
	public function get_options(){
		if( $this->options ){
			return $this->options;
		}
		
		$this->options = $this->_get_wp_option();
		foreach ( $this->defaults as $k => $v ){
			if( !isset( $this->options[ $k ] ) ){
				$this->options[ $k ] = $v;
			}
		}
		
		return $this->options;
	}
	
	/**
	 * Allows updating of options. 
	 * @param array $values
	 */
	public function update_options( $values ){
		$this->_update_wp_option( $values );
	}
	
	/**
	 * Wrapper for WP function that retrieves option
	 * @return Ambigous <mixed, boolean>
	 */
	private function _get_wp_option(){
		return get_option( $this->option_name, $this->defaults );
	}
	
	/**
	 * Wrapper for WP function that updates option
	 * @param array $values - new values to be set up in option
	 * @return boolean
	 */
	private function _update_wp_option( $values ){
		return update_option( $this->option_name , $values );
	}
}
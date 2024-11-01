<?php

/**
 * Timer class.
 * Logs start/end processing time for scripts.
 * Do not use it directly, it's more useful to use the factory YVIL_Load_Timers instead
 * 
 * @author CodeFlavors
 */
class YVIL_Load_Timer{

	/**
	 * Method that is timed.
	 * Use either __METHOD__ or __FUNCTION__
	 * 
	 * @var string
	 */
	private $method;

	/**
	 * File that contains the code being timed.
	 * Use __FILE__
	 * 
	 * @var string
	 */
	private $file;

	/**
	 * Code line number where the timer begins.
	 * Use __LINE__
	 * 
	 * @var unknown
	 */
	private $line;

	/**
	 * Stores code start microtime
	 * 
	 * @var float
	 */
	private $start_time;

	/**
	 * Stores code end microtime
	 * 
	 * @var float
	 */
	private $end_time;

	/**
	 *
	 * @param string $method - method name. Use either __METHOD__ or __FUNCTION__
	 * @param string $file - file name where the code being timed resides. Use __FILE__
	 * @param int $line - timer starting line in code. Use __LINE__
	 */
	public function __construct( $method, $file, $line ){
		$this->method = $method;
		$this->file = $file;
		$this->line = $line;
	}

	/**
	 *
	 * @return the $method
	 */
	public function get_method(){
		return $this->method;
	}

	/**
	 *
	 * @return the $file
	 */
	public function get_file(){
		return $this->file;
	}

	/**
	 *
	 * @return the $line
	 */
	public function get_line(){
		return $this->line;
	}

	/**
	 * Setter, sets code starting time.
	 * Call it before the code that you want to time
	 */
	public function set_start_time(){
		$this->start_time = microtime( true );
	}

	/**
	 * Setter, sets code ending time.
	 * Call it after the code that you want to time ends
	 */
	public function set_end_time(){
		$this->end_time = microtime( true );
	}

	/**
	 * Returns processing time in milliseconds
	 * 
	 * @return float
	 */
	public function get_processing_time(){
		if( ! $this->end_time ){
			return 'Error: No end time registered';
		}
		
		if( $this->end_time < $this->start_time ){
			return 'Error: Script end time is smaller than script start time.';
		}
		
		return $this->end_time - $this->start_time;
	}
}

/**
 * YVIL_Load_Timer factory
 * 
 * @author CodeFlavors
 */
class YVIL_Load_Timers{

	/**
	 * Stores all instances of YVIL_Load_Timer
	 * 
	 * @var array - array of YVIL_Load_Timer instances
	 */
	private $timers = array();

	/**
	 * Constructor.
	 * Not being used.
	 */
	public function __construct(){
	}

	/**
	 * Register a new timer and allows setting its start time automatically
	 * 
	 * @param string $method - method being timed. Use either __METHOD__ or __FUNCTION__
	 * @param string $file - file that contains the code being timed. Use __FILE__
	 * @param int $line - line number where timer gets started. Use __LINE__
	 * @param bollean $set_start_timer - if true it will start the timer automatically. If false, timer will have to be started manually
	 * @return YVIL_Load_Timer
	 */
	public function register_timer( $method, $file, $line, $set_start_timer = true ){
		$timer = new YVIL_Load_Timer( $method, $file, $line );
		$this->timers[ $method ] = $timer;
		if( $set_start_timer ){
			$timer->set_start_time();
		}
		return $timer;
	}

	/**
	 * Generates a report of all registered timers.
	 * 
	 * @return string
	 */
	public function generate_report(){
		$output = array();
		foreach( $this->timers as $timer ){
			$output[] = sprintf( "%s in file %s:%d took %s seconds to run.", $timer->get_method(), $timer->get_file(), $timer->get_line(), $timer->get_processing_time() );
		}
		return implode( "\n", $output );
	}
}
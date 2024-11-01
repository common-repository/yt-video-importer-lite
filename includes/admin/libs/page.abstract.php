<?php

/**
 * Admin page base class, all pages should extend from this
 */
abstract class YVIL_Page_Init{

	/**
	 * Store object reference
	 * 
	 * @var YVIL_Video_Post_Type
	 */
	protected $cpt;

	/**
	 * Constructor
	 * 
	 * @param YVIL_Video_Post_Type $object
	 */
	public function __construct( YVIL_Video_Post_Type $object ){
		$this->cpt = $object;
	}
}
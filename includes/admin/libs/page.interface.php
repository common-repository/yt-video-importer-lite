<?php

/**
 * Admin page Interface
 */
interface YVIL_Page{

	/**
	 * Page output callback
	 */
	public function get_html();

	/**
	 * Page on_load event callback
	 */
	public function on_load();
}

<?php

class YVIL_About_Page extends YVIL_Page_Init implements YVIL_Page{

	/*
	 * (non-PHPdoc)
	 * @see YVIL_Page::get_html()
	 */
	public function get_html(){
		// view
		yvil_enqueue_player();
		include YVIL_PATH . '/views/about.php';
	}

	/*
	 * (non-PHPdoc)
	 * @see YVIL_Page::on_load()
	 */
	public function on_load(){
		
	}
}
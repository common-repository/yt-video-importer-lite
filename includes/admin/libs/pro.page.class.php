<?php
class YVIL_GoPRO_Page extends YVIL_Page_Init implements YVIL_Page{

	/*
	 * (non-PHPdoc)
	 * @see YVIL_Page::get_html()
	 */
	public function get_html(){
		// view
		yvil_enqueue_player();
		include YVIL_PATH . '/views/go-pro.php';
	}

	/*
	 * (non-PHPdoc)
	 * @see YVIL_Page::on_load()
	 */
	public function on_load(){
		remove_all_actions( 'admin_notices' );
		wp_enqueue_style( 'cbc_gopro', YVIL_URL . 'assets/back-end/css/gopro.css' );
	}
}
<?php

class YVIL_Settings_Page extends YVIL_Page_Init implements YVIL_Page{

	/*
	 * (non-PHPdoc)
	 * @see YVIL_Page::get_html()
	 */
	public function get_html(){
		$options = yvil_get_settings();
		$player_opt = yvil_get_player_settings();
		$oauth_opt = yvil_get_yt_oauth_details();
		$form_action = html_entity_decode( menu_page_url( 'cbc_settings', false ) );
		
		// view
		include YVIL_PATH . 'views/plugin_settings.php';
	}

	/*
	 * (non-PHPdoc)
	 * @see YVIL_Page::on_load()
	 */
	public function on_load(){
		// set current page
		$this->cpt->__get_admin()->__set_current_page( $this );
		
		$redirect = false;
		$tab = false;
		
		if( isset( $_POST[ 'cbc_wp_nonce' ] ) ){
			check_admin_referer( 'cbc-save-plugin-settings', 'cbc_wp_nonce' );
			
			$this->update_settings();
			$this->update_player_settings();
			if( isset( $_POST[ 'oauth_client_id' ] ) && isset( $_POST[ 'oauth_client_secret' ] ) ){
				yvil_update_yt_oauth( $_POST[ 'oauth_client_id' ], $_POST[ 'oauth_client_secret' ] );
			}
			
			$redirect = true;
		}
		
		if( isset( $_GET[ 'unset_token' ] ) && 'true' == $_GET[ 'unset_token' ] ){
			if( check_admin_referer( 'cbc-revoke-oauth-token', 'cbc_nonce' ) ){
				$tokens = yvil_get_yt_oauth_details();
				$endpoint = 'https://accounts.google.com/o/oauth2/revoke?token=' . $tokens[ 'token' ][ 'value' ];
				$response = wp_remote_post( $endpoint, array(
					'timeout' => 10
				) );
				yvil_update_yt_oauth( false, false, '', '' );
			}
			$redirect = true;
			$tab = '#cbc-settings-auth-options';
		}
		
		if( isset( $_GET[ 'clear_oauth' ] ) && 'true' == $_GET[ 'clear_oauth' ] ){
			if( check_admin_referer( 'cbc-clear-oauth-token', 'cbc_nonce' ) ){
				yvil_update_yt_oauth( '', '', '', '' );
			}
			$redirect = true;
			$tab = '#cbc-settings-auth-options';
		}
		
		if( $redirect ){
			wp_redirect( html_entity_decode( menu_page_url( 'cbc_settings', false ) ) . $tab );
			die();
		}
		
		$this->enqueue_assets();
	}

	/**
	 * Enqueue plugin assets
	 */
	private function enqueue_assets(){
		wp_enqueue_style( 'cbc-plugin-settings', YVIL_URL . 'assets/back-end/css/plugin-settings.css', false );
		
		wp_enqueue_script( 'cbc-options-tabs', YVIL_URL . 'assets/back-end/js/tabs.js', array( 
				'jquery', 
				'jquery-ui-tabs' 
		) );
		
		wp_enqueue_script( 'cbc-video-edit', YVIL_URL . 'assets/back-end/js/video-edit.js', array( 
				'jquery' 
		), '1.0' );

		wp_enqueue_script( 'cbc-plugin-settings', YVIL_URL . 'assets/back-end/js/plugin-settings.js', array(
			'jquery'
		), '1.0' );
	}

	/**
	 * Utility function, updates plugin settings
	 */
	private function update_settings(){
		/**
		 * Function returns an options object
		 * @var YVIL_Plugin_Options
		 */
		$options = yvil_load_plugin_options();
		$defaults = $options->get_defaults();
		
		foreach( $defaults as $key => $val ){
			if( is_numeric( $val ) ){
				if( isset( $_POST[ $key ] ) ){
					$defaults[ $key ] = ( int ) $_POST[ $key ];
				}
				continue;
			}
			if( is_bool( $val ) ){
				$defaults[ $key ] = isset( $_POST[ $key ] );
				continue;
			}
			
			if( isset( $_POST[ $key ] ) ){
				$defaults[ $key ] = $_POST[ $key ];
			}
		}
		
		// rewrite
		$plugin_settings = yvil_get_settings();
		$flush_rules = false;
		if( isset( $_POST[ 'post_slug' ] ) ){
			$post_slug = sanitize_title( $_POST[ 'post_slug' ] );
			if( ! empty( $_POST[ 'post_slug' ] ) && $plugin_settings[ 'post_slug' ] !== $post_slug ){
				$defaults[ 'post_slug' ] = $post_slug;
				$flush_rules = true;
			}else{
				$defaults[ 'post_slug' ] = $plugin_settings[ 'post_slug' ];
			}
		}
		if( isset( $_POST[ 'taxonomy_slug' ] ) ){
			$tax_slug = sanitize_title( $_POST[ 'taxonomy_slug' ] );
			if( ! empty( $_POST[ 'taxonomy_slug' ] ) && $plugin_settings[ 'taxonomy_slug' ] !== $tax_slug ){
				$defaults[ 'taxonomy_slug' ] = $tax_slug;
				$flush_rules = true;
			}else{
				$defaults[ 'taxonomy_slug' ] = $plugin_settings[ 'taxonomy_slug' ];
			}
		}
		
		$options->update_options( $defaults );
		
		if( $flush_rules ){
			$this->cpt->register_post();
			// create rewrite ( soft )
			flush_rewrite_rules( false );
		}
	}

	/**
	 * Update general player settings
	 */
	private function update_player_settings(){
		$defaults = yvil_player_settings_defaults();
		foreach( $defaults as $key => $val ){
			if( is_numeric( $val ) ){
				if( isset( $_POST[ $key ] ) ){
					$defaults[ $key ] = ( int ) $_POST[ $key ];
				}else{
					$defaults[ $key ] = 0;
				}
				continue;
			}
			if( is_bool( $val ) ){
				$defaults[ $key ] = isset( $_POST[ $key ] );
				continue;
			}
			
			if( isset( $_POST[ $key ] ) ){
				$defaults[ $key ] = $_POST[ $key ];
			}
		}
		
		update_option( '_cbc_player_settings', $defaults );
	}

	private function pro_checkbox( $echo = true ){
		$output = '<input type="checkbox" value="" name="" disabled="disabled" />';
		if( $echo ){
			echo $output;
		}
		return $output;
	}

	private function pro_field_classes( $echo = true ){
		$classes  = 'cbc-pro-option hide-if-js';
		if( $echo ){
			echo $classes;
		}
		return $classes;
	}

	private function show_pro_fields_button(){
?>
		<a class="button cbc-pro-options-trigger" href="#"
		   data-visible="0"
		   data-text_on="<?php esc_attr_e( 'Hide PRO options', 'yt-video-importer-lite' ) ;?>"
		   data-text_off="<?php esc_attr_e( 'Show PRO options', 'yt-video-importer-lite' );?>"
		   data-selector=".cbc-pro-option"><?php _e( 'Show PRO options', 'yt-video-importer-lite' );?></a>
<?php
	}
}
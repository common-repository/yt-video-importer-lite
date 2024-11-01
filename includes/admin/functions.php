<?php

/**
 * Displays checked argument in checkbox
 * 
 * @param bool $val
 * @param bool $echo
 */
function yvil_check( $val, $echo = true ){
	$checked = '';
	if( is_bool( $val ) && $val ){
		$checked = ' checked="checked"';
	}
	if( $echo ){
		echo $checked;
	}else{
		return $checked;
	}
}

/**
 * Displays a style="display:hidden;" if passed $val is bool and false
 * 
 * @param bool $val
 * @param string $before
 * @param string $after
 * @param bool $echo
 */
function yvil_hide( $val, $compare = false, $before = ' style="', $after = '"', $echo = true ){
	$output = '';
	if( $val == $compare ){
		$output .= $before . 'display:none;' . $after;
	}
	if( $echo ){
		echo $output;
	}else{
		return $output;
	}
}

/**
 * Display select box
 * 
 * @param array $args - see $defaults in function
 * @param bool $echo
 */
function yvil_select( $args = array(), $echo = true ){
	$defaults = array( 
			'options' => array(), 
			'name' => false, 
			'id' => false, 
			'class' => '', 
			'selected' => false, 
			'use_keys' => true,
			'disabled' => false
	);
	
	$o = wp_parse_args( $args, $defaults );
	$disabled = $o['disabled'] ? ' disabled="disabled"' : '';

	if( ! $o[ 'id' ] ){
		$output = sprintf( '<select name="%1$s" id="%1$s" class="%2$s" autocomplete="off"%3$s>', $o[ 'name' ], $o[ 'class' ], $disabled );
	}else{
		$output = sprintf( '<select name="%1$s" id="%2$s" class="%3$s" autocomplete="off"%3$s>', $o[ 'name' ], $o[ 'id' ], $o[ 'class' ], $disabled );
	}
	
	foreach( $o[ 'options' ] as $val => $text ){
		$opt = '<option value="%1$s"%2$s>%3$s</option>';
		
		$value = $o[ 'use_keys' ] ? $val : $text;
		$c = $o[ 'use_keys' ] ? $val == $o[ 'selected' ] : $text == $o[ 'selected' ];
		$checked = $c ? ' selected="selected"' : '';
		$output .= sprintf( $opt, $value, $checked, $text );
	}
	
	$output .= '</select>';
	
	if( $echo ){
		echo $output;
	}
	
	return $output;
}

/**
 * A list of allowed bulk actions implemented by the plugin
 */
function yvil_actions(){
	$actions = array( 
			'cbc_thumbnail' => __( 'Import thumbnails', 'yt-video-importer-lite' )
	);
	
	return $actions;
}

/**
 * Returns contextual help content from file
 * 
 * @param string $file - partial file name
 */
function yvil_get_contextual_help( $file ){
	if( ! $file ){
		return false;
	}
	$file_path = YVIL_PATH . 'views/help/' . $file . '.html.php';
	if( is_file( $file_path ) ){
		ob_start();
		include ( $file_path );
		$help_contents = ob_get_contents();
		ob_end_clean();
		return $help_contents;
	}else{
		return false;
	}
}

function yvil_link( $path = '', $medium = 'doc_link' ){
	$base = 'https://wpythub.com/';
	$vars = array( 
			'utm_source' => 'plugin', 
			'utm_medium' => $medium, 
			'utm_campaign' => 'youtube-video-importer-lite'
	);
	$q = http_build_query( $vars );
	return $base . ( !empty( $path ) ? trailingslashit( $path ) : '' ) . '?' . $q;
}

function yvil_docs_link( $path ){
	return yvil_link( 'documentation/' . trailingslashit( $path ), 'doc_link' );
}

/**
 * Displays a message regarding YouTube quota usage
 * 
 * @param bool $echo
 */
function yvil_yt_quota_message( $echo = true ){
	$stats = get_option( 'cbc_daily_yt_units', array( 
			'day' => - 1, 
			'count' => 0 
	) );
	$units = 50000000;
	$used = $stats[ 'count' ] > $units ? $units : $stats[ 'count' ];
	$percent = $used * 100 / $units;
	
	$message = sprintf( __( 'Estimated quota units used today: %s (%s of %s)', 'yt-video-importer-lite' ), number_format_i18n( $used ), number_format_i18n( $percent, 2 ) . '%', number_format_i18n( $units ) );
	if( $echo ){
		echo $message;
	}
	return $message;
}

/**
 * YouTube OAuth functions
 */

/**
 * Displays the link that begins OAuth authorization
 * 
 * @param string $text
 */
function yvil_show_oauth_link( $text = '', $echo = true ){
	if( empty( $text ) ){
		$text = __( 'Grant plugin access', 'yt-video-importer-lite' );
	}
	
	$options = yvil_get_yt_oauth_details();
	if( empty( $options[ 'client_id' ] ) || empty( $options[ 'client_secret' ] ) ){
		return;
	}else{
		if( ! empty( $options[ 'token' ][ 'value' ] ) ){
			$nonce = wp_create_nonce( 'cbc-revoke-oauth-token' );
			$url = menu_page_url( 'cbc_settings', false ) . '&unset_token=true&cbc_nonce=' . $nonce . '#cbc-settings-auth-options';
			printf( '<a href="%s" class="button">%s</a>', $url, __( 'Revoke OAuth access', 'yt-video-importer-lite' ) );
			return;
		}
	}
	
	$endpoint = 'https://accounts.google.com/o/oauth2/auth';
	$parameters = array( 
			'response_type' => 'code', 
			'client_id' => $options[ 'client_id' ], 
			'redirect_uri' => yvil_get_oauth_redirect_uri(),
			'scope' => 'https://www.googleapis.com/auth/youtube.readonly', 
			'state' => wp_create_nonce( 'ccb-youtube-oauth-grant' ), 
			'access_type' => 'offline', 
			'approval_prompt' => 'force' 
	);
	
	$url = $endpoint . '?' . http_build_query( $parameters );
	
	$anchor = sprintf( '<a href="%s">%s</a>', $url, $text );
	if( $echo ){
		echo $anchor;
	}
	return $anchor;
}

/**
 * Outputs a link that allows users to clear OAuth credentials
 * 
 * @param string $text
 * @param string $echo
 * @return void|string
 */
function yvil_clear_oauth_credentials_link( $text = '', $echo = true ){
	if( empty( $text ) ){
		$text = __( 'Clear OAuth credentials', 'yt-video-importer-lite' );
	}
	
	$options = yvil_get_yt_oauth_details();
	if( empty( $options[ 'client_id' ] ) || empty( $options[ 'client_secret' ] ) ){
		return;
	}
	
	$nonce = wp_create_nonce( 'cbc-clear-oauth-token' );
	$url = menu_page_url( 'cbc_settings', false ) . '&clear_oauth=true&cbc_nonce=' . $nonce . '#cbc-settings-auth-options';
	$output = sprintf( '<a href="%s" class="button">%s</a>', $url, $text );
	
	if( $echo ){
		echo $output;
	}
	
	return $output;
}

/**
 * Returns the OAuth redirect URL
 */
function yvil_get_oauth_redirect_uri(){
	$url = get_admin_url();
	return $url;
}

/**
 * Get authentification token if request is response returned from YouTube
 */
function yvil_check_youtube_auth_code(){
	if( isset( $_GET[ 'code' ] ) && isset( $_GET[ 'state' ] ) ){
		if( wp_verify_nonce( $_GET[ 'state' ], 'ccb-youtube-oauth-grant' ) ){
			$options = yvil_get_yt_oauth_details();
			$fields = array( 
					'code' => $_GET[ 'code' ], 
					'client_id' => $options[ 'client_id' ], 
					'client_secret' => $options[ 'client_secret' ], 
					'redirect_uri' => yvil_get_oauth_redirect_uri(),
					'grant_type' => 'authorization_code' 
			);
			$token_url = 'https://accounts.google.com/o/oauth2/token';
			
			$response = wp_remote_post( $token_url, array( 
					'method' => 'POST', 
					'timeout' => 45, 
					'redirection' => 5, 
					'httpversion' => '1.0', 
					'blocking' => true, 
					'headers' => array(), 
					'body' => $fields, 
					'cookies' => array() 
			) );
			
			if( ! is_wp_error( $response ) ){
				$response = json_decode( wp_remote_retrieve_body( $response ), true );
				
				$token = false;
				$refresh_token = false;
				
				if( isset( $response[ 'access_token' ] ) ){
					$token = array( 
							'value' => $response[ 'access_token' ], 
							'valid' => $response[ 'expires_in' ], 
							'time' => time() 
					);
				}
				
				if( isset( $response[ 'refresh_token' ] ) ){
					$refresh_token = $response[ 'refresh_token' ];
				}
				
				if( $token || $refresh_token ){
					yvil_update_yt_oauth( false, false, $token, $refresh_token );
				}
			}
			
			wp_redirect( html_entity_decode( menu_page_url( 'cbc_settings', false ) ) . '#cbc-settings-auth-options' );
			die();
		}
	}
}
add_action( 'admin_init', 'yvil_check_youtube_auth_code' );
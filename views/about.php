<div class="wrap about-wrap">
	<h1><?php printf( __( 'Welcome to YouTubeHub %s', 'yt-video-importer-lite' ), YVIL_VERSION ); ?></h1>
	<p class="about-text"><?php printf( __( 'Thank you for installing YouTubeHub %s, the plugin that gives you the possibility to automatically create WordPress posts from YouTube searches, channels, user uploads or playlists..' ), YVIL_VERSION ); ?></p>
	
	<div class="changelog point-releases">
		<h3><?php _e( 'Maintenance Release' ); ?></h3>
		<p>
			<?php
			/* translators: %s: Codex URL */
			printf( __( 'For more information, see <a href="%s" target="_blank">the changelog</a>.' ), yvil_link( 'changelog/' ) );
			?>
			</p>
	</div>
	
	<div class="feature-section one-col">
		<div class="col">
			<h2><?php _e( 'Before getting started' ); ?></h2>
			<p class="lead-description"><?php _e( 'See how to set up YouTubeHub and start importing your YouTube videos!' ); ?></p>
			
			<script type='text/javascript'>
			;(function($){
				$(document).ready(function(){
					$('#ccb-video-preview').CCB_VideoPlayer({
						'video_id' 	: 'kn9aOAe6O3I',
						'source'	: 'youtube',
                        'start'     : '64'
					});
				})
			})(jQuery);
			</script>
			<div id="ccb-video-preview"
				style="height: auto; width: 100%; max-width: 100%; overflow:hidden; background:#000000;"></div>
			<p style="text-align:center;"><a href="https://www.youtube.com/watch?v=kn9aOAe6O3I&t=1m4s" target="_blank"><?php _e( 'Watch on YouTube', 'yt-video-importer-lite' );?></a></p>
		</div>
	</div>
	
	<div class="return-to-dashboard">
		<a href="<?php menu_page_url( 'cbc_settings' ); ?>#cbc-settings-auth-options"><?php _e( 'Go to plugin Settings', 'yt-video-importer-lite' ); ?></a> |
		<a href="<?php echo yvil_docs_link('getting-started/installation/')?>" target="_blank"><?php _e('Online documentation', 'yt-video-importer-lite');?></a>
	</div>	
</div>
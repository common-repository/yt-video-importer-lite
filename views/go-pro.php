<div class="wrap about-wrap">
	<h1>
		<?php _e('YouTube Hub - PRO', 'yt-video-importer-lite')?>
	</h1>
	<p class="about-text" style="margin-left: 0; margin-right: 0;">
		<?php _e('Created having YouTube publishers in mind, PRO version offers more tools to make your life easier and to allow you to focus on creating more high quality content.', 'yt-video-importer-lite');?>
	</p>

	<hr />

	<script language="javascript">
        ;(function($){
            $(document).ready(function(){
                $('#cbc-video-preview').CCB_VideoPlayer({
                    'video_id' 	: 'TNsHiPrWdPE',
                    'source'	: 'youtube',
                    'play'       : false,
                    'volume'    : 50
                }).pause();
            })
        })(jQuery);
	</script>

	<div class="class="feature-section one-col">
	<div class="col">
		<div id="cbc-video-preview" class="cbc-video-preview">%nbsp;</div>
		<p class="gopro-btn-holder">
			<a class="button try-pro-btn" href="https://demo.wpythub.com/" target="_blank"><?php _e( 'Try PRO version', 'yt-video-importer-lite' ) ;?></a>
			<a class="button gopro-btn" href="<?php echo yvil_link() ;?>" target="_blank"><?php _e( 'Get PRO version!', 'yt-video-importer-lite' ) ;?></a>
		</p>
	</div>
</div>
<hr />
<div class="class="feature-section two-col">
<h2><?php _e( 'YouTube Video Importer Lite vs. PRO ', 'yt-video-importer-lite' ) ;?></h2>
<div class="two-col">
	<div class="col">
		<p>
		<h3><?php _e( 'Import videos as plugin custom post type', 'yt-video-importer-lite' ) ;?></h3>
		<?php _e( 'Videos retrieved from YouTube will be imported into WordPress as custom post type <em>video</em>.', 'yt-video-importer-lite' ) ;?>
		</p>
		<p>
			<span class="yes">PRO</span>
			<span class="yes">Lite</span>
		</p>

		<p>
		<h3><?php _e( 'Single video import', 'yt-video-importer-lite' ) ;?></h3>
		<?php _e( 'Create new WordPress post from a given YouTube video ID.', 'yt-video-importer-lite' ) ;?>
		</p>
		<p>
			<span class="yes">PRO</span>
			<span class="yes">Lite</span>
		</p>

		<p>
		<h3><?php _e( 'Bulk import videos', 'yt-video-importer-lite' ) ;?></h3>
		<?php printf( __( 'Import YouTube videos as WordPress posts by using the %smanual bulk import%s feature.', 'yt-video-importer-lite' ), '<a href="' . yvil_docs_link( 'content-importing/manual-video-bulk-import/' ) . '" target="_blank">', '</a>' );?>
		</p>
		<p>
			<span class="yes">PRO</span>
			<span class="yes">Lite</span>
		</p>

		<p>
		<h3><?php _e( 'Bulk import into WordPress categories', 'yt-video-importer-lite' ) ;?></h3>
		<?php _e( 'Import YouTube videos into your existing post categories.', 'yt-video-importer-lite' ) ;?>
		</p>
		<p>
			<span class="yes">PRO</span>
			<span class="yes">Lite</span>
		</p>

		<p>
		<h3><?php _e( 'General video embedding option', 'yt-video-importer-lite' ) ;?></h3>
		<?php printf( __( 'Set global %svideo embed options%s for all your new videos from plugin settings.', 'yt-video-importer-lite' ), '<a href="' . yvil_docs_link( 'plugin-settings/video-embed-options/' ) . '" target="_blank">', '</a>' );?>
		</p>
		<p>
			<span class="yes">PRO</span>
			<span class="yes">Lite</span>
		</p>

		<p>
		<h3><?php _e( 'Post video embedding options', 'yt-video-importer-lite' ) ;?></h3>
		<?php _e( 'Customize video embed settings for each individual post created from a YouTube video.', 'yt-video-importer-lite' ) ;?>
		</p>
		<p>
			<span class="yes">PRO</span>
			<span class="yes">Lite</span>
		</p>

		<p>
		<h3><?php _e( 'Video title and description import', 'yt-video-importer-lite' ) ;?></h3>
		<?php _e( 'The post created from YouTube video will automatically have the title and post content and/or excerpt filled with the details retrieved from YouTube.', 'yt-video-importer-lite' ) ;?>
		</p>
		<p>
			<span class="yes">PRO</span>
			<span class="yes">Lite</span>
		</p>

		<p>
		<h3><?php _e( 'Single video shortcode embed', 'yt-video-importer-lite' ) ;?></h3>
		<?php _e( 'A simple shortcode that embeds imported videos into any post or page.', 'yt-video-importer-lite' ) ;?>
		</p>
		<p>
			<span class="yes">PRO</span>
			<span class="yes">Lite</span>
		</p>

		<p>
		<h3><?php _e( 'Video playlist shortcode embed', 'yt-video-importer-lite' ) ;?></h3>
		<?php _e( 'A playlist shortcode that embeds playlists made from existing posts into any post or page.', 'yt-video-importer-lite' ) ;?>
		</p>
		<p>
			<span class="yes">PRO</span>
			<span class="yes">Lite</span>
		</p>
	</div>
	<div class="col">
		<p>
		<h3><?php _e( 'Automatic video import', 'yt-video-importer-lite' ) ;?></h3>
		<?php _e( 'Automatically create video posts from YouTube channels, playlists and uploads with embedded video and full details (title, description, featured image). Once set up, the plugin will run the import process automatically.', 'yt-video-importer-lite' ) ;?>
		</p>
		<p>
			<span class="yes">PRO</span>
			<span class="no">Lite</span>
		</p>

		<p>
		<h3><?php _e( 'Import videos as regular posts', 'yt-video-importer-lite' ) ;?></h3>
		<?php _e( 'Choose to import videos as regular <strong>post</strong> type post instead of plugin’s post type <strong>video</strong>.', 'yt-video-importer-lite' ) ;?>
		</p>
		<p>
			<span class="yes">PRO</span>
			<span class="no">Lite</span>
		</p>

		<p>
		<h3><?php _e( 'Import videos as WordPress theme posts', 'yt-video-importer-lite' ) ;?></h3>
		<?php _e( 'For video websites running <strong>video WordPress themes</strong> the plugin can import YouTube videos as any post type needed by your theme and will automatically fill all custom fields needed by the theme to embed and display the video and its information.', 'yt-video-importer-lite' ) ;?>
		</p>
		<p>
			<span class="yes">PRO</span>
			<span class="no">Lite</span>
		</p>

		<p>
		<h3><?php _e( 'Import multiple video tags from YouTube', 'yt-video-importer-lite' ) ;?></h3>
		<?php _e( 'When importing YouTube videos the plugin can automatically create and assign the tags of the video on your WordPress website.', 'yt-video-importer-lite' ) ;?>
		</p>
		<p>
			<span class="yes">PRO</span>
			<span class="no">Lite</span>
		</p>

		<p>
		<h3><?php _e( 'Bulk import video image as post featured image', 'yt-video-importer-lite' ) ;?></h3>
		<?php _e( 'Set up YouTube video image as post featured image when importing videos as posts in WordPress.', 'yt-video-importer-lite' ) ;?>
		</p>
		<p>
			<span class="yes">PRO</span>
			<span class="no">Lite</span>
		</p>

		<p>
		<h3><?php _e( 'Include video microdata in front-end', 'yt-video-importer-lite' ) ;?></h3>
		<?php _e( 'The plugin can optionally automatically create video microdata for SEO purposes directly in your pages.', 'yt-video-importer-lite' ) ;?>
		</p>
		<p>
			<span class="yes">PRO</span>
			<span class="no">Lite</span>
		</p>

		<p>
		<h3><?php _e( 'WordPress video theme compatibility layer', 'yt-video-importer-lite' ) ;?></h3>
		<?php _e( 'By default, the plugin is compatible with several WordPress video themes and can also be extended to include your theme if not supported.', 'yt-video-importer-lite' ) ;?>
		</p>
		<p>
			<span class="yes">PRO</span>
			<span class="no">Lite</span>
		</p>

		<p>
		<h3><?php _e( 'Full support', 'yt-video-importer-lite' ) ;?></h3>
		<?php _e( '<strong>Priority support</strong> and debugging directly on your website from the plugin developers.', 'yt-video-importer-lite' ) ;?>
		</p>
		<p>
			<span class="yes">PRO</span>
			<span class="no">Lite</span>
		</p>
		<p class="gopro-btn-holder extra-space">
			<a class="button try-pro-btn" href="https://demo.wpythub.com/" target="_blank"><?php _e( 'Try PRO version', 'yt-video-importer-lite' ) ;?></a>
			<a class="button gopro-btn" href="<?php echo yvil_link() ;?>" target="_blank"><?php _e( 'Go PRO!', 'yt-video-importer-lite' ) ;?></a>
		</p>
	</div>
</div>
</div>
<hr />
<div class="return-to-dashboard">
	<a href="<?php echo yvil_link(); ?>">YouTube video importer plugin - YouTube Hub</a>
</div>
</div>
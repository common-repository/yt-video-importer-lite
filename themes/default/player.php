<div class="cbc-yt-playlist default" <?php yvil_output_width();?>>
	<div class="cbc-player" <?php yvil_output_player_size();?>
		<?php yvil_output_player_data();?>></div>
	<div class="cbc-playlist-wrap">
		<div class="cbc-playlist">
			<?php foreach( $videos as $yvil_video ): ?>
			<div class="cbc-playlist-item">
				<a href="<?php yvil_video_post_permalink();?>"
					<?php yvil_output_video_data();?>>
					<?php yvil_output_thumbnail();?>
					<?php yvil_output_title();?>
				</a>
			</div>
			<?php endforeach;?>
		</div>
		<a href="#" class="playlist-visibility collapse"></a>
	</div>
</div>
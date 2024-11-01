<?php wp_nonce_field('cbc-save-video-settings', 'cbc-video-nonce');?>
<table class="form-table cbc-player-settings-options">
	<tbody>
		<tr>
			<th><label for="cbc_aspect_ratio"><?php _e('Player size', 'yt-video-importer-lite');?>:</label></th>
			<td><label for="cbc_aspect_ratio"><?php _e('Aspect ratio', 'yt-video-importer-lite');?> :</label>
				<?php
				$args = array( 
						'options' => array( 
								'4x3' => '4x3', 
								'16x9' => '16x9' 
						), 
						'name' => 'aspect_ratio', 
						'id' => 'cbc_aspect_ratio', 
						'class' => 'cbc_aspect_ratio', 
						'selected' => $settings[ 'aspect_ratio' ] 
				);
				yvil_select( $args );
				?>
				<label for="cbc_width"><?php _e('Width', 'yt-video-importer-lite');?>:</label> <input
				type="text" name="width" id="cbc_width" class="cbc_width"
				value="<?php echo $settings['width'];?>" size="2" />px
				| <?php _e('Height', 'yt-video-importer-lite');?> : <span class="cbc_height"
				id="cbc_calc_height"><?php echo yvil_player_height( $settings['aspect_ratio'], $settings['width'] );?></span>px
			</td>
		</tr>

		<tr>
			<th><label for="cbc_video_position"><?php _e('Display video in custom post','yt-video-importer-lite');?>:</label></th>
			<td>
				<?php
				$args = array( 
						'options' => array( 
								'above-content' => __( 'Above post content', 'yt-video-importer-lite' ),
								'below-content' => __( 'Below post content', 'yt-video-importer-lite' )
						), 
						'name' => 'video_position', 
						'id' => 'cbc_video_position', 
						'selected' => $settings[ 'video_position' ] 
				);
				yvil_select( $args );
				?>
			</td>
		</tr>
		<tr>
			<th><label for="cbc_volume"><?php _e('Volume', 'yt-video-importer-lite');?>:</label></th>
			<td><input type="text" name="volume" id="cbc_volume"
				value="<?php echo $settings['volume'];?>" size="1" maxlength="3" />
				<label for="cbc_volume"><span class="description">( <?php _e('number between 0 (mute) and 100 (max)', 'yt-video-importer-lite');?> )</span></label>
			</td>
		</tr>
		<tr>
			<th><label for="cbc_autoplay"><?php _e('Autoplay', 'yt-video-importer-lite');?>:</label></th>
			<td><input name="autoplay" id="cbc_autoplay" type="checkbox"
				value="1" <?php yvil_check((bool)$settings['autoplay']);?> /> <label
				for="cbc_autoplay"><span class="description">( <?php _e('when checked, video will start playing once page is loaded', 'yt-video-importer-lite');?> )</span></label>
			</td>
		</tr>

		<tr>
			<th><label for="cbc_controls"><?php _e('Show controls', 'yt-video-importer-lite');?>:</label></th>
			<td><input name="controls" id="cbc_controls" class="cbc_controls"
				type="checkbox" value="1"
				<?php yvil_check((bool)$settings['controls']);?> /> <label
				for="cbc_controls"><span class="description">( <?php _e('when checked, player will display video controls', 'yt-video-importer-lite');?> )</span></label>
			</td>
		</tr>

		<tr class="controls_dependant"
			<?php yvil_hide((bool)$settings['controls']);?>>
			<th><label for="cbc_fs"><?php _e('Allow full screen', 'yt-video-importer-lite');?>:</label></th>
			<td><input name="fs" id="cbc_fs" type="checkbox" value="1"
				<?php yvil_check((bool)$settings['fs']);?> /></td>
		</tr>

		<tr class="controls_dependant"
			<?php yvil_hide((bool)$settings['controls']);?>>
			<th><label for="cbc_autohide"><?php _e('Autohide controls');?>:</label></th>
			<td>
				<?php
				$args = array( 
						'options' => array( 
								'0' => __( 'Always show controls', 'yt-video-importer-lite' ),
								'1' => __( 'Hide controls on load and when playing', 'yt-video-importer-lite' ),
								'2' => __( 'Hide controls when playing', 'yt-video-importer-lite' )
						), 
						'name' => 'autohide', 
						'id' => 'cbc_autohide', 
						'selected' => $settings[ 'autohide' ] 
				);
				yvil_select( $args );
				?>
			</td>
		</tr>

		<tr class="controls_dependant"
			<?php yvil_hide((bool)$settings['controls']);?>>
			<th><label for="cbc_theme"><?php _e('Player theme', 'yt-video-importer-lite');?>:</label></th>
			<td>
				<?php
				$args = array( 
						'options' => array( 
								'dark' => __( 'Dark', 'yt-video-importer-lite' ),
								'light' => __( 'Light', 'yt-video-importer-lite' )
						), 
						'name' => 'theme', 
						'id' => 'cbc_theme', 
						'selected' => $settings[ 'theme' ] 
				);
				yvil_select( $args );
				?>
			</td>
		</tr>

		<tr class="controls_dependant"
			<?php yvil_hide((bool)$settings['controls']);?>>
			<th><label for="cbc_color"><?php _e('Player color', 'yt-video-importer-lite');?>:</label></th>
			<td>
				<?php
				$args = array( 
						'options' => array( 
								'red' => __( 'Red', 'yt-video-importer-lite' ),
								'white' => __( 'White', 'yt-video-importer-lite' )
						), 
						'name' => 'color', 
						'id' => 'cbc_color', 
						'selected' => $settings[ 'color' ] 
				);
				yvil_select( $args );
				?>
			</td>
		</tr>

		<tr class="controls_dependant" valign="top"
			<?php yvil_hide($settings['controls']);?>>
			<th scope="row"><label for="modestbranding"><?php _e('No YouTube logo on controls bar', 'yt-video-importer-lite')?>:</label></th>
			<td><input type="checkbox" value="1" id="modestbranding"
				name="modestbranding"
				<?php yvil_check( (bool)$settings['modestbranding'] );?> /> <span
				class="description"><?php _e('Setting the color parameter to white will cause this option to be ignored.', 'yt-video-importer-lite');?></span>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="iv_load_policy"><?php _e('Annotations', 'yt-video-importer-lite')?>:</label></th>
			<td>
				<?php
				$args = array( 
						'options' => array( 
								'1' => __( 'Show annotations by default', 'yt-video-importer-lite' ),
								'3' => __( 'Hide annotations', 'yt-video-importer-lite' )
						), 
						'name' => 'iv_load_policy', 
						'selected' => $settings[ 'iv_load_policy' ] 
				);
				yvil_select( $args );
				?>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="rel"><?php _e('Show related videos', 'yt-video-importer-lite')?>:</label></th>
			<td><input type="checkbox" value="1" id="rel" name="rel"
				<?php yvil_check( (bool)$settings['rel'] );?> /> <label for="rel"><span
					class="description"><?php _e('when checked, after video ends player will display related videos', 'yt-video-importer-lite');?></span></label>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="showinfo"><?php _e('Show video title in player', 'yt-video-importer-lite')?>:</label></th>
			<td><input type="checkbox" value="1" id="showinfo" name="showinfo"
				<?php yvil_check( (bool )$settings['showinfo']);?> /></td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="disablekb"><?php _e('Disable keyboard player controls', 'yt-video-importer-lite')?>:</label></th>
			<td><input type="checkbox" value="1" id="disablekb" name="disablekb"
				<?php yvil_check( (bool)$settings['disablekb'] );?> /> <span
				class="description"><?php _e('Works only when player has focus.', 'yt-video-importer-lite');?></span>
			</td>
		</tr>

	</tbody>
</table>
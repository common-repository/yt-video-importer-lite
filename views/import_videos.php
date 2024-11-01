
<p class="description">
		<?php _e('Import videos from YouTube.', 'yt-video-importer-lite');?><br />
		<?php _e('Enter your search criteria and submit. All found videos will be displayed and you can selectively import videos into WordPress.', 'yt-video-importer-lite');?>
	</p>

<form method="get" action="" id="cbc_load_feed_form">
		<?php wp_nonce_field('cbc-video-import', 'cbc_search_nonce');?>
		<input type="hidden" name="post_type"
		value="<?php echo $this->cpt->get_post_type();?>" /> <input
		type="hidden" name="page" value="cbc_import" /> <input type="hidden"
		name="cbc_source" value="youtube" />
	<table class="form-table">
		<tr class="cbc_feed">
			<th valign="top"><label for="cbc_feed"><?php _e('Feed type', 'yt-video-importer-lite');?>:</label>
			</th>
			<td>					
					<?php
					$selected = isset( $_GET[ 'cbc_feed' ] ) ? $_GET[ 'cbc_feed' ] : 'query';
					$args = array( 
							'options' => array( 
									'user' => __( 'User feed', 'yt-video-importer-lite' ),
									'playlist' => __( 'Playlist feed', 'yt-video-importer-lite' ),
									'channel' => __( 'Channel uploads feed', 'yt-video-importer-lite' ),
									'query' => __( 'Search query feed', 'yt-video-importer-lite' )
							), 
							'name' => 'cbc_feed', 
							'id' => 'cbc_feed', 
							'selected' => $selected 
					);
					yvil_select( $args );
					?>
					<span class="description"><?php _e('Select the type of feed you want to load.', 'yt-video-importer-lite');?></span>
			</td>
		</tr>

		<tr class="cbc_duration">
			<th valign="top"><label for="cbc_duration"><?php _e('Video duration', 'yt-video-importer-lite');?>:</label></th>
			<td>
					<?php
					$selected = isset( $_GET[ 'cbc_duration' ] ) ? $_GET[ 'cbc_duration' ] : '';
					$args = array( 
							'options' => array( 
									'any' => __( 'Any', 'yt-video-importer-lite' ),
									'short' => __( 'Short (under 4min.)', 'yt-video-importer-lite' ),
									'medium' => __( 'Medium (between 4 and 20min.)', 'yt-video-importer-lite' ),
									'long' => __( 'Long (over 20min.)', 'yt-video-importer-lite' )
							), 
							'name' => 'cbc_duration', 
							'id' => 'cbc_duration', 
							'selected' => $selected 
					);
					yvil_select( $args );
					?>		
				</td>
		</tr>

		<tr class="cbc_query">
			<th valign="top"><label for="cbc_query"><?php _e('Search by', 'yt-video-importer-lite');?>:</label>
			</th>
			<td>
					<?php $query = isset( $_GET['cbc_query'] ) ? sanitize_text_field( $_GET['cbc_query'] ) : '';?>
					<input type="text" name="cbc_query" id="cbc_query"
				value="<?php echo $query;?>" size="50" /> <span class="description"><?php _e('Enter playlist ID, user ID or search query according to Feed Type selection.', 'yt-video-importer-lite');?></span>
			</td>
		</tr>

		<tr class="cbc_order">
			<th valign="top"><label for="cbc_order"><?php _e('Order by', 'yt-video-importer-lite');?>:</label></th>
			<td>
					<?php
					$selected = isset( $_GET[ 'cbc_order' ] ) ? $_GET[ 'cbc_order' ] : false;
					$args = array( 
							'options' => array( 
									'date' => __( 'Date of publishing', 'yt-video-importer-lite' ),
									'rating' => __( 'Rating', 'yt-video-importer-lite' ),
									'relevance' => __( 'Search relevance', 'yt-video-importer-lite' ),
									'title' => __( 'Video title', 'yt-video-importer-lite' ),
									'viewCount' => __( 'Number of views', 'yt-video-importer-lite' )
							), 
							'name' => 'cbc_order', 
							'id' => 'cbc_order', 
							'selected' => $selected 
					);
					yvil_select( $args );
					?>
				</td>
		</tr>			
			<?php
			$theme_support = yvil_check_theme_support();
			if( $theme_support ):
				?>
			<tr>
			<th valign="top"><label for="cbc_theme_import"><?php printf( __('Import as posts <br />compatible with <strong>%s</strong>?', 'yt-video-importer-lite'), $theme_support['theme_name']);?></label>
			</th>
			<td>
					<?php $checked = isset( $_GET['cbc_theme_import'] ) ? ' checked="checked"' : '';?>
					<input type="checkbox" name="cbc_theme_import"
				id="cbc_theme_import" value="1" <?php echo $checked?> /> <span
				class="description">
						<?php printf( __('If you choose to import as %s posts, all videos will be imported as post type <strong>%s</strong> and will be visible in your blog categories.', 'yt-video-importer-lite'), $theme_support['theme_name'], $theme_support['post_type']);?>
					</span>
			</td>
		</tr>
			<?php 
				endif
			?>
	</table>
		<?php submit_button( __('Load feed', 'yt-video-importer-lite'));?>
	</form>
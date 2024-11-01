<div class="wrap">
	<div class="icon32" id="icon-options-general">
		<br>
	</div>
	<h2><?php _e('Videos - Plugin settings', 'yt-video-importer-lite');?></h2>
	<form method="post" action="<?php echo $form_action;?>">
		<div id="cbc_tabs">
			<?php wp_nonce_field('cbc-save-plugin-settings', 'cbc_wp_nonce');?>
			<ul class="cbc-tab-labels">
				<li><a href="#cbc-settings-post-options"><i
						class="dashicons dashicons-arrow-right"></i> <?php _e('Post options', 'yt-video-importer-lite')?></a></li>
				<li><a href="#cbc-settings-content-options"><i
						class="dashicons dashicons-arrow-right"></i> <?php _e('Content options', 'yt-video-importer-lite')?></a></li>
				<li><a href="#cbc-settings-image-options"><i
						class="dashicons dashicons-arrow-right"></i> <?php _e('Image options', 'yt-video-importer-lite')?></a></li>
				<li><a href="#cbc-settings-import-options"><i
						class="dashicons dashicons-arrow-right"></i> <?php _e('Import options', 'yt-video-importer-lite')?></a></li>
				<li><a href="#cbc-settings-embed-options"><i
						class="dashicons dashicons-arrow-right"></i> <?php _e('Embed options', 'yt-video-importer-lite')?></a></li>
				<li><a href="#cbc-settings-auth-options"><i
						class="dashicons dashicons-arrow-right"></i> <?php _e('API & License', 'yt-video-importer-lite')?></a></li>
                <li><a href="#cbc-settings-help-info"><i
                                class="dashicons dashicons-help"></i> <?php _e('Info & Help', 'yt-video-importer-lite')?></a></li>
			</ul>
			<!-- Tab post options -->
			<div id="cbc-settings-post-options">
				<table class="form-table">
					<tbody>
						<!-- Import type -->
						<tr>
							<th colspan="2">
                                <h4>
									<i class="dashicons dashicons-admin-tools"></i> <?php _e('General settings', 'yt-video-importer-lite');?>
                                    <?php $this->show_pro_fields_button();?>
                                </h4>
                            </th>
						</tr>

                        <!-- PRO option -->
						<tr valign="top" class="<?php $this->pro_field_classes();?>">
							<th scope="row"><label for="post_type_post"><?php _e('Import as regular post type (aka post)', 'yt-video-importer-lite')?>:</label></th>
							<td><?php $this->pro_checkbox();?> <span
								class="description">
								<?php _e('Videos will be imported as <strong>regular posts</strong> instead of custom post type video. Posts having attached videos will display having the same player options as video post types.', 'yt-video-importer-lite');?>
								</span></td>
						</tr>

                        <tr valign="top">
							<th scope="row"><label for="archives"><?php _e('Embed videos in archive pages', 'yt-video-importer-lite')?>:</label></th>
							<td><input type="checkbox" name="archives" value="1"
								id="archives" <?php yvil_check( $options['archives'] );?> /> <span
								class="description">
									<?php _e('When checked, videos will be visible on all pages displaying lists of video posts.', 'yt-video-importer-lite');?>
								</span></td>
						</tr>

                        <!-- PRO option -->
                        <tr valign="top" class="<?php $this->pro_field_classes();?>">
							<th scope="row"><label for="use_microdata"><?php _e('Include microdata on video pages', 'yt-video-importer-lite')?>:</label></th>
							<td><?php $this->pro_checkbox();?> <span
								class="description">
									<?php _e('When checked, all pages displaying videos will also include microdata for SEO purposes ( more on <a href="http://schema.org" target="_blank">http://schema.org</a> ).', 'yt-video-importer-lite');?>
								</span></td>
						</tr>

                        <!-- PRO option -->
						<tr valign="top" class="<?php $this->pro_field_classes();?>">
							<th scope="row"><label for="check_video_status"><?php _e('Check video statuses after import', 'yt-video-importer-lite')?>:</label></th>
							<td><?php $this->pro_checkbox();?> <span
								class="description">
									<?php _e('When checked, will verify on YouTube every 24H if the video still exists or is embeddable and if not, it will automatically set the post status to pending. This action is triggered by your website visitors.', 'yt-video-importer-lite');?>
								</span></td>
						</tr>

						<!-- Visibility -->
						<tr>
							<th colspan="2">
                                <h4>
									<i class="dashicons dashicons-video-alt3"></i> <?php _e('Video post type options', 'yt-video-importer-lite');?>
	                                <?php $this->show_pro_fields_button();?>
                                </h4>
                            </th>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="public"><?php _e('Video post type is public', 'yt-video-importer-lite')?>:</label></th>
							<td><input type="checkbox" name="public" value="1" id="public"
								<?php yvil_check( $options['public'] );?> /> <span
								class="description">
								<?php if( !$options['public'] ):?>
									<span style="color: red;"><?php _e('Videos cannot be displayed in front-end. You can only incorporate them in playlists or display them in regular posts using shortcodes.', 'yt-video-importer-lite');?></span>
								<?php else:?>
								<?php _e('Videos will display in front-end as post type video are and can also be incorporated in playlists or displayed in regular posts.', 'yt-video-importer-lite');?>
								<?php endif;?>
								</span></td>
						</tr>

                        <!-- PRO option -->
						<tr valign="top" class="<?php $this->pro_field_classes() ;?>">
							<th scope="row"><label for="homepage"><?php _e('Include videos post type on homepage', 'yt-video-importer-lite')?>:</label></th>
							<td><?php $this->pro_checkbox();?> <span
								class="description">
									<?php _e('When checked, if your homepage displays a list of regular posts, videos will be included among them.', 'yt-video-importer-lite');?>
								</span></td>
						</tr>

                        <!-- PRO option -->
						<tr valign="top" class="<?php $this->pro_field_classes() ;?>">
							<th scope="row"><label for="main_rss"><?php _e('Include videos post type in main RSS feed', 'yt-video-importer-lite')?>:</label></th>
							<td><?php $this->pro_checkbox();?> <span
								class="description">
									<?php _e('When checked, custom post type will be included in your main RSS feed.', 'yt-video-importer-lite');?>
								</span></td>
						</tr>


						<!-- Rewrite settings -->
						<tr>
							<th colspan="2"><h4>
									<i class="dashicons dashicons-admin-links"></i> <?php _e('Video post type rewrite (pretty links)', 'yt-video-importer-lite');?></h4></th>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="post_slug"><?php _e('Post slug', 'yt-video-importer-lite')?>:</label></th>
							<td><input type="text" id="post_slug" name="post_slug"
								value="<?php echo $options['post_slug'];?>" /></td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="taxonomy_slug"><?php _e('Taxonomy slug', 'yt-video-importer-lite')?> :</label></th>
							<td><input type="text" id="taxonomy_slug" name="taxonomy_slug"
								value="<?php echo $options['taxonomy_slug'];?>" /></td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="tag_taxonomy_slug"><?php _e('Tags slug', 'yt-video-importer-lite')?> :</label></th>
							<td><input type="text" id="tag_taxonomy_slug"
								name="tag_taxonomy_slug"
								value="<?php echo $options['tag_taxonomy_slug'];?>" /></td>
						</tr>
					</tbody>
				</table>
				<?php submit_button(__('Save settings', 'yt-video-importer-lite'));?>
			</div>
			<!-- /Tab post options -->

			<!-- Tab content options -->
			<div id="cbc-settings-content-options">
				<table class="form-table">
					<tbody>
						<!-- Content settings -->
						<tr>
							<th colspan="2">
                                <h4>
									<i class="dashicons dashicons-admin-post"></i> <?php _e('Post content settings', 'yt-video-importer-lite');?>
	                                <?php $this->show_pro_fields_button();?>
                                </h4>
                            </th>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="import_categories"><?php _e('Import categories', 'yt-video-importer-lite')?>:</label></th>
							<td><input type="checkbox" value="1" id="import_categories"
								name="import_categories"
								<?php yvil_check($options['import_categories']);?> /> <span
								class="description"><?php _e('Categories retrieved from YouTube will be automatically created and videos assigned to them accordingly.', 'yt-video-importer-lite');?></span>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="import_tags"><?php _e('Import tag', 'yt-video-importer-lite')?>:</label></th>
							<td><input type="checkbox" value="1" id="import_tags"
								name="import_tags" <?php yvil_check($options['import_tags']);?> />
								<span class="description"><?php _e('Automatically import first video tag as post tag from feeds.', 'yt-video-importer-lite');?></span><br />
							</td>
						</tr>

                        <!-- PRO option -->
						<tr valign="top" class="<?php $this->pro_field_classes();?>">
							<th scope="row"><label for="max_tags"><?php _e('Maximum number of tags', 'yt-video-importer-lite')?>:</label></th>
							<td><input type="text"
								value="5" name="" size="1" disabled="disabled" />
                                <span
								class="description"><?php _e('Maximum number of tags that will be imported. PRO version allows importing of multiple tags.', 'yt-video-importer-lite');?></span>
							</td>
						</tr>

                        <!-- PRO option -->
						<tr valign="top" class="<?php $this->pro_field_classes();?>">
							<th scope="row"><label for="import_date"><?php _e('Import date', 'yt-video-importer-lite')?>:</label></th>
							<td><?php $this->pro_checkbox();?>
								<span class="description"><?php _e("Imports will have YouTube's publishing date.", 'yt-video-importer-lite');?></span>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="import_title"><?php _e('Import titles', 'yt-video-importer-lite')?>:</label></th>
							<td><input type="checkbox" value="1" id="import_title"
								name="import_title" <?php yvil_check($options['import_title']);?> />
								<span class="description"><?php _e('Automatically import video titles from feeds as post title.', 'yt-video-importer-lite');?></span>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="import_description"><?php _e('Import descriptions as', 'yt-video-importer-lite')?>:</label></th>
							<td>
								<?php
								$args = array( 
										'options' => array( 
												'content' => __( 'post content', 'yt-video-importer-lite' ),
												'excerpt' => __( 'post excerpt', 'yt-video-importer-lite' ),
												'content_excerpt' => __( 'post content and excerpt', 'yt-video-importer-lite' ),
												'none' => __( 'do not import', 'yt-video-importer-lite' )
										), 
										'name' => 'import_description', 
										'selected' => $options[ 'import_description' ] 
								);
								yvil_select( $args );
								?>
								<p class="description"><?php _e('Import video description from feeds as post description, excerpt or none.', 'yt-video-importer-lite')?></p>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="remove_after_text"><?php _e('Remove text from descriptions found after', 'yt-video-importer-lite')?>:</label></th>
							<td><input type="text" name="remove_after_text"
								value="<?php echo $options['remove_after_text'];?>"
								id="remove_after_text" size="70" />
								<p class="description">
									<?php _e('If text above is found in description, all text following it (including the one entered above) will be removed from post content.', 'yt-video-importer-lite');?><br />
									<?php _e('<strong>Please note</strong> that the plugin will search for the entire string entered here, not parts of it. An exact match must be found to perform the action.', 'yt-video-importer-lite');?>
								</p></td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="prevent_autoembed"><?php _e('Prevent auto embed on video content', 'yt-video-importer-lite')?>:</label></th>
							<td><input type="checkbox" value="1" name="prevent_autoembed"
								id="prevent_autoembed"
								<?php yvil_check($options['prevent_autoembed']);?> /> <span
								class="description">
									<?php _e('If content retrieved from YouTube has links to other videos, checking this option will prevent auto embedding of videos in your post content.', 'yt-video-importer-lite');?>
								</span></td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="make_clickable"><?php _e("Make URL's in video content clickable", 'yt-video-importer-lite')?>:</label></th>
							<td><input type="checkbox" value="1" name="make_clickable"
								id="make_clickable"
								<?php yvil_check($options['make_clickable']);?> /> <span
								class="description">
									<?php _e("Automatically make all valid URL's from content retrieved from YouTube clickable.", 'yt-video-importer-lite');?>
								</span></td>
						</tr>

					</tbody>
				</table>
				<?php submit_button(__('Save settings', 'yt-video-importer-lite'));?>
			</div>
			<!-- /Tab content options -->

			<!-- Tab image options -->
			<div id="cbc-settings-image-options">
				<table class="form-table">
					<tbody>
						<tr>
							<th colspan="2">
                                <h4>
									<i class="dashicons dashicons-format-image"></i> <?php _e('Image settings', 'yt-video-importer-lite');?>
	                                <?php $this->show_pro_fields_button();?>
                                </h4>
                            </th>
						</tr>

                        <!-- PRO option -->
						<tr valign="top" class="<?php $this->pro_field_classes();?>">
							<th scope="row"><label for="featured_image"><?php _e('Import images', 'yt-video-importer-lite')?>:</label></th>
							<td><?php $this->pro_checkbox();?> <span
								class="description"><?php _e("YouTube video thumbnail will be set as post featured image.", 'yt-video-importer-lite');?></span>
							</td>
						</tr>

                        <!-- PRO option -->
						<tr valign="top" class="<?php $this->pro_field_classes();?>">
							<th scope="row"><label for="image_on_demand"><?php _e('Import featured image on request', 'yt-video-importer-lite')?>:</label></th>
							<td><?php $this->pro_checkbox();?> <span
								class="description"><?php _e("YouTube video thumbnail will be imported only when featured images needs to be displayed (ie. a post created by the plugin is displayed).", 'yt-video-importer-lite');?></span>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="image_size"><?php _e('Image size', 'yt-video-importer-lite')?>:</label></th>
							<td>
								<?php
								$args = array( 
										'options' => array( 
												'' => __( 'Choose', 'yt-video-importer-lite' ),
												'default' => __( 'Default (120x90 px)', 'yt-video-importer-lite' ),
												'medium' => __( 'Medium (320x180 px)', 'yt-video-importer-lite' ),
												'high' => __( 'High (480x360 px)', 'yt-video-importer-lite' ),
												'standard' => __( 'Standard (640x480 px)', 'yt-video-importer-lite' ),
												'maxres' => __( 'Maximum (1280x720 px)', 'yt-video-importer-lite' )
										), 
										'name' => 'image_size', 
										'selected' => $options[ 'image_size' ] 
								);
								yvil_select( $args );
								?>	
								( <input type="checkbox" value="1" name="maxres" id="maxres"
								<?php yvil_check( $options['maxres'] );?> /> <label for="maxres"><?php _e('try to retrieve maximum resolution if available', 'yt-video-importer-lite');?></label>
								)
							</td>
						</tr>

					</tbody>
				</table>
				<?php submit_button(__('Save settings', 'yt-video-importer-lite'));?>
			</div>
			<!-- /Tab image options -->

			<!-- Tab import options -->
			<div id="cbc-settings-import-options">
				<table class="form-table">
					<tbody>
						<!-- Manual Import settings -->
						<tr>
							<th colspan="2">
                                <h4>
									<i class="dashicons dashicons-download"></i> <?php _e('Bulk Import settings', 'yt-video-importer-lite');?>
	                                <?php $this->show_pro_fields_button();?>
                                </h4>
                            </th>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="import_status"><?php _e('Import status', 'yt-video-importer-lite')?>:</label></th>
							<td>
								<?php
								$args = array( 
										'options' => array( 
												'publish' => __( 'Published', 'yt-video-importer-lite' ),
												'draft' => __( 'Draft', 'yt-video-importer-lite' ),
												'pending' => __( 'Pending', 'yt-video-importer-lite' )
										), 
										'name' => 'import_status', 
										'selected' => $options[ 'import_status' ] 
								);
								yvil_select( $args );
								?>
								<p class="description"><?php _e('Imported videos will have this status.', 'yt-video-importer-lite');?></p>
							</td>
						</tr>

                        <!-- PRO option -->
						<tr valign="top" class="<?php $this->pro_field_classes();?>">
							<th scope="row"><label for="import_frequency"><?php _e('Automatic import', 'yt-video-importer-lite')?>:</label></th>
							<td>
								<?php _e('Import ', 'yt-video-importer-lite');?>
								<?php
								$args = array( 
										'options' => array(
											'1' => __( '1 video', 'yt-video-importer-lite' ),
											'5' => __( '5 videos', 'yt-video-importer-lite' ),
											'10' => __( '10 videos', 'yt-video-importer-lite' ),
											'15' => __( '15 videos', 'yt-video-importer-lite' ),
											'20' => __( '20 videos', 'yt-video-importer-lite' ),
											'25' => __( '25 videos', 'yt-video-importer-lite' ),
											'30' => __( '30 videos', 'yt-video-importer-lite' ),
											'40' => __( '40 videos', 'yt-video-importer-lite' ),
											'50' => __( '50 videos', 'yt-video-importer-lite' )
										),
										'name' => '',
										'selected' => 15,
                                        'disabled' => true
								);
								yvil_select( $args );
								?>
								<?php _e('every', 'yt-video-importer-lite');?>
								<?php
								$args = array( 
										'options' => array(
											'1' => __( 'minute', 'yt-video-importer-lite' ),
											'5' => __( '5 minutes', 'yt-video-importer-lite' ),
											'15' => __( '15 minutes', 'yt-video-importer-lite' ),
											'30' => __( '30 minutes', 'yt-video-importer-lite' ),
											'60' => __( 'hour', 'yt-video-importer-lite' ),
											'120' => __( '2 hours', 'yt-video-importer-lite' ),
											'180' => __( '3 hours', 'yt-video-importer-lite' ),
											'360' => __( '6 hours', 'yt-video-importer-lite' ),
											'720' => __( '12 hours', 'yt-video-importer-lite' ),
											'1440' => __( 'day', 'yt-video-importer-lite' )
										),
										'name' => '',
										'selected' => 5,
                                        'disabled' => true
								);
								yvil_select( $args );
								?>
								<p class="description"><?php _e('How often should YouTube be queried for playlist updates.', 'yt-video-importer-lite');?></p>
							</td>
						</tr>

                        <!-- PRO option -->
						<tr valign="top" class="<?php $this->pro_field_classes();?>">
							<th scope="row"><label for="cbc_conditional_import"><?php _e( 'Enable conditional automatic imports', 'yt-video-importer-lite' );?>:</label>
							</th>
							<td><?php $this->pro_checkbox();?> <span
								class="description"><?php _e( 'When enabled, automatic imports will run only when a custom URL is opened on your website.', 'yt-video-importer-lite' );?></span>
							</td>
						</tr>

                        <!-- PRO option -->
						<tr valign="top" class="<?php $this->pro_field_classes();?>">
							<th scope="row"><label for="page_load_autoimport"><?php _e('Legacy automatic import', 'yt-video-importer-lite')?>:</label></th>
							<td><?php $this->pro_checkbox();?> <span
								class="description"><?php _e( 'Trigger automatic video imports on page load (will increase page load time when doing automatic imports)', 'yt-video-importer-lite' );?></span>
								<p>
									<?php _e( 'Starting with version 1.2, automatic imports are triggered by making a remote call to your website that triggers the imports. This decreases page loading time and improves the import process.', 'yt-video-importer-lite' );?><br />
									<?php _e( 'Some systems may not allow this functionality. If you notice that your automatic import playlists aren\'t importing, enable this option.', 'yt-video-importer-lite' );?>
								</p></td>
						</tr>

                        <!-- PRO option -->
						<tr valign="top" class="<?php $this->pro_field_classes();?>">
							<th scope="row"><label for="unpublish_on_yt_error"><?php _e('Remove playlist from queue on YouTube error', 'yt-video-importer-lite');?>:</label></th>
							<td><?php $this->pro_checkbox();?>
								<span class="description">
									<?php _e( 'When checked, if automatically imported playlist returns a YouTube error when queued, it will be unpublished.', 'yt-video-importer-lite' );?>
								</span></td>
						</tr>

						<tr>
							<th scope="row"><label for="manual_import_per_page"><?php _e('Manual import results per page', 'yt-video-importer-lite')?>:</label></th>
							<td>
								<?php
								$args = array( 
										'options' => array(
										    '5' => __('5 videos', 'yt-video-importer-lite'),
                                            '10' => __('10 videos', 'yt-video-importer-lite'),
                                            '20' => __('20 videos', 'yt-video-importer-lite'),
                                            '50' => __('50 videos', 'yt-video-importer-lite')
                                        ),
										'name' => 'manual_import_per_page', 
										'selected' => $options[ 'manual_import_per_page' ] 
								);
								yvil_select( $args );
								?>
								<p class="description"><?php _e('How many results to display per page on manual import.', 'yt-video-importer-lite');?></p>
							</td>
						</tr>
					</tbody>
				</table>
				<?php submit_button(__('Save settings', 'yt-video-importer-lite'));?>
			</div>
			<!-- /Tab import options -->

			<!-- Tab embed options -->
			<div id="cbc-settings-embed-options">
				<table class="form-table">
					<tbody>
						<tr>
							<th colspan="2">
								<h4>
									<i class="dashicons dashicons-video-alt3"></i> <?php _e('Player settings', 'yt-video-importer-lite');?></h4>
								<p class="description"><?php _e('General YouTube player settings. These settings will be applied to any new video by default and can be changed individually for every imported video.', 'yt-video-importer-lite');?></p>
							</th>
						</tr>

						<tr>
							<th><label for="cbc_aspect_ratio"><?php _e('Player size', 'yt-video-importer-lite');?>:</label></th>
							<td class="cbc-player-settings-options"><label
								for="cbc_aspect_ratio"><?php _e('Aspect ratio', 'yt-video-importer-lite');?>:</label>
								<?php
								$args = array( 
										'options' => array( 
												'4x3' => '4x3', 
												'16x9' => '16x9' 
										), 
										'name' => 'aspect_ratio', 
										'id' => 'cbc_aspect_ratio', 
										'class' => 'cbc_aspect_ratio', 
										'selected' => $player_opt[ 'aspect_ratio' ] 
								);
								yvil_select( $args );
								?>
								<label for="cbc_width"><?php _e('Width', 'yt-video-importer-lite');?>:</label>
								<input type="text" name="width" id="cbc_width" class="cbc_width"
								value="<?php echo $player_opt['width'];?>" size="2" />px
								| <?php _e('Height', 'yt-video-importer-lite');?> : <span class="cbc_height"
								id="cbc_calc_height"><?php echo yvil_player_height( $player_opt['aspect_ratio'], $player_opt['width'] );?></span>px
							</td>
						</tr>

						<tr>
							<th><label for="cbc_video_position"><?php _e('Show video in custom post','yt-video-importer-lite');?>:</label></th>
							<td>
								<?php
								$args = array( 
										'options' => array( 
												'above-content' => __( 'Above post content', 'yt-video-importer-lite' ),
												'below-content' => __( 'Below post content', 'yt-video-importer-lite' )
										), 
										'name' => 'video_position', 
										'id' => 'cbc_video_position', 
										'selected' => $player_opt[ 'video_position' ] 
								);
								yvil_select( $args );
								?>
							</td>
						</tr>

						<tr>
							<th><label for="cbc_volume"><?php _e('Volume', 'yt-video-importer-lite');?></label>:</th>
							<td><input type="text" name="volume" id="cbc_volume"
								value="<?php echo $player_opt['volume'];?>" size="1"
								maxlength="3" /> <label for="cbc_volume"><span
									class="description">( <?php _e('number between 0 (mute) and 100 (max)', 'yt-video-importer-lite');?> )</span></label>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="autoplay"><?php _e('Autoplay', 'yt-video-importer-lite')?>:</label></th>
							<td><input type="checkbox" value="1" id="autoplay"
								name="autoplay"
								<?php yvil_check( (bool )$player_opt['autoplay'] );?> /></td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="cbc_controls"><?php _e('Show player controls', 'yt-video-importer-lite')?>:</label></th>
							<td><input type="checkbox" value="1" id="cbc_controls"
								class="cbc_controls" name="controls"
								<?php yvil_check( (bool)$player_opt['controls'] );?> /></td>
						</tr>

						<tr valign="top" class="controls_dependant"
							<?php yvil_hide((bool)$player_opt['controls']);?>>
							<th scope="row"><label for="fs"><?php _e('Allow fullscreen', 'yt-video-importer-lite')?>:</label></th>
							<td><input type="checkbox" name="fs" id="fs" value="1"
								<?php yvil_check( (bool)$player_opt['fs'] );?> /></td>
						</tr>

						<tr valign="top" class="controls_dependant"
							<?php yvil_hide((bool)$player_opt['controls']);?>>
							<th scope="row"><label for="autohide"><?php _e('Autohide controls', 'yt-video-importer-lite')?>:</label></th>
							<td>
								<?php
								$args = array( 
										'options' => array( 
												'0' => __( 'Always show controls', 'yt-video-importer-lite' ),
												'1' => __( 'Hide controls on load and when playing', 'yt-video-importer-lite' ),
												'2' => __( 'Fade out progress bar when playing', 'yt-video-importer-lite' )
										), 
										'name' => 'autohide', 
										'selected' => $player_opt[ 'autohide' ] 
								);
								yvil_select( $args );
								?>
							</td>
						</tr>

						<tr valign="top" class="controls_dependant"
							<?php yvil_hide((bool)$player_opt['controls']);?>>
							<th scope="row"><label for="theme"><?php _e('Player theme', 'yt-video-importer-lite')?>:</label></th>
							<td>
								<?php
								$args = array( 
										'options' => array( 
												'dark' => __( 'Dark', 'yt-video-importer-lite' ),
												'light' => __( 'Light', 'yt-video-importer-lite' )
										), 
										'name' => 'theme', 
										'selected' => $player_opt[ 'theme' ] 
								);
								yvil_select( $args );
								?>
							</td>
						</tr>

						<tr valign="top" class="controls_dependant"
							<?php yvil_hide((bool)$player_opt['controls']);?>>
							<th scope="row"><label for="color"><?php _e('Player color', 'yt-video-importer-lite')?>:</label></th>
							<td>
								<?php
								$args = array( 
										'options' => array( 
												'red' => __( 'Red', 'yt-video-importer-lite' ),
												'white' => __( 'White', 'yt-video-importer-lite' )
										), 
										'name' => 'color', 
										'selected' => $player_opt[ 'color' ] 
								);
								yvil_select( $args );
								?>
							</td>
						</tr>

						<tr valign="top" class="controls_dependant"
							<?php yvil_hide((bool)$player_opt['controls']);?>>
							<th scope="row"><label for="modestbranding"><?php _e('No YouTube logo on controls bar', 'yt-video-importer-lite')?>:</label></th>
							<td><input type="checkbox" value="1" id="modestbranding"
								name="modestbranding"
								<?php yvil_check( (bool)$player_opt['modestbranding'] );?> /> <span
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
										'selected' => $player_opt[ 'iv_load_policy' ] 
								);
								yvil_select( $args );
								?>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="rel"><?php _e('Show related videos', 'yt-video-importer-lite')?>:</label></th>
							<td><input type="checkbox" value="1" id="rel" name="rel"
								<?php yvil_check( (bool)$player_opt['rel'] );?> /> <label
								for="rel"><span class="description"><?php _e('when checked, after video ends player will display related videos', 'yt-video-importer-lite');?></span></label>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="showinfo"><?php _e('Show video title by default', 'yt-video-importer-lite')?>:</label></th>
							<td><input type="checkbox" value="1" id="showinfo"
								name="showinfo"
								<?php yvil_check( (bool )$player_opt['showinfo']);?> /></td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="disablekb"><?php _e('Disable keyboard player controls', 'yt-video-importer-lite')?>:</label></th>
							<td><input type="checkbox" value="1" id="disablekb"
								name="disablekb"
								<?php yvil_check( (bool)$player_opt['disablekb'] );?> /> <span
								class="description"><?php _e('Works only when player has focus.', 'yt-video-importer-lite');?></span>
								<p class="description"><?php _e('Controls:<br> - spacebar : play/pause,<br> - arrow left : jump back 10% in current video,<br> - arrow-right: jump ahead 10% in current video,<br> - arrow up - volume up,<br> - arrow down - volume down.', 'yt-video-importer-lite');?></p>
							</td>
						</tr>
					</tbody>
				</table>
				<?php submit_button(__('Save settings', 'yt-video-importer-lite'));?>
			</div>
			<!-- /Tab embed options -->

			<!-- Tab auth options -->
			<div id="cbc-settings-auth-options">
				<table class="form-table">
					<tbody>
						<tr>
							<th colspan="2">
								<h4>
									<i class="dashicons dashicons-admin-network"></i> <?php _e('YouTube API options', 'yt-video-importer-lite');?></h4>
							</th>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="show_quota_estimates"><?php _e('Show YouTube API daily quota', 'yt-video-importer-lite')?>:</label></th>
							<td><input type="checkbox" value="1" id="show_quota_estimates"
								name="show_quota_estimates"
								<?php yvil_check( $options['show_quota_estimates'] );?> /> <span
								class="description">
									<?php _e( 'When checked, will display estimates regarding your daily YouTube API available units.', 'yt-video-importer-lite' );?>
								</span></td>
						</tr>

						<tr>
							<td colspan="2">
								<h4>
									<i class="dashicons dashicons-admin-network"></i> <?php _e('YouTube OAuth credentials', 'yt-video-importer-lite');?></h4>
								<p class="description">
									<?php _e( 'By allowing the plugin to access your YouTube account, you will be able to quickly create automatic imports from your YouTube playlists and will also be able to import any public YouTube video.', 'yt-video-importer-lite' );?><br />
									<?php _e( 'After entering the OAuth credentials and granting access to your YouTube account, the server API key from the field above will not be used anymore and can be left empty.', 'yt-video-importer-lite' );?><br />
									<?php _e( 'To get your OAuth credentials, please visit: ', 'yt-video-importer-lite' );?> <a
										href="https://console.cloud.google.com/cloud-resource-manager" target="_blank">https://console.cloud.google.com/cloud-resource-manager</a>.
								</p>
								<p class="notice" style="padding: 1em 1em;">
									<?php _e( 'When creating OAuth credentials, make sure that under Authorized redirect URIs you enter: ' )?> <strong><?php echo yvil_get_oauth_redirect_uri();?></strong>
								</p>
							</td>
						</tr>
						<?php if( empty( $oauth_opt['client_id'] ) || empty( $oauth_opt['client_secret'] ) ):?>
						<tr valign="top">
							<th scope="row"><label for="oauth_client_id"><?php _e('Client ID', 'yt-video-importer-lite')?>:</label></th>
							<td><input type="text" name="oauth_client_id"
								id="oauth_client_id"
								value="<?php echo $oauth_opt['client_id'];?>" size="60" /></td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="oauth_client_secret"><?php _e('Client secret', 'yt-video-importer-lite')?>:</label></th>
							<td><input type="text" name="oauth_client_secret"
								id="oauth_client_secret"
								value="<?php echo $oauth_opt['client_secret'];?>" size="60" />
								<p class="description">
								<a href="<?php echo yvil_docs_link( 'getting-started/set-youtube-oauth-client-id-client-secret/' );?>" target="_blank"><?php _e( 'See how to generate OAuth credentials and authorize the plugin to use YouTube API.', 'yt-video-importer-lite' );?></a>
								</p>
							</td>
						</tr>
						<?php else: ?>
						<tr valign="top">
							<td colspan="2">
								<p class="description">
									<?php if( empty( $oauth_opt['token']['value'] ) ):?>
									<?php _e( 'In order to be able to use the plugin you must grant it access to your YouTube account by clicking the button below.', 'yt-video-importer-lite' );?>
									<?php else :?>
									<?php _e( 'You have granted plugin access to your YouTube account. To remove access, please click the button below.', 'yt-video-importer-lite' );?>
									<?php endif;?>
								</p>
								<p><?php yvil_show_oauth_link();?></p>
								<hr />
								<p class="description">									
									<?php _e( 'You have successfully entered your OAuth credentials. To enter new ones, please clear the current credentials.', 'yt-video-importer-lite' );?>
								</p>
								<p><?php yvil_clear_oauth_credentials_link();?></p>
							</td>
						</tr>
						<?php endif;?>
					</tbody>
				</table>
				<?php submit_button(__('Save settings', 'yt-video-importer-lite'));?>
			</div>
			<!-- /Tab auth options -->

            <!-- Tab Help&Info options -->
            <div id="cbc-settings-help-info">
                <h4>
                    <i class="dashicons dashicons-editor-help"></i> <?php _e('System information', 'yt-video-importer-lite');?>
                </h4>
                <ul>
                    <li><?php _e('PHP version', 'yt-video-importer-lite');?> : <strong><?php echo phpversion();?></strong></li>
                    <li><?php _e('WordPress version', 'yt-video-importer-lite');?> : <strong><?php echo get_bloginfo('version');?></strong></li>
                    <li><?php _e('Plugin version', 'yt-video-importer-lite')?> : <strong><?php echo YVIL_VERSION;?></strong></li>
                    <li><?php _e('WP Debug', 'yt-video-importer-lite') ?> : <?php WP_DEBUG ? _e('On', 'yt-video-importer-lite') : _e('Off', 'yt-video-importer-lite');?></li>
                    <li><?php _e('Remote requests', 'yt-video-importer-lite')?> : <?php defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && WP_HTTP_BLOCK_EXTERNAL ? _e( 'Blocked', 'yt-video-importer-lite') : _e( 'Allowed', 'yt-video-importer-lite'); ?></li>
		            <?php if( defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && WP_HTTP_BLOCK_EXTERNAL ):?>
                        <li><?php _e( 'Accesible hosts', 'yt-video-importer-lite' )?> : <?php echo  defined( 'WP_ACCESSIBLE_HOSTS' ) ? WP_ACCESSIBLE_HOSTS : __( 'Plugin unable to make API queries' , 'yt-video-importer-lite');?></li>
		            <?php endif;?>
                </ul>

                <h4>
                    <i class="dashicons dashicons-sos"></i> <?php _e( 'Documentation', 'yt-video-importer-lite' );?>
                </h4>
                <?php
                $links = array(
	                array(
		                'text' => __( 'Video import options', 'yt-video-importer-lite' ),
		                'url' => yvil_docs_link( 'plugin-settings/video-import-options/' )
	                ),
	                array(
		                'text' => __( 'Importing videos manually', 'yt-video-importer-lite' ),
		                'url' => yvil_docs_link( 'content-importing/manual-video-bulk-import/' )
	                ),
	                array(
		                'text' => __( 'Automatic video import', 'yt-video-importer-lite' ),
		                'url' => yvil_docs_link( 'content-importing/automatic-video-import/' )
	                ),
	                array(
		                'text' => __( 'Theme compatibility tutorial', 'yt-video-importer-lite' ),
		                'url' => yvil_docs_link( 'third-party-compatibility/' )
	                ),
	                array(
		                'text' => __( 'AMP plugin compatibility', 'yt-video-importer-lite' ),
		                'url' => yvil_docs_link( 'tutorials/amp-plugin-compatibility/' )
	                ),
	                array(
		                'text' => __( 'Store video stats in custom fields', 'yt-video-importer-lite' ),
		                'url' => yvil_docs_link( 'tutorials/storing-video-statistics-custom-fields/' )
	                ),
	                array(
		                'text' => __( 'A detailed view on automatic importing', 'yt-video-importer-lite' ),
		                'url' => yvil_docs_link( 'tutorials/automatic-import-explained/' )
	                ),
	                array(
		                'text' => __( 'Plugin actions', 'yt-video-importer-lite' ),
		                'url' => yvil_docs_link( 'plugin-actions/' )
	                ),
	                array(
		                'text' => __( 'Plugin filters', 'yt-video-importer-lite' ),
		                'url' => yvil_docs_link( 'plugin-filters/' )
	                )
                );
                ?>
                <ul>
		            <?php foreach($links as $link):?>
                        <li>
				            <?php if( isset( $link['before'] ) ){ echo $link['before']; }?>
                            <a href="<?php echo $link['url']?>" target="_blank"><?php echo $link['text'];?></a>
                        </li>
		            <?php endforeach;?>
                </ul>
            </div>
            <!-- /Tab Help&Info options -->

		</div>
		<!-- #cbc_tabs -->
	</form>
</div>
<?php
/*
	Section: Image Grid
	Author: Aleksander Hansson
	Author URI: http://ahansson.com
	Demo: http://imagegrid.ahansson.com
	Version: 1.3
	Description: A responsive Image Grid.
	Class Name: ImageGrid
	v3: true
*/

class ImageGrid extends PageLinesSection {

	var $tabID = 'image_grid_meta';

	function section_styles() {

		wp_enqueue_script( 'jquery' );

		wp_enqueue_script( 'ig-masonry', $this->base_url.'/js/masonry.pkgd.min.js' );

	}

	function section_head() {

		$clone_id = $this->get_the_id();

		?>

			<script type="text/javascript">
				jQuery(document).ready(function($){
					var $ig_modal<?php echo $clone_id; ?> = $('#ig_modal<?php echo $clone_id; ?>').modal({show: false});
					var $ig_carousel = $('#ig_carousel').carousel({'interval': false});

					$('.ig-image').each(function() {
						var $this = $(this);
						var index = $(this).data('slide-index');
						$this.click(function() {
							$ig_modal<?php echo $clone_id; ?>.modal('show');
							$ig_carousel.carousel(index);
						});
					});
				})
			</script>
			<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery('.ig-modal').appendTo(jQuery('body'))
				})
			</script>

		<?php

	}
	/**
	 * Section template.
	 */

	function section_template( ) {

		$ig_shortcode_override = ( ploption( 'ig_shortcode_override', $this->oset ) ) ? ploption( 'ig_shortcode_override', $this->oset ) : false;
		if ( $ig_shortcode_override == false ) {
			$this->ig_draw_container();
		}
	}

	function section_optionator( $settings ) {

		$settings = wp_parse_args( $settings, $this->optionator_default );
		$option_array = array(
			'ig_configs' => array(
				'type'    => 'multi_option',
				'title'   => 'Config Options (optional)',
				'shortexp'  => 'Control the selection, ordering and number of images in Image Grid',
				'selectvalues' => array(
					'ig_shortcode_override'  => array( 'inputlabel' => 'Disable section output (use this if you want to use the [imagegrid] shortcode).',
						'type' => 'select', 'selectvalues' => array(
							true   => array( 'name' => "Yes" ),
							false   => array( 'name' => "No" ),
						)
					),
					'ig_cols'  => array( 'inputlabel' => 'Number of columns (4, 3 and 2 is mobile friendly!)', 'type' => 'select', 'selectvalues' => array(
							'ig-col-2'   => array( 'name' => "2" ),
							'ig-col-3'   => array( 'name' => "3" ),
							'ig-col-4'   => array( 'name' => "4" ),
							'ig-col-5'   => array( 'name' => "5" ),
							'ig-col-6'   => array( 'name' => "6" ),
							'ig-col-7'   => array( 'name' => "7" ),
							'ig-col-8'   => array( 'name' => "8" ),
							'ig-col-8'   => array( 'name' => "9" ),
							'ig-col-10'   => array( 'name' => "10" ),
						)
					),
					'ig_order'  => array( 'inputlabel' => 'Order Type (Default: ASC)',
						'type' => 'select', 'selectvalues' => array(
							'ASC'   => array( 'name' => "Ascending Order" ),
							'DESC'   => array( 'name' => "Descending Order" ),
						)
					),
					'ig_orderby' => array( 'inputlabel' => 'Order According To (Default: Menu Order)',
						'type' => 'select', 'selectvalues' => array(
							'menu_order' => array( 'name' => "Using Add Media Popup (menu order)" ),
							'title'   => array( 'name' => "Using Image Title" ),
							'post_date'  => array( 'name' => "Using Post Date" ),
							'rand'   => array( 'name' => "Random Selection" ),
							'ID'   => array( 'name' => "Attachment ID" ),
						)
					),
					'ig_numposts' => array( 'inputlabel' => 'Maximum Amount of Images (Default: Unlimited; Must be more than 2)', 'type' => 'text_small' ),
					'ig_exclude' => array( 'inputlabel' => 'Exclude Attachment IDs - Comma Separated (Default: None)', 'type' => 'text' ),
					'ig_include' => array( 'inputlabel' => 'Include Attachments IDs - Comma Separated (Default: All)', 'type' => 'text' ),
				)
			),
			'ig_more_info'    => array(
				'type'          => 'help',
				'title'      => 'HOW TO USE:',
				'shortexp'   => '<strong>1.</strong> In Drag&Drop, drag Image Grid section to a template of your choice.<br/><br /><strong>2.</strong> Go to the page where you want your image grid.<br/><br /><strong>3.</strong> Upload an image gallery to your page (You have to upload images - you cannot choose images that are not uploaded to the specific post or page).<br/><br /><strong>4.</strong> Type in the meta information for each image in the gallery, including the title, description, and caption. <br/><br /><strong>5.</strong> By default Image Grid is outputting the image grid in the section area but you can use the shortcode [imagegrid] to output Image Grid in your post or page. (If you choose to use the shortcode you can disable the section output) <br/><br />',
			),
		);
		$metatab_settings = array(
			'id'   => $this->tabID,
			'name'   => 'Image Grid',
			'icon'   => $this->icon,
			'clone_id' => $settings['clone_id'],
			'active' => $settings['active']
		);
		register_metatab( $metatab_settings, $option_array );
	}

	function ig_draw_container() {

		global $post;
		$ig_numposts = ( ploption( 'ig_numposts', $this->oset ) ) ? ploption( 'ig_numposts', $this->oset ) : -1;
		$ig_order = ( ploption( 'ig_order', $this->oset ) ) ? ploption( 'ig_order', $this->oset ) : 'ASC';
		$ig_orderby = ( ploption( 'ig_orderby', $this->oset ) ) ? ploption( 'ig_orderby', $this->oset ) : 'menu_order';
		$ig_include = ( ploption( 'ig_include', $this->oset ) ) ? ploption( 'ig_include', $this->oset ) : false;
		$ig_exclude = ( ploption( 'ig_exclude', $this->oset ) ) ? ploption( 'ig_exclude', $this->oset ) : false;
		$this->max_width = ( ploption( 'ig_width', $this->oset ) ) ? ploption( 'ig_width', $this->oset ).'px' : '600px';
		$query = array(
			'post_status' => null,
			'post_type' => 'attachment',
			'orderby' => $ig_orderby,
			'order'=> $ig_order,
			'post_mime_type' => 'image',
			'post_parent' => $post->ID,
			'numberposts' => $ig_numposts,
		);
		if ( $ig_exclude )
			$query['exclude'] = $ig_exclude;
		if ( $ig_include )
			$query['include'] = $ig_include;
		$ig_images = get_posts( $query );
		if ( !is_array( $ig_images ) || empty( $ig_images ) || count( $ig_images ) <= 2 ) {
			echo setup_section_notify( $this, __( 'Not enough images in page media library.', 'ImageGrid' ), null, 'Upload Media' );
			return;
		}
		if ( $ig_images ) {
			$this->ig_draw_images( $ig_images );
		}
	}

	function ig_draw_images( $ig_images ) {

		$clone_id = $this->get_the_id();

		?>
			<div class="pl-ig-container">
				<div id="ig-images-container" class="ig-images-container js-masonry" data-masonry-options='{ "itemSelector": ".ig-image-container" }'>
					<?php
						$ig_cols = ( ploption( 'ig_cols', $this->oset ) ) ? ploption( 'ig_cols', $this->oset ) : 'ig-col-3';
						$i = -1;
						foreach ( $ig_images as $ig_image ) {

							$attachment_meta = $this->ig_get_attachment($ig_image->ID);

							$i++;
							$ig_image_number = sprintf('%s', $i);

							?>

								<div class="ig-image-container <?php echo $ig_cols; ?>">
									<figure class="ig-cap-bot">
										<a href="" class="ig-image" data-slide-index="<?php echo $ig_image_number; ?>" data-toggle="modal">

											<?php

												$ig_description = $attachment_meta['description'] ? $attachment_meta['description'] :'There is no description for this image!';

												if ($ig_description) {
													$ig_figcaption = printf('<figcaption class="ig-overlay-container"><div>%s</div></figcaption>', $ig_description );
												} else {
													$ig_figcaption = '';
												}

												printf(
													'<img src="%s" alt="%s" />',
													$this->ig_get_attachment_large_url( $ig_image->ID),
													get_post_meta( $ig_image->ID, '_wp_attachment_image_alt', true ),
													$ig_figcaption
												);
											?>
										</a>
									</figure>
								</div>
							<?php

						}

					?>

					<div id="ig_modal<?php echo $clone_id; ?>" class="modal fade hide ig-modal">
						<div id="ig_carousel" class="carousel">
							<div class="carousel-inner">
								<?php
									$i = -1;
									foreach ( $ig_images as $ig_image ) {
									$attachment_meta = $this->ig_get_attachment($ig_image->ID);
									if($i==0) {
											$active = 'active ';
										} else {
											$active = '';
										}
											$i++;
										?>
											<div class="<?php echo $active ?>item">
												<div class="modal-header">
													<button type="button" class="close" data-dismiss="modal">×</button>
													<h3><?php echo $attachment_meta['title'] ? $attachment_meta['title'] :'There is no title for this image!'; ?></h3>
												</div>
												<div class="modal-body">
													<?php
														printf(
															'<img src="%s" alt="%s" class="center" />',
																wp_get_attachment_url($ig_image->ID, 'full', false,''),
																get_post_meta( $ig_image->ID, '_wp_attachment_image_alt', true )
														);
													?>
												</div>
												<div class="modal-footer"><?php echo $attachment_meta['caption'] ? $attachment_meta['caption'] :'There is no caption for this image!'; ?></div>
											</div>
										<?php
									}
								?>
							</div>
							<a class="carousel-control left" href="#ig_carousel" data-slide="prev">&lsaquo;</a>
							<a class="carousel-control right" href="#ig_carousel" data-slide="next">&rsaquo;</a>
						</div>
					</div>
				</div>
			</div>

		<?php

	}

	function ig_get_attachment_large_url($id){
		$large_array = image_downsize( $id, 'large' );
		$large_path = $large_array[0];
		return $large_path;

	}

	function ig_get_attachment( $attachment_id ) {
		$attachment = get_post( $attachment_id );
		return array(
			'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
			'caption' => $attachment->post_excerpt,
			'description' => $attachment->post_content,
			'href' => get_permalink( $attachment->ID ),
			'src' => $attachment->guid,
			'title' => $attachment->post_title
		);

	}

	function section_persistent(){

		add_action('template_redirect', array($this, 'ig_shortcode_imagegrid'));

	}

	function ig_shortcode_imagegrid() {

		add_shortcode('imagegrid', array(&$this,'ig_shortcode_markup' ));

	}

	function ig_shortcode_markup() {

		ob_start();
		$this->ig_draw_container();
		$output = ob_get_clean();

		return $output;

	}
}
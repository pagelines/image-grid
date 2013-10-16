<?php
/*
	Section: Image Grid
	Author: Aleksander Hansson
	Author URI: http://ahansson.com
	Demo: http://imagegrid.ahansson.com
	Description: A responsive Image Grid.
	Class Name: ImageGrid
	Filter: gallery
	Cloning: true
	v3: true
*/

class ImageGrid extends PageLinesSection {

	var $tabID = 'image_grid_meta';

	function section_styles() {

		wp_enqueue_script( 'jquery' );

		wp_enqueue_script( 'imagesloaded', $this->base_url.'/js/imagesloaded.pkgd.min.js');

		wp_enqueue_script( 'masonry', $this->base_url.'/js/masonry.pkgd.min.js' );

		wp_enqueue_script( 'jquery-requestAnimationFrame', $this->base_url.'/js/ilightbox.packed.js' );

		wp_enqueue_script( 'jquery-mousewheel', $this->base_url.'/js/jquery.mousewheel.js' );

		wp_enqueue_script( 'ilightbox', $this->base_url.'/js/ilightbox.packed.js' );

	}

	function section_foot(){

		$ig_lightbox_theme = ( $this->opt( 'ig_lightbox_theme' ) ) ? $this->opt( 'ig_lightbox_theme' ) : 'smooth';

		$clone_id = $this->get_the_id();

		?>

			<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery('.ig-gallery-<?php echo $clone_id; ?>').iLightBox({
					  	skin: '<?php echo $ig_lightbox_theme; ?>'
					});
				});
			</script>


		<?php
	}

	function section_head() {

		$clone_id = $this->get_the_id();

		?>

			<script type="text/javascript">
				jQuery(document).ready(function(){
					var ig_modal<?php echo $clone_id; ?> = jQuery('#ig_modal<?php echo $clone_id; ?>').modal({show: false});
					var ig_carousel = jQuery('#ig_carousel').carousel({'interval': false});

					jQuery('.ig-image').each(function() {
						var index = jQuery(this).data('slide-index');
						jQuery(this).click(function() {
							ig_modal<?php echo $clone_id; ?>.modal('show');
							ig_carousel.carousel(index);
						});
					});

				});
				jQuery(document).ready(function(){
					jQuery('.ig-images-container').imagesLoaded( function() {
						jQuery('.ig-images-container').masonry();
					});
				});


			</script>

		<?php

	}

	function section_template( ) {

		$ig_shortcode_override = ( $this->opt( 'ig_shortcode_override' ) ) ? $this->opt( 'ig_shortcode_override' ) : false;
		if ( $ig_shortcode_override == false ) {
			$this->ig_draw_container();
		}
	}

	function ig_draw_container( ) {

		$clone_id = $this->get_the_id();

		global $post;
		$ig_numposts = ( $this->opt( 'ig_numposts' ) ) ? $this->opt( 'ig_numposts' ) : -1;
		$ig_order = ( $this->opt( 'ig_order' ) ) ? $this->opt( 'ig_order' ) : 'ASC';
		$ig_orderby = ( $this->opt( 'ig_orderby' ) ) ? $this->opt( 'ig_orderby' ) : 'menu_order';
		$ig_include = ( $this->opt( 'ig_include' ) ) ? $this->opt( 'ig_include' ) : false;
		$ig_exclude = ( $this->opt( 'ig_exclude' ) ) ? $this->opt( 'ig_exclude' ) : false;
		$this->max_width = ( $this->opt( 'ig_width' ) ) ? $this->opt( 'ig_width' ).'px' : '600px';
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
			echo setup_section_notify( $this, __( 'Not enough images in page media library.', 'image-grid' ), null, 'Setup Image Grid' );
			return;
		}
		if ( $ig_images ) {
			$this->ig_draw_images( $ig_images );
		}
	}

	function ig_draw_images( $ig_images ) {

		$clone_id = ( $this->get_the_id() ) ? $this->get_the_id(): 'shortcode';

		?>
			<div class="pl-ig-container">
				<div id="ig-images-container" class="ig-images-container js-masonry" data-masonry-options='{ "itemSelector": ".ig-image-container" }'>
					<?php
						$ig_cols = ( $this->opt( 'ig_cols' ) ) ? $this->opt( 'ig_cols' ) : 'ig-col-3';
						$i = -1;
						foreach ( $ig_images as $ig_image ) {

							$attachment_meta = $this->ig_get_attachment($ig_image->ID);

							$title = $attachment_meta['title'] ? $attachment_meta['title'] :'';
							$ig_description = $attachment_meta['description'] ? $attachment_meta['description'] :'';
							$ig_caption = $attachment_meta['caption'] ? $attachment_meta['caption'] :'';
							$alt = $attachment_meta['alt'] ? $attachment_meta['alt'] :'';

							$ig_sign = ( $this->opt( 'ig_sign' ) ) ? $this->opt( 'ig_sign' ) : 'questionmark';

							if (! $attachment_meta['description'] ) {
								$ig_sign = 'none';
							}

							$i++;
							$ig_image_number = sprintf('%s', $i);

							printf('<a href="%s" class="ig-gallery-%s ig-image-container %s" data-title="%s" data-caption="%s">',
								$this->ig_get_attachment_large_url( $ig_image->ID),
								$clone_id,
								$ig_cols,
								$title,
								$ig_caption
							);

								?>
									<figure class="ig-cap-bot <?php echo $ig_sign; ?>">

											<?php

												if ($ig_description) {
													$ig_figcaption = printf('<figcaption class="ig-overlay-container"><div>%s</div></figcaption>', $ig_description );
												} else {
													$ig_figcaption = '';
												}

												printf(
													'<img src="%s" alt="%s" />',
													$this->ig_get_attachment_large_url( $ig_image->ID),
													$alt
												);
											?>
									</figure>
								</a>
							<?php
						}

					?>

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

		add_action('template_redirect', array(&$this, 'ig_shortcode_imagegrid'));

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

	function section_opts() {

		$options = array();

		$how_to_use = __( '
		<strong>Read the instructions below before asking for additional help:</strong>
		</br></br>
		<strong>1.</strong> In Drag&Drop, drag Image Grid section to a template of your choice.
		</br></br>
		<strong>2.</strong> Go to the page where you want your image grid.
		</br></br>
		<strong>3.</strong> Upload an image gallery to your page (You have to upload images - you cannot choose images that are not uploaded to the specific post or page).
		</br></br>
		<strong>4.</strong> Type in the meta information for each image in the gallery, including the title, description, and caption.
		</br></br>
		<strong>5.</strong> By default Image Grid is outputting the image grid in the section area but you can use the shortcode &#91;imagegrid&#93; to output Image Grid in your post or page. (If you choose to use the shortcode you can disable the section output).
		</br></br>
		<div class="tac zmb"><strong>Video Walktrough</strong></div>
		</br>
		[pl_video type="youtube" id="UQT4egpkxQ8"]
		</br></br>
		<div class="row zmb">
				<div class="span6 tac zmb">
					<a class="btn btn-info" href="http://forum.pagelines.com/71-products-by-aleksander-hansson/" target="_blank" style="padding:4px 0 4px;width:100%"><i class="icon-ambulance"></i>          Forum</a>
				</div>
				<div class="span6 tac zmb">
					<a class="btn btn-info" href="http://betterdms.com" target="_blank" style="padding:4px 0 4px;width:100%"><i class="icon-align-justify"></i>          Better DMS</a>
				</div>
			</div>
			<div class="row zmb" style="margin-top:4px;">
				<div class="span12 tac zmb">
					<a class="btn btn-success" href="http://shop.ahansson.com" target="_blank" style="padding:4px 0 4px;width:100%"><i class="icon-shopping-cart" ></i>          My Shop</a>
				</div>
			</div>
		', 'image-grid' );

		$options[] = array(
			'key' => 'ig_help',
			'type'     => 'template',
			'template'      => do_shortcode( $how_to_use ),
			'title' =>__( 'How to use:', 'image-grid' ) ,
		);

		$help = 'If using "Custom" you have to add this to your Custom LESS/CSS field:</br></br>
		.ig-images-container {</br>
			&nbsp;&nbsp;&nbsp;&nbsp;.ig-image-container {</br>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;figure.custom {</br>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&:before {</br>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;content:"M"; //Your custom sign</br>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</br>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</br>
			&nbsp;&nbsp;&nbsp;&nbsp;}</br>
		}
		';

		$options[] = array(

			'title' => __( 'Settings', 'image-grid' ),
			'type'	=> 'multi',
			'key' 	=> 'ig_settings',
			'opts'	=> array(

				array(
					'key' 	=> 'ig_shortcode_override',
					'label'	=> __( 'Disable section output (use this if you want to use the [imagegrid] shortcode).', 'image-grid' ),
					'type' 	=> 'select',
					'default' => false,
					'opts' 	=> array(
						true   	=> array( 'name' => "Yes" ),
						false 	=> array( 'name' => "No" ),
					)
				),
				array(
					'key' 	=> 'ig_cols',
					'label' => __( 'Number of columns (4, 3 and 2 is mobile friendly!)', 'image-grid' ),
					'type' 	=> 'select',
					'default' => 'ig-col-3',
					'opts' 	=> array(
						'ig-col-2'   => array( 'name' => "2" ),
						'ig-col-3'   => array( 'name' => "3" ),
						'ig-col-4'   => array( 'name' => "4" ),
						'ig-col-5'   => array( 'name' => "5" ),
						'ig-col-6'   => array( 'name' => "6" ),
						'ig-col-7'   => array( 'name' => "7" ),
						'ig-col-8'   => array( 'name' => "8" ),
						'ig-col-9'   => array( 'name' => "9" ),
						'ig-col-10'  => array( 'name' => "10" ),
					)
				),
				array(
					'key' 	=> 'ig_order',
					'label' => __( 'Order Type (Default: ASC)', 'image-grid' ),
					'type' 	=> 'select',
					'default' => 'ASC',
					'opts' 	=> array(
						'ASC'	=> array( 'name' => "Ascending Order" ),
						'DESC'	=> array( 'name' => "Descending Order" ),
						)
					),
				array(
					'key' 	=> 'ig_orderby',
					'label' => __( 'Order According To (Default: Menu Order)', 'image-grid' ),
					'type' 	=> 'select',
					'default' => 'menu_order',
					'opts' 	=> array(
						'menu_order' 	=> array( 'name' => __( "Using Add Media Popup (menu order)", 'image-grid' ) ),
						'title'   		=> array( 'name' => __( "Using Image Title", 'image-grid' ) ),
						'post_date'  	=> array( 'name' => __( "Using Post Date", 'image-grid' ) ),
						'rand'   		=> array( 'name' => __( "Random Selection", 'image-grid' ) ),
						'ID'   			=> array( 'name' => __( "Attachment ID", 'image-grid' ) ),
					)
				),
				array(
					'key' 	=> 'ig_numposts',
					'label' => __( 'Maximum Amount of Images (Default: Unlimited; Must be more than 2)', 'image-grid' ),
					'type' 	=> 'text'
				),
				array(
					'key' 	=> 'ig_exclude',
					'label' => __( 'Exclude Attachment IDs - Comma Separated (Default: None)', 'image-grid' ),
					'type' 	=> 'text'
				),
				array(
					'key' 	=> 'ig_include',
					'label' => __( 'Include Attachments IDs - Comma Separated (Default: All)', 'image-grid' ),
					'type' 	=> 'text'
				),

			),
		);

		$options[] = array(

			'title' => __( 'Styling', 'image-grid' ),
			'key' 	=> 'ig_styling',
			'type'	=> 'multi',
			'opts'	=> array(

				array(
					'key' 	=> 'ig_lightbox_theme',
					'label'	=> __( 'Choose from 6 different themes', 'image-grid' ),
					'type' 	=> 'select',
					'default' => 'smooth',
					'opts' 	=> array(
						'dark'   		=> array( 'name' => "Dark" ),
						'light'   		=> array( 'name' => "Light" ),
						'parade'   		=> array( 'name' => "Parade" ),
						'smooth'   		=> array( 'name' => "Smooth" ),
						'metro-black'  	=> array( 'name' => "Metro-Black" ),
						'metro-white'	=> array( 'name' => "Metro-White" ),
						'mac' 			=> array( 'name' => "Mac" ),
					)
				),

				array(
					'key' 	=> 'ig_sign',
					'label' => __( 'Choose sign', 'image-grid' ),
					'type' 	=> 'select',
					'help' 	=> $help,
					'default' => 'questionmark',
					'opts' 	=> array(
						'questionmark'   => array( 'name' => "?" ),
						'iletter'   => array( 'name' => "i" ),
						'custom'   => array( 'name' => "Custom" ),
						'none'   => array( 'name' => "None" )
					)
				),
			),
		);

	return $options;

	}
}
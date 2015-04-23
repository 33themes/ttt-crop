<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
*/
?><!DOCTYPE html>
<!--[if IE 6]>
<html id="ie6" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 7]>
<html id="ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html id="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>

	<script type="text/javascript">
	addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
	var ajaxurl = '/wp-admin/admin-ajax.php',
		pagenow = 'ttt-crop',
		typenow = '',
		adminpage = 'ttt-crop',
		thousandsSeparator = '.',
		decimalPoint = ',',
		isRtl = 0;
	</script>
	<?php

	global $title, $hook_suffix, $current_screen, $wp_locale, $pagenow, $wp_version,
	        $current_site, $update_title, $total_update_count, $parent_file;

	//do_action('admin_enqueue_scripts', $hook_suffix);
	do_action("admin_print_styles-$hook_suffix");
	do_action('admin_print_styles');
	do_action("admin_print_scripts-$hook_suffix");
	do_action('admin_print_scripts');

	?>

	<?php wp_print_styles(); ?>
	<?php wp_print_scripts(); ?>
</head>
<body id="tttcrop"  class="wp-core-ui">
	<div id="debug">
	</div> <!-- #debug -->


	<div id="editor">
		<div class="col orig">
			<img src="" id="target" alt="Flowers" />
		</div>
		<div class="col prev">
			<div style="overflow:hidden;" class="preview-area">
				<img src="" id="preview" alt="Preview" class="jcrop-preview" />
			</div>
		</div>
	</div> <!-- #editor -->

	<div class="media-frame-toolbar">
		<div class="media-toolbar">
			<div class="media-toolbar-secondary">
				<div class="media-selection">
					<div class="selection-info">
						<span class="count">Select to edit</span>
					</div>
					<div class="selection-view">
						<ul id="sizes" class="attachments ui-sortable">
							<?php
							function local_image_sizes() {
								global $_wp_additional_image_sizes;

								foreach( array('thumbnail','medium','large') as $size ) {

									if ( !isset($_wp_additional_image_sizes[ $size ]) ) {
										$_wp_additional_image_sizes[ $size ] = array(
											'crop'=>true,
											'width'=>get_option( $size.'_size_w'),
											'height'=>get_option( $size.'_size_h' ),
											'default'=>true
										); 
									}
								}

								if ( $_custom_sizes = apply_filters('tttcrop_image_sizes',$_wp_additional_image_sizes) )
									$_wp_additional_image_sizes = $_custom_sizes;
								
								if (!is_array($_wp_additional_image_sizes)) return false;

								return $_wp_additional_image_sizes;
							}
							?>
							<?php foreach( local_image_sizes() as $name => $size ): ?>

								<?php

								if(preg_match('/\s+/', $_REQUEST['type'])) {
									if ( $_REQUEST['type'] != $name ) continue;
								}
								if ( isset($size['default']) ) {
									$image_attributes = wp_get_attachment_image_src( $_REQUEST['id'] , array(
										get_option( $name.'_size_w'), get_option( $name.'_size_h')
									));

									$size['width'] = $image_attributes[1];
									$size['height'] = $image_attributes[2];
								}
								else {
									$image_attributes = wp_get_attachment_image_src( $_REQUEST['id'] , $name );
									// $size['width'] = $image_attributes[1];
									// $size['height'] = $image_attributes[2];
								}

								?>
								<li class="attachment selection selected">
									<div class="size attachment-preview <?php echo ( $size['crop'] == false ? 'crop' : '' ); ?>" data-src="<?php echo $image_attributes[0]; ?>" data-width="<?php echo $size['width']; ?>" data-height="<?php echo $size['height'];?>" data-resize="<?php echo ( $size['crop'] == false ? 1 : 0 );?>" data-attachementid="<?php echo $_REQUEST['id'];?>" data-namesize="<?php echo $name; ?>">
										<div class="info">
											<h3><?php echo apply_filters('ttt_crop_human_name',$name); ?></h3>
											<p><?php echo __('Size','ttt-crop'); ?> <strong><?php echo $size['width'];?>x<?php echo $size['height'];?></strong></p>
											<?php if ($size['crop'] == false): ?>
												<p><?php echo __('It is only for resize and zoom, not crop','ttt-crop'); ?></p>
											<?php endif; ?>
										</div>
										<div class="background"><div class="img" style="background-image: url(<?php echo $this->view_cache_check( $_REQUEST['id'], $name, 90, 90 ); ?>);"></div></div>
									</div>
								</li>
							<?php endforeach; ?>
						</ul> <!-- #sizes --> 
					</div> <!-- .selection-view -->
				</div> <!-- .media-selection -->
			</div> <!-- .media-tollbar-secondary -->
				
			<div class="media-toolbar-primary">
				<?php if ($_REQUEST['src']): ?>
					<a class="button media-button button-primary button-large media-button-insert submit" href="#">Update Thumbnail</a>
				<?php else : ?>
					<a class="button media-button button-primary button-large media-button-insert" href="javascript:TTTCropEd.insert(TTTCropEd.local_ed)">Insert</a>
				<?php endif; ?>
			</div> <!-- .media-toolbar-primary -->
		</div> <!-- .media-toolbar -->
	</div> <!-- .media-frame-toolbar -->
</body>
</html>

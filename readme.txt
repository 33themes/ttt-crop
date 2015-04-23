=== TTT Crop ===
Contributors: 33themes, gabrielperezs, lonchbox, tomasog
Tags: images, thumbnail, media editor, edit media, image sizes
Requires at least: 3.7
Tested up to: 4.1.2
Stable tag: 0.1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html 

Edit all generated thumbnails images crop area in a simple way.


== Description ==

This is an easy and fast way to crop any uploaded image in your wordpress. No more complicate graphical editors, photos of people without head or products with wrong view. Select the thumbnail, edit the crop area and save a new thumbnail image.

This plugin dones´t create new file or folder, when it save the new crop area rewrite the original thumbnail file, this means will not break the theme design :)

= Features =

* The plugin automatically use all images created with the function _add_image_size_ even if they are hard crop or proportional. More info in http://codex.wordpress.org/Function_Reference/add_image_size#Crop_Mode
* "Crop Editor" quick link in media list.
* "Crop Editor" link in featured image widget.
* "Crop Editor" Button inside file details in media manager.
* **GEM** Now when you add an image into the editor you to choose and insert any thumbnail size your theme or plugin register into the system.

= Recomendations =

If you are a developer and need to rebuild the thumbnails we recommend use this plugins http://wordpress.org/plugins/ajax-thumbnail-rebuild/ it help you to do it one at a time. IMPORTANT: Remember that rebuild thumb will rewrite the thumbnail you croped before with the TTT Crop plugin.

= Contributions =

You can send issues, ideas, etc.. to github the repo https://github.com/33themes/ttt-crop



== Screenshots ==

1. Typical problem with cropped image.
2. Go to medias and open the images, and click in TTT Crop.
3. Choose the image you need, and crop in the area you want.
4. You can crop from featured box

== Hacks ==

Just copy&paste this code into your functions.php

**Remove sizes from the editor**

`add_filter( 'tttcrop_image_sizes', 'custom_tttcrop_image_sizes');
function custom_tttcrop_image_sizes( $sizes ) {
	unset( $sizes['thumbnail'] );
	unset( $sizes['large'] );
	return $sizes;
}
?>`

This hack remove the thumbnail from TTT-Crop Editor tool.



**Remove thumbnails sizes from the editor for an specific post type**

`function custom_tttcrop_image_sizes_CPT($sizes) {

    foreach ($sizes as $key => $values) {
        if ($key == 'THUMBNAIL-SIZE1')
            $new[ $key ] = $values;
        elseif ($key == 'THUMBNAIL-SIZE1')
            $new[ $key ] = $values;            
    }

    return $new;
}`

Because we use only two or three thumbnails in a Custom Post Type.



**Change the name of the image to human names**

`function local_ttt_crop_human_name($name) {
    switch( $name ) {
        case 'single-slider';
            return 'Home slider image'; break;
        case 'widget-thumbnail';
            return 'Widget small image'; break;

        default:
            return $name; break;
    }
}
add_filter('ttt_crop_human_name','local_ttt_crop_human_name');`

This will change the name of the image inside the tooltip when it is selected.




== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `ttt-crop` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Contributors ==

Follow us on https://github.com/33themes/ttt-crop

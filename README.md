
TTT Crop
========

* Contributors: 33themes, gabrielperezs, lonchbox, tomasog
* Tags: images, thumbnail, media editor, edit media, image sizes
* Requires at least: 3.7
* Tested up to: 4.1.2
* Stable tag: 0.1.3
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html 


Edit all generated thumbnails images crop area in a simple way.


Description
===========

This is an easy and fast way to crop any uploaded image in your wordpress. No more complicate graphical editors, photos of people without head or products with wrong view. Select the thumbnail, edit the crop area and save a new thumbnail image.

This plugin dones´t create new file or folder, when it save the new crop area rewrite the original thumbnail file, this means will not break the theme design :)

Available in WordPress.org
==========================

Yes, it is, here: https://wordpress.org/plugins/ttt-crop/


Features
========

* The plugin automatically use all images created with the function _add_image_size_ even if they are hard crop or proportional. More info in http://codex.wordpress.org/Function_Reference/add_image_size#Crop_Mode
* "Crop Editor" quick link in media list.
* "Crop Editor" link in featured image widget.
* "Crop Editor" Button inside file details in media manager.
* *GEM* Now when you add an image into the editor you to choose and insert any thumbnail size your theme or plugin register into the system.


Screenshots
===========

Typical problem with cropped image.

![How it works](/screenshots/screenshot-1.png)

Go to medias and open the images, and click in TTT Crop.

![Save](/screenshots/screenshot-2.png)

Choose the image you need, and crop in the area you want.

![Edit image](/screenshots/screenshot-3.png)

You can crop from featured box
![Edit featured image](/screenshots/screenshot-4.png)

Hacks
=====

Just copy&paste this code into your functions.php

*Remove sizes from the editor*

You can remove some sizes from the TTT Crop thumbnail editor with this code in your functions.php. 

Example code:

```php
    add_filter( 'tttcrop_image_sizes', 'custom_tttcrop_image_sizes');
    function custom_tttcrop_image_sizes( $sizes ) {
        unset( $sizes['thumbnail'] );
        unset( $sizes['large'] );
        return $sizes;
    }
```

*Set human names for the thumbnail names*

For example, the name "author-thumbnail" could be "Author Image" 


```php
    function local_ttt_crop_human_name($name) {
        switch( $name ) {
            case 'single-slider';
                return 'Home slider image'; break;
            case 'author-thumbnail';
                return 'Author Image'; break;

            default:
                return $name; break;
        }
    }
    add_filter('ttt_crop_human_name','local_ttt_crop_human_name');
```

*Show some of the sizes of one post_type*

In this example you existe the custom post type "author" and two image sizes
for the author content (single, archive, etc..). And you want to show only this
two image sizes on the crop screen. 

The idea is make more simple for the end user, don't show all the sizes, only
the sizes the user need.

```php
    add_action('init','custom_tttgallery_init',0);

    function custom_tttgallery_init() {
        global $pagenow;
        
        if ( 'admin-ajax.php' == $pagenow && $_REQUEST['action'] == 'ttt-crop_load' && isset($_REQUEST['post_id']) ) {
            if ( $post_type == 'author') {
                add_filter('tttcrop_image_sizes','custom_tttcrop_image_sizes_author');
            }
        }
    }

    function custom_tttcrop_image_sizes_author($sizes) {
        foreach ($sizes as $key => $values) {
            if ($key == 'author-thumbnail')
                $new[ $key ] = $values;
            elseif ($key == 'author-slider')
                $new[ $key ] = $values;
        }
        return $new;
    }
```


Recomendations
==============

If you are a developer and need to rebuild the thumbnails we recommend use this plugins http://wordpress.org/plugins/ajax-thumbnail-rebuild/ it help you to do it one at a time. IMPORTANT: Remember that rebuild thumb will rewrite the thumbnail you croped before with the TTT Crop plugin.

Installation
============

This section describes how to install the plugin and get it working.

e.g.

1. Upload `ttt-crop` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

<?php

class TTTCrop_Admin extends TTTCrop_Common {
    function __construct() {
        parent::__construct();
    }

    function init() {
        if( current_user_can('edit_posts') ) {
            $this->activation_check();
            $this->ajax();
            $this->cssjs();
            add_action('admin_menu', array( &$this, 'menu' ));
            add_filter('attachment_fields_to_edit', array( &$this, 'media_edit_button'), 10, 2);
            add_filter('admin_post_thumbnail_html', array( &$this, 'featured_link'), 10, 2 );
            add_filter('media_row_actions', array( &$this, 'media_library_action' ), 10, 2);
            add_filter('image_size_names_choose', array( &$this, 'insert_custom_image_sizes') );
        }
    }

    function insert_custom_image_sizes( $sizes ) {
        global $_wp_additional_image_sizes;
        if ( empty( $_wp_additional_image_sizes ) )
            return $sizes;
            
        foreach ( $_wp_additional_image_sizes as $id => $data ) {
            if ( !isset($sizes[$id]) )
                $sizes[$id] = ucfirst( str_replace( '-', ' ', $id ) );
        }
        
        return $sizes;
    }

    /*
        Button in editor
    */

    function media_edit_button( $form_fields, $post ) {


        $form_fields["tttcrop-edit-image"] = array(
            "label" => __("Thumbnails", parent::sname ),
            "input" => "html",
            //"html" => "<p><a class='button-secondary' id='tttcrop' href='".tttcrop_href( $post->guid )."'>" . __("Edit image", parent::sname ) . "</a></p>",
            "html" => '<p><a class="button-secondary" id="ttt-crop-createiframe" onclick="TTT_Crop_Edit(\''.$this->iframe_ref( $_REQUEST['post_id'], $post->ID ).'\')" href="#">' . __("Crop Editor", parent::sname ) . '</a></p>',
            //"helps" => __("Edit thumbnails", parent::sname )
        );
        
        return $form_fields;
    }



    function media_library_action($actions, $post) {
        /* Almost sure this is not necessary. Just in case... */
        global $current_screen;
        if ( 'upload' != $current_screen->id ) 
            return $actions; 

        // relative path/name of the file
        //$the_file = str_replace(WP_CONTENT_URL, '.', $post->guid);
        
        // adding the Action to the Quick Edit row
        $actions['ttt-crop-createiframe'] = '<a class="ttt-crop-createiframe" href="'.$this->iframe_ref( false, $post->ID ).'">'.__('Crop Editor', parent::sname ).'</a>';
        
        return $actions;    
    }


    function iframe_ref( $post_id = false, $thumbnail_id = false ) {

        if ( !$thumbnail_id )
            $thumbnail_id = get_post_thumbnail_id( $post_id );

        $src = wp_get_attachment_image_src( $thumbnail_id );
        return admin_url('admin-ajax.php').'?'.http_build_query(array(
            'action' => 'ttt-crop_load',
            'src' => $src[0],
            'id' => $thumbnail_id,
            'post_id' => $post_id
        ));
    }



    function featured_link($content = false, $post = false ) {
        //global $thumbnail_id, $content_width, $_wp_additional_image_sizes, $post_ID, $post;

        if ( preg_match('/media-upload\.php\?post_id=(\d+)/',$content,$regs) ) {
            $_thumbnail_id = (int) $regs[1];
        }
        else
            return $content;

        if (preg_match('/<img ([^\>]+)>/',$content,$regs)) {
            $params = $regs[1];
            preg_match('/src=[\"\']([^\'\"]+)[\"\']/',$params,$regs);

            $link = '<a class="ttt-crop-createiframe" href="'.$this->iframe_ref( $_thumbnail_id ).'">'.__('Crop editor', parent::sname ).'</a>';
        }
        $js = '<script type="text/javascript">TTT_Crop_AddClick();</script>';

        return $content." ".$link.$js;
    }


    

    /*
        Ajax
    */

    function ajax() {
        add_action('wp_ajax_ttt-crop_load', array( &$this, 'load_callback' ) );
        add_action('wp_ajax_ttt-crop_view', array( &$this, 'view_callback' ) );
        add_action('wp_ajax_ttt-crop_save', array( &$this, 'save_callback' ) );
        add_action('wp_ajax_ttt-crop_read', array( &$this, 'read_callback' ) );
    }

    function load_callback() {
        wp_enqueue_style( 'wp-admin' );
        wp_enqueue_style( 'buttons' );
        wp_enqueue_style( 'media-views' );
        wp_enqueue_style( 'jcrop' );
        wp_enqueue_style( 'ttt-crop-editor-css', plugins_url('template/admin/css/editor.css' , dirname(__FILE__) ) );

        wp_enqueue_style( 'jquery-ui-core');
        wp_enqueue_style( 'jquery-effects-shake');
        wp_enqueue_style( 'wp-pointer' );

        wp_enqueue_script( 'jcrop' );

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-effects-core' );
        wp_enqueue_script( 'jquery-effects-bounce');
        wp_enqueue_script( 'jquery-effects-shake');
        wp_enqueue_script( 'jquery-effects-highlight');
        wp_enqueue_script( 'wp-pointer', array('jquery') );
        wp_enqueue_script( 'ttt-querystring', plugins_url('template/admin/js/jquery.querystring.js' , dirname(__FILE__) ), array( 'jquery')  );
        wp_enqueue_script( 'ttt-crop-editor-js', plugins_url('template/admin/js/editor.js' , dirname(__FILE__) ), array( 'jquery')  );


        wp_localize_script( 'ttt-crop-editor-js', 'TTT_Crop_Editor', array(
            'ajax' => admin_url('admin-ajax.php'),
            'Nonce' => wp_create_nonce( 'ttt-crop-editor-nonce' ),
            'lere' => 'test',
        ) );

        require_once( dirname(__FILE__).'/../template/admin/iframe.inc.php' );
        exit;
    }

    function view_cache_check( $_id, $sizename, $width, $height  ) {
        $size = wp_get_attachment_image_src($_id, $sizename, true);
        $file = $this->_realfile_from_path( $size[0] );

        $path = '/wp-content/ttt-crop/'.sha1( $file ).'-'.$width.'x'.$height.'.png';

        $cache = ABSPATH . $path;

        if ( !is_file($cache) ) {
            $img = $this->resize( $file, $width, $height );
            //imagejpeg($img,$cache,80);
            imagesavealpha($img,true); 
            imagepng($img,$cache);
            imagedestroy($img);            
        }

        return get_site_url().$path.'?anticache='.rand(0,9999999);
    }

    function view_cache( $file, $attrs, $data = false ) {

        if ( !is_dir( ABSPATH.'/wp-content/ttt-crop' ) )
            mkdir( ABSPATH.'/wp-content/ttt-crop' );

        $filetime = filemtime( $file );

        $cache = ABSPATH.'/wp-content/ttt-crop/'.sha1( $file ).'-'.$attrs['_vw'].'x'.$attrs['_vh'].'.png';

        if ( $data == false ) {
            if (!is_file($cache)) return $cache;
            $cachetime = filemtime( $cache );
            if ( $filetime > $cachetime ) return $cache;
            
            // header("HTTP/1.0 200");
            header('Content-Type: image/png');
            header('Content-Length: ' . filesize($cache));
            header('X-TTT-Crop-cache: true');
            ob_clean();
            flush();
            readfile($cache);
            die();
        }

        return $cache;
    }



    function view_callback() {
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $realfile = $this->_realfile_from_path( $_REQUEST['src'] );
        
        $cachefile = $this->view_cache( $realfile, $_REQUEST );


        $resize = $this->resize( $realfile, $_REQUEST['_vw'], $_REQUEST['_vh'] );

        header("HTTP/1.0 200");
        //header('Content-Type: image/jpeg');
        //imagejpeg($resize,$cachefile,80);
        imagesavealpha($resize,true); 
        imagepng($resize,$cachefile);
        imagedestroy($resize);

        $this->view_cache( $realfile, $_REQUEST );
    }

    function resize( $realfile, $width_final, $height_final ) {
        
        if ( preg_match('/\.(jpg|jpeg)$/i',$realfile) )
            $im = imagecreatefromjpeg( $realfile );
        elseif ( preg_match('/\.(png)$/i',$realfile) )
            $im = imagecreatefrompng( $realfile );
        elseif ( preg_match('/\.(gif)$/i',$realfile) )
            $im = imagecreatefrompng( $realfile );
        elseif ( preg_match('/\.(bmp)$/i',$realfile) )
            $im = imagecreatefromwbmp( $realfile );
        else
            $im = imagecreatefromstring( file_get_contents( $realfile ) );

        if ($im === false) {
            echo 'Error';
            exit;
        }

        $width = imagesx($im);
        $height = imagesy($im);

        $ratio = ( $height_final * 100 / $height );

        $width_new = round( $width * $ratio /100 );
        $height_new = round( $height * $ratio /100 );

        if ($width_new > $width_final ) {
            
            $ratio = ( $width_final * 100 / $width );

            $width_new = round( $width * $ratio /100 );
            $height_new = round( $height * $ratio /100 );
        }

        $resize = imagecreatetruecolor(  $width_new, $height_new );
        imagealphablending($resize, true); 
        $transparent = imagecolorallocatealpha( $resize, 0, 0, 0, 127 ); 
        imagefill( $resize, 0, 0, $transparent ); 

        $dst_x = 0;
        $dst_y = 0;
        $src_x = 0;
        $src_y = 0;
        $dst_w = $width_new;
        $dst_h = $height_new;
        $src_w = $width;
        $src_h = $height;


        $d = imagecopyresampled($resize, $im, $dst_x , $dst_y , $src_x , $src_y ,  $dst_w , $dst_h , $src_w , $src_h );

        return $resize;
    }


    function save_callback() {
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("HTTP/1.0 200");
        header('Content-Type: text/plain');

        $html = stripslashes($_REQUEST['_file']);
        if (preg_match('/(http|https):\/\/([^"\']+)/i',$html,$regs)) {
            $url = $regs[1].'://'.$regs[2];
        }

        $url = stripslashes($_REQUEST['_src']);

        $parse_url = parse_url($url);
        $path = $parse_url['path'];
        $pathinfo = pathinfo($path);


        $fileext = 'jpg';
        $origfile = $this->_realfile_from_path( $_REQUEST['_src'] );
        if (preg_match('/.*(jpg|jpeg|png)$/i',$origfile,$regs)) {
            $fileext = $regs[1];
        }

        $filename = $pathinfo['filename'].'-'.$_REQUEST['_fw'].'x'.$_REQUEST['_fh'].'.'.$fileext;
        $realfile = $this->get_dir_from_file( $origfile ).'/'.$filename;
        $destfile = $this->get_path_from_file( $realfile ).'/'.$filename;
        $desturl = $parse_url['scheme'].'://'.$parse_url['host'].$destfile;

        // var_dump(array(
        //     'origfile' => $origfile,
        //     'filename' => $filename,
        //     'realfile' => $realfile,
        //     'destfile' => $destfile,
        //     'desturl' => $desturl,
        //     'path' => $path,
        //     'wpbase' => wp_upload_dir()
        // ));
        // die();

        if (!is_file($origfile)) {
            $im = imagecreate( $_REQUEST['_fw'], $_REQUEST['_fh'] );
        }
        else {
            if ( preg_match('/\.(jpg|jpeg)$/i',$origfile) )
                $im = imagecreatefromjpeg( $origfile );
            elseif ( preg_match('/\.(png)$/i',$origfile) )
                $im = imagecreatefrompng( $origfile );
            elseif ( preg_match('/\.(gif)$/i',$origfile) )
                $im = imagecreatefrompng( $realfile );
            elseif ( preg_match('/\.(bmp)$/i',$origfile) )
                $im = imagecreatefromwbmp( $origfile );
            else
                $im = imagecreatefromstring( file_get_contents( $origfile ) );
        }

        if ($im === false) {
            echo 'Error';
            exit;
        }
        imageinterlace($im, true);

        $width = imagesx($im);
        $height = imagesy($im);

        $ratio = ( $_REQUEST['_vw'] * 100 / $width );

        if (preg_match('/^(null|0|false)$/i',$_REQUEST['_crop'])) {

            $_auto_height = $_REQUEST['_oWidth'] / ( $width / $height );
            $resize = imagecreatetruecolor($_REQUEST['_oWidth'],$_auto_height);

            $dst_x = 0;
            $dst_y = 0;
            $src_x = 0;
            $src_y = 0;
            $dst_w = (int) $_REQUEST['_oWidth'];
            $dst_h = $_auto_height;
            $src_w = $width;
            $src_h = $height;
        }
        else {
            $resize = imagecreatetruecolor($_REQUEST['_fw'],$_REQUEST['_fh']);
            $dst_x = 0;
            $dst_y = 0;
            $src_x = round( $_REQUEST['x'] * 100 / $ratio );
            $src_y = round( $_REQUEST['y'] * 100 / $ratio );
            $dst_w = $_REQUEST['_fw'];
            $dst_h = $_REQUEST['_fh'];
            $src_w = round( $_REQUEST['w'] * 100 / $ratio );
            $src_h = round( $_REQUEST['h'] * 100 / $ratio );
        }

        imagealphablending($resize, true); 
        $transparent = imagecolorallocatealpha( $resize, 0, 0, 0, 127 ); 
        imagefill( $resize, 0, 0, $transparent ); 

        $d = imagecopyresampled($resize, $im, $dst_x , $dst_y , $src_x , $src_y ,  $dst_w , $dst_h , $src_w , $src_h );
        if ( $d === false) {
            var_dump($d);
            exit;
        }

        if (preg_match('/(jpg|jpeg)/i',$fileext)) {
            $d = imagejpeg($resize,$realfile,90);
        }
        elseif (preg_match('/(png)/i',$fileext)) {
            imagesavealpha($resize,true); 
            $d = imagepng($resize,$realfile,9);
        }

        imagedestroy($im);
        echo $desturl;

        $metadata = wp_get_attachment_metadata( $_REQUEST['attachementid'] );
        $metadata['sizes'][ $_REQUEST['namesize'] ] = array(
            'width' => $dst_w,
            'height' => $dst_h,
            'file' => $filename, 
            'mime-type' => 'image/jpeg'
        );
        
        // $metadata = wp_generate_attachment_metadata( $_REQUEST['attachementid'], $origfile );
        wp_update_attachment_metadata( $_REQUEST['attachementid'], $metadata );
        
        exit;
    }

    function resize_image( $params = false ) {
        
    }

    function read_callback() {
        $url = stripslashes($_REQUEST['src']);

        $parse_url = parse_url($url);
        $path = $parse_url['path'];
        $pathinfo = pathinfo($path);

        $realfile = $this->_realfile_from_path( $_REQUEST['src'] );

        if (!is_file($realfile)) {
            echo "error".$realfile;
            exit;
        }

        list($width, $height, $type, $attributes) = getimagesize( $realfile );

        $this->_ajax_echo( array('width'=>$width,'height'=>$height) );
    }

    function _ajax_echo( $s ) {
        header( "Content-Type: text/plain; charset=utf-8" );
        echo json_encode( (array) $s);
        exit;
    }



    function cssjs() {
        // wp_enqueue_style( 'tttcrop-css', plugins_url('../tttcrop.css', __FILE__ ) );
        // wp_enqueue_script( 'tttcrop-js', plugins_url('../tttcrop.js' , __FILE__ ), array( 'jquery' )  );
        

        wp_enqueue_style('media-views');
        wp_enqueue_style( 'ttt-crop-css', plugins_url('template/admin/css/all.css' , dirname(__FILE__) ) );

        wp_enqueue_script( 'Image cropper' );
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'ttt-crop-js', plugins_url('template/admin/js/ttt-crop.js' , dirname(__FILE__) ), array( 'jquery')  );

        wp_localize_script( 'ttt-crop-js', 'TTT_Crop_conf', array(
            'ajax' => admin_url('admin-ajax.php'),
            'Nonce' => wp_create_nonce( 'ttt-crop-nonce' )
        ) );

    }
    
    function menu() {
        // add_menu_page( 'Booking', __('Booking','tttcrop') , 'administrator', 'tttcrop-menu', array( &$this, 'pagemain' ) );
        // add_submenu_page( 'tttcrop-menu', __('Hotels'), __('Hotels'), 'manage_options', 'tttcrop-hotels', array( &$this, 'hotels' ) ); 
    }

    /**
     * Editor
     */
    
    function get_dir_from_file( $file ) {
        if ( !is_file($file) || !is_readable($file) ) return false;

        $s = explode('/',$file);
        array_pop( $s );
        
        return implode('/',$s);
    }

    function get_path_from_file( $file ) {
        if ( preg_match('/^(.*?)(\/wp-content\/.*?)$/',$file,$regs) ) {
            $basedir = $regs[2];
        }

        $s = explode('/',$basedir);
        array_pop( $s );
        
        return implode('/',$s);
    }

    function _base( $src ) {
        $url = stripslashes( $src );
        
        $parse_url = parse_url($url);
        $path = $parse_url['path'];
        $pathinfo = pathinfo($path);

        $wp_dir = wp_upload_dir();
        if ( preg_match('/^(.*?)(\/wp-content\/.*?)$/',$wp_dir['basedir'],$regs) ) {
            $basedir = $regs[1];
        }

        if ( preg_match('/^.*(\/wp-content\/.*?)$/',$url,$regs) ) {
            $wp_content = $regs[1];
        }

        $realbase = $basedir . $wp_content;
        if ( is_file($realbase) )
            return $realbase;

        // header('Content-type: text/plain');

        // var_dump( $url );
        // var_dump( $basedir );
        // var_dump( $wp_content );
        // var_dump( 'real', $realbase );
        // exit;


        // $realbase = $wp_dir['basedir'].'/../..'.$pathinfo['dirname'];
        // if (!is_dir( $realbase ))
        //     $realbase = $wp_dir['basedir'].'/..'.$pathinfo['dirname'];
        // 
        // if (is_file( $realbase ))
        //     return $realbase;

        // $realbase = $wp_dir['basedir'].'/../..'.$pathinfo['dirname'];
        // if (!is_dir( $realbase ))

        //     header('Content-type: text/plain');
        //     var_dump( $url );
        //     var_dump( $wp_dir );
        //     var_dump( $pathinfo );
        //     var_dump( $realbase );
        //     echo "error: ".$realbase;
        //     exit;
        // 
        // return $realbase;
    }

    function _realfile_from_path( $src ) {
        $url = stripslashes( $src );
       
        $parse_url = parse_url($url);
        $path = $parse_url['path'];
        $pathinfo = pathinfo($path);

        $wp_dir = wp_upload_dir();

        foreach( array('jpg','JPG','png','PNG','jpeg','JPEG') as $ext ) {
            $realfile = $this->_base( $src );
            // .'/'.$pathinfo['filename'].'.'.$ext;
            if (is_file($realfile))
                break;

        }
        
        if (!is_file($realfile)) {
            echo "error: ".$realfile;
            exit;
        }

        return $realfile;

    }

    function on_activation() {
        add_option( parent::sname.'_activation', 1 );
    }
    
    function activation_check() {
        if ( get_option( parent::sname.'_activation' ) ) {
            //* Notice
            delete_option( parent::sname.'_activation');
            add_action( 'admin_notices', array( &$this, 'activation_notice') );
        }
    }
    
    function activation_notice() {
        require_once( dirname(__FILE__).'/../template/admin/activation.inc.php' );
    }

}


?>

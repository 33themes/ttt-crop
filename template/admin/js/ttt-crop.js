
function TTT_Crop_iFrame( frame_src ) {
    
    var html = '';

    html += '<div class="media-frame-title"><h1>Crop Tool</h1></div>';
    html += '<a class="media-modal-close" href="#" title="Close"><span class="media-modal-icon"></span></a>';
    html += '<div class="frame">';
        html += '<iframe src="'+frame_src+'" name="ttt-crop-frame1" id="ttt-crop-frame1" scrolling="no" frameborder="0"></iframe>';
    html += '</div>';

    return html;
}

function TTT_Crop_Edit( src ) {
    var $ = jQuery;
        
    if ( src == undefined ) return false;


    if ( $('.modia-modal-content').length > 0 ) {
        $('.media-modal-content').append('<div id="ttt-crop-frame"></div>');
    }
    else {
        var html = '';
        html += '<div tabindex="0" id="ttt-crop-modal" class="supports-drag-drop">';
            html += '<div class="media-modal wp-core-ui">';
                html += '<div id="ttt-crop-frame"></div>';
            html += '</div>';
            html += '<div class="media-modal-backdrop"></div>';
        html += '</div>';

        $('body').append( html );

        $('#ttt-crop-modal .media-modal-backdrop').click(function() {
            $('#ttt-crop-modal').remove();
        });
    }


    var ttt_crop = $('#ttt-crop-frame');
    ttt_crop.click(function(event) {
        event.preventDefault();
        $('#ttt-crop-modal').remove();
    });
    ttt_crop.html( TTT_Crop_iFrame( src ) );

    return false;
}

function TTT_Crop_AddClick() {
    var $ = jQuery;
    $('.ttt-crop-createiframe').each(function() {
        if ( !$(this).data('tttcrop-done') ) {
            $(this).on('click',function(event) {
                event.preventDefault();
                TTT_Crop_Edit( $(this).attr('href') );
            }).data('tttcrop-done',true);
        }
    });
}

jQuery(document).ready(function($) {
    TTT_Crop_AddClick();
});

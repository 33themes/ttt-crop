var TTTCropEd = {};

jQuery(document).ready(function($) {
//(function($) {
	TTTCropEd = {
		rx: 70,
		ry: 200,
		crop: true,
		ratio: function() { return TTTCropEd.rx / TTTCropEd.ry; },
		preview_ratio: 1,
		local_ed : 'ed',
		coords: {},
		init: function() {
			var query = $.QueryString;
			TTTCropEd.src = query.src;

			$('a.submit').click(function() {
				TTTCropEd.save();
			});
			TTTCropEd.mainStart();
		},
		initMCE: function(ed) {
			TTTCropEd.local_ed = ed;
			TTTCropEd.local_el = $( TTTCropEd.local_ed.selection.getNode() );

			TTTCropEd.src = $( TTTCropEd.local_el ).attr('src');

			TTTCropEd.mainStart();
			
			tinyMCEPopup.resizeToInnerSize();
		},
		mainStart: function() {
			//var w = $('#target').width();
			//var h = $('#target').height();
			var w = $('.col.orig').width();
			var h = w;

			var regex = new RegExp('(.*)-([0-9]+x[0-9]+)\.(jpeg|jpg|gif|png)');
			var result = regex.exec( TTTCropEd.src );
			if (result) {
				TTTCropEd.src = result[1]+'.'+result[3];
			}

			//console.log( TTT_Crop_conf.url+'/view.php?_vh='+h+'&_vw='+w+'&src='+encodeURIComponent(TTTCropEd.src)+'&rand='+Math.random() )

			var urlsrc = TTT_Crop_Editor.ajax+'?action=ttt-crop_view&_vh='+h+'&_vw='+w+'&src='+encodeURIComponent(TTTCropEd.src)+'&rand='+Math.random();
			$('#target, #preview').attr('src', urlsrc );
			
			//console.log( urlsrc, w, h );
		},
		insert: function insertTTTCropEd(ed) {

			// For no crop
			TTTCropEd.coords['_crop'] = TTTCropEd.crop;
			TTTCropEd.coords['_oHeight'] = TTTCropEd.oHeight;
			TTTCropEd.coords['_oWidth'] = TTTCropEd.oWidth;
			
			// Normals
			TTTCropEd.coords['_src'] = TTTCropEd.src;
			TTTCropEd.coords['_fw'] = TTTCropEd.rx;
			TTTCropEd.coords['_fh'] = TTTCropEd.ry;
			TTTCropEd.coords['_vw'] = $('#target').width();
			TTTCropEd.coords['_vh'] = $('#target').height();



			$.get(TTT_Crop_conf.url+'/crop.php', TTTCropEd.coords, function(data) {
				
				var html = '<img src="'+data+'?rand='+Math.random()+'" width="'+TTTCropEd.rx+'" />';
				//$('#debug').html( html );

				html = '<a class="TTTCropEd" href="'+TTTCropEd.src+'">'+html+'</a>'

				//tinyMCEPopup.execCommand('mceRemoveNode', false, null);
				tinyMCEPopup.execCommand('mceReplaceContent', false, html);
				tinyMCEPopup.close();

			});
		},
		save: function() {
	
			TTTCropEd.coords['action'] = 'ttt-crop_save';
			TTTCropEd.coords['rand'] = Math.random();
			TTTCropEd.coords['_src'] = TTTCropEd.src;
			TTTCropEd.coords['_fw'] = TTTCropEd.rx;
			TTTCropEd.coords['_fh'] = TTTCropEd.ry;
			TTTCropEd.coords['_vw'] = $('#target').width();
			TTTCropEd.coords['_vh'] = $('#target').height();
			TTTCropEd.coords['attachementid'] = $('#tttcrop #sizes .details .size').attr('data-attachementid');
			TTTCropEd.coords['namesize'] = $('#tttcrop #sizes .details .size').attr('data-namesize');
			if ( $('#tttcrop #sizes .details .size').attr('data-resize') > 0 ) {
				TTTCropEd.coords['_crop'] = 1;
			}

			$.get( TTT_Crop_Editor.ajax+'?action=ttt-crop_save', TTTCropEd.coords, function(data) {
				var html = '<a target="_blank" href="'+data+'">';
				html += '<img src="'+data+'?rand='+Math.random()+'" width="'+TTTCropEd.rx+'" height="'+TTTCropEd.ry+'">';
				html += '</a>';
				$('#tttcrop #sizes .details .size').trigger('update-thumb', [ data ]);

				lere = $.ajax( data, {
					headers: {
						'cache-control': 'no-cache, must-revalidate',
						'pragma': 'no-cache',
						'expires': 'Fri, 30 Oct 1998 14:19:41 GMT'
					}
				}).done(function( s ) {
					newImage = new Image();
					newImage.src = data;
				});
				//window.parent.closeTTTCropEdFrame();
			});		
		},
		setJcrop: function() {
			// Create variables (in this scope) to hold the API and image size
			var jcrop_api, boundx, boundy;

			if ( TTTCropEd.jcrop_api ) {
				TTTCropEd.jcrop_api.destroy();
			}

			// $('#target').css({
			// 	width: '100%',
			// 	height: '100%',
			// });

			$('#target').Jcrop({
				onChange: updatePreview,
				onSelect: updatePreview,
				//boxWidth: 450,
				boxHeight: 380,
				aspectRatio: TTTCropEd.ratio()
			},function(){
				// Use the API to get the real image size
				var bounds = this.getBounds();
				boundx = bounds[0];
				boundy = bounds[1];
				// Store the API in the jcrop_api variable
				TTTCropEd.jcrop_api = this;
			});

			function calcRatio(y) {

				return Math.round( y / TTTCropEd.preview_ratio )
			}
			
			function updatePreview(c) {

				TTTCropEd.coords = c;

				if (parseInt(c.w) > 0) {
					var rx = TTTCropEd.rx / c.w;
					var ry = TTTCropEd.ry / c.h;
					
					$('#preview').css({
						width: calcRatio(rx * boundx) + 'px',
						height: calcRatio(ry * boundy) + 'px',
						marginLeft: '-' + calcRatio(rx * c.x) + 'px',
						marginTop: '-' + calcRatio(ry * c.y) + 'px'
					});
				}
			};

		}
	};

	// if ( typeof(tinyMCEPopup) != 'undefined' ) {
	// 	tinyMCEPopup.onInit.add(TTTCropEd.initMCE, TTTCropEd);
	// 	document.write('<base href="'+tinymce.baseURL+'" />');
	// }
	// else {
	// 	TTTCropEd.init();
	// }





//jQuery(document).ready(function($) {

	var prew = $('#tttcrop .col').width(),
		preh = $('#tttcrop .col').height();


	$('#tttcrop #sizes').width( ( $('#tttcrop #sizes .size').width() + 20 ) * $('#tttcrop #sizes .size').length );


	$('#tttcrop #sizes .size').bind('click',function() {


		// var regex = new RegExp('([0-9]+)x([0-9]+)');
		// var size = $('.pixel',this).html();
		// var r = regex.exec( size );

		TTTCropEd.crop = true;
		TTTCropEd.rx = Number( $(this).attr('data-width') );
		TTTCropEd.ry = Number( $(this).attr('data-height') );


		if ( $(this).attr('data-resize') > 0 ) {
			
			//console.log( TTT_Crop_conf );

			TTTCropEd.oWidth = TTTCropEd.rx;
			TTTCropEd.oHeight = TTTCropEd.ry;

			// TTTCropEd.rx = $('#target').width();
			// TTTCropEd.ry = $('#target').height();

			// $.getJSON( TTTCropEdUrl+'/read.php', { src: TTTCropEd.src },function(data) {
			// 	TTTCropEd.real = data;
			// });

			TTTCropEd.crop = null;

			$('#editor .prev').fadeOut();

			$.ajax({
				async: false,
				url: TTT_Crop_Editor.ajax,
				dataType: 'json',
				data: { action: 'ttt-crop_read', src: TTTCropEd.src, rand: Math.random() },
				success: function(data) {
					var ratio = 1;
					var w = 0, h = 0;

					//console.log( data.width, data.height );

					//if ( data.width > data.height ) {
						ratio = ( data.width / TTTCropEd.rx );
				    // }
				    // else {
				    // 	ratio = ( data.height / TTTCropEd.ry );
				    // }

					TTTCropEd.rx = Math.round( data.width / ratio );
					TTTCropEd.ry = Math.round( data.height / ratio );

					//console.log( ratio, TTTCropEd.rx, TTTCropEd.ry );
				}
			});
		}
		else {
			$('#editor .prev').fadeIn();
		}

		TTTCropEd.preview_ratio = 1;

		if ( TTTCropEd.rx > prew ) {
			TTTCropEd.preview_ratio = TTTCropEd.rx / prew ;
		}
		else if ( TTTCropEd.ry > preh ) {
			TTTCropEd.preview_ratio = ( TTTCropEd.ry / preh ) + 0.5 ;
		}

		//console.log('Preview', TTTCropEd.preview_ratio );
		

		//console.log( TTTCropEd.preview_ratio );

		$('#tttcrop .preview-area').css({
			width: Math.round( TTTCropEd.rx / TTTCropEd.preview_ratio ) ,
			height: Math.round( TTTCropEd.ry / TTTCropEd.preview_ratio )
		});

		$('#sizes li').removeClass('details');
		try {
			$('#sizes li').pointer({}).pointer('close');
		} catch(e) {
		}

		$(this).parent().addClass('details').pointer({
			content: $('.info',this).html(),
			position: 'bottom',
			close: function() {
				// Once the close button is hit
			}
		}).pointer('open');


		TTTCropEd.setJcrop();


	});

	$('#tttcrop #sizes .size').bind('update-thumb',function(event, newsrc ) {
		var src = $(this).attr('data-src');
		if ( /^http/.test(newsrc) && newsrc != src ) {
			src = newsrc;
			$(this).attr('data-src',src);
		}
			
		var w = Number( $('.background',this).width() ) - 10;
		var h = Number( $('.background',this).height()  ) - 10;
		var urlsrc = TTT_Crop_Editor.ajax+'?action=ttt-crop_view&_vh='+h+'&_vw='+w+'&src='+encodeURIComponent(src)+'&rand='+Math.random();
		if ( $(this).attr('data-resize') >= 0 ) {
			urlsrc += '&_crop=1';
		}

		$('.background .img',this).css({
			backgroundImage: "url('"+urlsrc+"')",
		});

		//$('.background',this).effect( "bounce");
		$(this).effect( "highlight");


	});

	//$('#tttcrop #sizes .size').trigger('update-thumb');

	$('#target').load(function() {
		setTimeout(function() { $('#tttcrop #sizes .size:first').click(); },10);
	});

	//$('#tttcrop #sizes .size:first').click();
	
	TTTCropEd.init();

});

//})(jQuery);

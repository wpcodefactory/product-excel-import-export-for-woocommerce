/**
 * Product Excel Import & Export for WooCommerce - JS
 *
 * @author  WPFactory
 */

(function( $ ) {

	$(".importer-wrap .exportToggler").click(function(){
		$(".importer-wrap #exp_ProductsForm").slideToggle();
		$(".importer-wrap .exportTableWrapper").slideToggle();
		$(".importer-wrap .downloadToExcel").slideToggle();
	});


	$('.importer-wrap #upload').attr('disabled','disabled');

	$(".importer-wrap .woopeiFile").change(function () {
		var fileExtension = ['xls', 'xlsx'];
		if ( $.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1 ) {
			alert( "Only format allowed: "+fileExtension.join(', ') );
			$('.importer-wrap #upload').attr('disabled','disabled');
		}else{
			$('.importer-wrap #upload').removeAttr('disabled');
			$(".importer-wrap #product_import").submit();
		}
	});

	$(".importer-wrap #woopeiCatFile").change(function () {
		var fileExtension = ['xls', 'xlsx'];
		if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
			alert("Only format allowed: "+fileExtension.join(', '));
			$('.importer-wrap #upload').attr('disabled','disabled');
		}else{
			$('.importer-wrap #upload').removeAttr('disabled');
			$("#categories_import").submit();
		}
	});

	$('.importer-wrap .nav-tab-wrapper a').click(function(e){
		if($(this).hasClass("premium") ){
			$(".premium_msg").slideDown('slow');
			$('.importer-wrap').removeClass('loading');
		}
	});


	$(".importer-wrap #categories_import").on('submit',function(e) {

		e.preventDefault();

		if(confirm("Are you sure you want to import the terms in the file?")){
			var wpeiData = new FormData();
			$.each($('#woopeiCatFile')[0].files, function(i, file) {
				wpeiData.append('file', file);
			});
			wpeiData.append('_wpnonce',$("#_wpnonce").val());
			wpeiData.append('importCategories',$("#importCategories").val() );

			$.ajax({
						url: $(this).attr('action'),
						data: wpeiData,
						cache: false,
						contentType: false,
						processData: false,
						type: 'POST',
						beforeSend: function() {
							$('.importer-wrap').addClass('loading');
						},
						success: function(response){
							$(".result").slideDown().html($(response).find(".result").html());
							$('.importer-wrap').removeClass('loading');
							$(".importer-wrap").find('form').hide().delay(5000).fadeIn();
							$(".importer-wrap form")[0].reset();

							$(".success, .warning, .error").delay(5000).fadeOut();
						}
			});
		}
	});

	$(".importer-wrap #product_import").on("submit", function (e) {
		e.preventDefault();
				var wpeiData = new FormData();
				$.each($('.importer-wrap .woopeiFile')[0].files, function(i, file) {
					wpeiData.append('file', file);
				});
				wpeiData.append('_wpnonce',$("#_wpnonce").val());
				wpeiData.append('importProducts',$("#importProducts").val() );
				var url= window.location.href;

				$.ajax({
					url: window.location.href,
					data: wpeiData,
					cache: false,
					contentType: false,
					processData: false,
					type: 'POST',
					beforeSend: function() {
						$("html, body").animate({ scrollTop: 0 }, "slow");
						$('.importer-wrap').addClass('loading');
					},
					success: function(response){
						$(".importer-wrap .result").slideDown().html($(response).find(".result").html());
						$('.importer-wrap').removeClass('loading');
						$("#product_import").fadeOut();

						wpeiDragDrop();

						automatch_columns();

						$(".importer-wrap #product_process").on('submit',function(e) {
							e.preventDefault();
							if($("input[name='post_title']").val() !='' || $("input[name='ID']").val() !='' || $("input[name='_sku']").val() !='' ){
								$(".progressText").fadeIn();
								var total = $(".importer-wrap input[name='finalupload']").val() ;
								$(".importer-wrap .total").html(total-1);
								var i = 2;
								$('.importer-wrap').addClass('loading');

								function wpeiImportProducts() {
									var start = parseInt($(".importer-wrap input[name='start']").val() ,10 );
									var total = parseInt( $(".importer-wrap input[name='finalupload']").val(),10 ) ;
									if(start > total  ){
										$('.importer-wrap .success , .importer-wrap .error, .importer-wrap .warning').delay(2000).hide();
										$(".importer-wrap #product_import").delay(4000).slideDown();
										if( woopei.hide_rating != 1 ) $(".importer-wrap .rating").delay(2000).fadeIn();

									}else{

										$.ajax({
											url: woopei.ajax_url,
											data: $(".importer-wrap #product_process").serialize(),
											type: 'POST',
											beforeSend: function() {
												//$("html, body").animate({ scrollTop: 0 }, "slow");
												$(".importer-wrap #product_process").hide();
											},
											success: function(response){
												console.log(response);
												$(".importer-wrap .importMessage").slideDown().html($(response).find(".importMessage").html());
												$(".importer-wrap .ajaxResponse").html(response);
												$(".importer-wrap .thisNum").html($("#AjaxNumber").html() );

													$(".importer-wrap input[name='start']").val(i + 1 );
													i++;

											},complete: function(response){
													$('.importer-wrap').removeClass('loading');
													wpeiImportProducts();
											}
										});

									}
								}

								wpeiImportProducts();
							}else alert( 'Title Selection, SKU or Product ID (for update from export file) is Mandatory.' );

						});
					}
			});
	});

			//drag and drop

	function wpeiDragDrop(){
			$('.importer-wrap .draggable').draggable({cancel:false});
			$( ".importer-wrap .droppable" ).droppable({
			  drop: function( event, ui ) {
				$( this ).addClass( "ui-state-highlight" ).val( $( ".ui-draggable-dragging" ).val() );
				$( this ).attr('value',$( ".ui-draggable-dragging" ).attr('key')); //ADDITION VALUE INSTEAD OF KEY
				$( this ).val($( ".ui-draggable-dragging" ).attr('key') ); //ADDITION VALUE INSTEAD OF KEY
				$( this ).attr('placeholder',$( ".ui-draggable-dragging" ).attr('value'));
				$( ".ui-draggable-dragging" ).css('visibility','hidden'); //ADDITION + LINE
				$( this ).css('visibility','hidden'); //ADDITION + LINE
				$( this ).parent().css('background','#90EE90');
			  }

			});
	}
	wpeiDragDrop();

	function automatch_columns(){

				$(".importer-wrap #automatch_columns").on("change",function(){

					if($(".importer-wrap #automatch_columns").is(':checked')){

						$( ".importer-wrap .draggable" ).each(function(){

							var key = $( this ).attr('key') ;
							key = key.toUpperCase();
							key = key.replace(" ", "_");
							var valDrag = $( this ).val() ;


							$( ".importer-wrap .droppable" ).each(function(){

								var valDrop = $( this ).val();

								var drop = $( this ).attr('name');

								drop.indexOf( '_' ) == 0 ? drop = drop.replace( '_', '' ) : drop;

								var drop = drop.replace(/_/g, " ");
								var nameDrop = drop.toUpperCase();

								if( valDrag == nameDrop ){

									$( this ).val( key );

									$( this ).css('background','#90EE90');
									$( this ).parent().css('background','#90EE90');
								}
							});
						});

						alert("Check your automatch - The letter after the match signifies the Excel Column Letter. If not satisfied you can always uncheck auto match and do manually");

					}else{
						$( ".importer-wrap .droppable" ).val('');
						$( ".importer-wrap .droppable" ).css('background','initial');
						$( ".importer-wrap .droppable" ).parent().css('background','initial');
					}

				});
	}



			$(".importer-wrap #exp_ProductsForm").on('submit',function(e) {
				e.preventDefault();
				//if checkbox is checked
				$(".importer-wrap .fieldsToShow").each(function(){
					if($(this).is(':checked')){
					}else localStorage.setItem($(this).attr('name') ,$(this).attr('name') );
				});

				$.ajax({
					url: $(this).attr('action'),
					data:  $(this).serialize(),
					type: 'POST',
					beforeSend: function() {
						$('.importer-wrap').addClass('loading');
					},
					success: function(response){

						$(".importer-wrap #exp_ProductsForm").hide();
						$(".importer-wrap #selectTaxonomy").hide();

						$(".resultExport").slideDown().html($(response).find(".resultExport").html());

								//if checkbox is checked
								$(".importer-wrap .fieldsToShow").each(function(){
									if (localStorage.getItem($(this).attr('name')) ) {
										$(this).attr('checked', false);
									}
									localStorage.removeItem($(this).attr('name'));
								});

									var i=0;
									$(".importer-wrap input[name='total']").val($(".importer-wrap .totalPosts").html());
									$(".importer-wrap input[name='start']").val($(".importer-wrap .startPosts").html());
									total = $(".importer-wrap input[name='total']").val();
									start = $(".importer-wrap input[name='start']").val();
									rowcount = $('#toExport >tbody >tr').length;
									progressBar(start,total) ;

								function woopeiExportProducts() {
									var total = $(".importer-wrap input[name='total']").val();
									var start = $(".importer-wrap input[name='start']").val() * i;

									if(parseInt($(".importer-wrap .totalPosts").html() , 10) <=500){
											$(".importer-wrap input[name='posts_per_page']").val($(".importer-wrap .totalPosts").html());
									}else $(".importer-wrap input[name='posts_per_page']").val($(".importer-wrap .startPosts").html());

									dif = total- start;

									if( $('#toExport >tbody >tr').length >= total ){

										$('.importer-wrap #myProgress').delay(10000).hide('loading');

										$.getScript(woopei.exportfile, function() {
											$("#toExport").tableExport();
											$('.xlsx').trigger('click');
										});

										$("body").find('#exp_ProductsForm').find("input[type='number'],input[type='text'], select, textarea").val('');
										$('.importer-wrap .message').html('Job Done!');
										$('.importer-wrap .message').addClass('success');
										$('.importer-wrap .message').removeClass('error');

										if( woopei.hide_rating != 1 ) $(".importer-wrap .rating").delay(2000).fadeIn();

									}else{

										var dif = total - start;
										if(parseInt(total,10)> 500 && parseInt(dif,10) <=500 ){
											$(".importer-wrap  input[name='posts_per_page']").val(dif);
										}

										$.ajax({
											url: woopei.ajax_url,
											data: $(".importer-wrap #exp_ProductsForm").serialize(),
											type: 'POST',
											beforeSend: function() {
												$("html, body").animate({ scrollTop: 0 }, "slow");
												$('.importer-wrap').removeClass('loading');
											},
											success: function(response){

												$(".importer-wrap .tableExportAjax").append(response);
												i++;
												start = $(".importer-wrap input[name='start']").val() * i;

												$(".importer-wrap  input[name='offset']").val(start);

												var offset = $(".importer-wrap  input[name='offset']").val();
												console.log("dif "+ dif+" i: "+ i + " offset: " + offset + " start: " + start+ " total: " + total);

												progressBar(start,total) ;
											},complete: function(response){
													woopeiExportProducts();
											}
										});
									}
								}
								woopeiExportProducts();
					}
					});
			});



			function progressBar(start,total) {
				var width = (start/total) * 100;
				var elem = document.getElementById("myBar");
				if (start >= total-1) {
				  elem.style.width = '100%';
				} else {
				  start++;
				  elem.style.width = width + '%';
				}
			}

		$(".importer-wrap .premium").click(function(e){
			e.preventDefault();
			$("#woopeiPopup").slideDown();
		});

		$("#woopeiPopup .close").click(function(e){
			e.preventDefault();
			$("#woopeiPopup").fadeOut();
		});

		var modal = document.getElementById('woopeiPopup');

		// When the user clicks anywhere outside of the modal, close it
		window.onclick = function(event) {
			if (event.target == modal) {
				modal.style.display = "none";
			}
		}



		//EXTENSIONS
		$(".importer-wrap .wp_extensions").click(function(e){

			e.preventDefault();

			if( $('#woopei_extensions_popup').length > 0 ) {

				$(".importer-wrap .get_ajax #woopei_extensions_popup").fadeIn();

				$("#woopei_extensions_popup .woopeiclose").click(function(e){
					e.preventDefault();
					$("#woopei_extensions_popup").fadeOut();
				});
				var extensions = document.getElementById('woopei_extensions_popup');
				window.onclick = function(event) {
					if (event.target === extensions) {
						extensions.style.display = "none";
						localStorage.setItem('hideIntro', '1');
					}
				}
			}else{


				var action = 'woopei_extensions';
				$.ajax({
					type: 'POST',
					url: woopei.ajax_url,
					data: {
						"action": action
					},
					 beforeSend: function(data) {
						$("html, body").animate({ scrollTop: 0 }, "slow");
						$('.importer-wrap').addClass('loading');

					},
					success: function (response) {
						$('.importer-wrap').removeClass('loading');
						if( response !='' ){
							console.log(response);
							$('.importer-wrap .get_ajax' ).css('visibility','hidden');
							$('.importer-wrap .get_ajax' ).append( response );
							$('.importer-wrap .get_ajax #woopei_extensions_popup' ).css('visibility','visible');
							$(".importer-wrap .get_ajax #woopei_extensions_popup").fadeIn();

							$("#woopei_extensions_popup .woopeiclose").click(function(e){
								e.preventDefault();
								$("#woopei_extensions_popup").fadeOut();
							});
							var extensions = document.getElementById('woopei_extensions_popup');
							window.onclick = function(event) {
								if (event.target === extensions) {
									extensions.style.display = "none";
									localStorage.setItem('hideIntro', '1');
								}
							}
						}
					},
					error:function(response){
						console.log('error');
					}
				});
			}
		});


		$("#woopei_signup").on('submit',function(e){
			e.preventDefault();
			var dat = $(this).serialize();
			$.ajax({

				url: "https://extend-wp.com/wp-json/signups/v2/post",
				data: dat,
				type: 'POST',
				beforeSend: function(data) {
						console.log(dat);
				},
				success: function(data){
					alert(data);
				},
				complete: function(data){
					dismissWoopei();
				}
			});
		});

		function dismissWoopei(){

				var ajax_options = {
					action: 'woopei_push_not',
					data: 'title=1',
					nonce: 'woopei_push_not',
					url: woopei.ajax_url,
				};

				$.post( woopei.ajax_url, ajax_options, function(data) {
					$(".woopei_notification").fadeOut();
				});
		}

		$(".woopei_notification .dismiss").on('click',function(e){
				var ajax_options = {
					action: 'woopei_push_not',
					data: 'title=1',
					nonce: 'woopei_push_not',
					url: woopei.ajax_url,
				};

				$.post( woopei.ajax_url, ajax_options, function(data) {
					$(".woopei_notification").fadeOut();
				});
		});

		$(".importer-wrap .rating .dismiss").on('click',function(e){
				var ajax_options = {
					action: 'woopei_hide_rating',
					data: 'title=1',
					nonce: 'woopei_hide_rating',
					url: woopei.ajax_url,
				};

				$.post( woopei.ajax_url, ajax_options, function(data) {
					$(".importer-wrap .rating").fadeOut();
				});
		});

})( jQuery );

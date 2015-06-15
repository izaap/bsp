	function swap(elm) 
	{
		$(elm).parents('.item').find('.main').attr('src', elm.href);
	}
	
	$(function() {

		$('img[id^="videoPlay_"]').each(function(){
			$(this).hover(
				function() {		
					$( '#videoModal_'+$(this).data('id') ).show( );
					document.getElementById('videoPlayer_'+$(this).data('id')).play();
				}, function() {
					$( '#videoModal_'+$(this).data('id') ).hide( );
					document.getElementById('videoPlayer_'+$(this).data('id')).pause();
				}
			);

			$(this).trigger('hover').trigger('mouseout');
		});


		$('.cart_qty').keyup(function(){

			var org = this,
				colors = $(org).parents('.sizes').find('.colors'),
				psku = $(org).data('psku'),
				elm_total_qty = $('[data-sku="'+psku+'"]'),
				total_qty = 0;

			colors.each(function(){
				var self = this;
				
				$(self).find('.cart_qty').each(function(){
					var val = $.trim(this.value);
					if( val != '' && !$.isNumeric( val ) )
					{
						alert("Please enter valid number.");
						this.value = '';
					}
					else
					{
						var tmp = parseInt(val)
						tmp = isNaN(tmp)?0:tmp;
						total_qty += tmp;

						if( val == '' )
							this.value = '';
						else
							this.value = tmp; 
					}
					
					
					//$.isNumeric( "-10" );  
				});
			});

			var price = $(elm_total_qty).data('price'),
				total_price = (total_qty*price),
				total_price = parseFloat(total_price).toFixed(2);

			$(elm_total_qty).find('.tqty').val(total_qty);
			$(elm_total_qty).find('.total_price').find('input').val(total_price);
			$(elm_total_qty).find('.total_price').html('= $'+total_price);
		
			setTimeout(function(){ gtt(); }, 10 );

		});


		$('.submit-order').click(function(){

			var flag =true,
				tot_qty = 0;

			$('.subtot').each(function(){

				var min_qty = $(this).data('min-qty'),
					min_qty = parseInt(min_qty),
					sku 	= $(this).data('sku'),
					qty 	= $(this).find('.tqty').val(),
					qty 	= parseInt(qty);

					tot_qty += qty;

				if( qty && qty < min_qty )
				{
					alert("You should order atleast "+min_qty+" Qty on Style:"+sku+".");
					flag = false;

				}
			});

			if(!tot_qty)
			{
				alert("You cart is empty.Please buy atleast one product.");
				flag = false;
			}

			if( flag )
			{
				$.post(location.href, $("#order-form").serialize(), function(rdata){
				
					if(rdata.status == 'success')
					{
						location.href='/order/confirmation/'+rdata.order_id;
					}
					else
					{
						$.each(rdata.errors, function(key, val){
							var elm = $("[name='"+key+"']");
							$.each(val, function(etype, error){
								elm.parent().find('.error').remove();
								elm.after('<span class="error">'+error+'</span>');
							});
						});
					}
				}, 'json');
			}
			

		});		

	});

	function gtt()
	{
		var grand_total = 0;

		$('.subtot').each(function(){
			var price = $(this).data('price'),
				qty = $(this).find('.tqty').val(),
				price = parseFloat(price).toFixed(2),
				qty = parseInt(qty);

			if( qty != undefined && price != undefined && qty && price )	
			{
				var total_price = qty*price
				grand_total += total_price;
				
			}
			
			

		});

		grand_total = parseFloat(grand_total).toFixed(2);

		$('.grand-total').html('$'+grand_total);
	}
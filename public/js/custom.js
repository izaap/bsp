	function swap(elm) 
	{
		$(elm).parents('.item').find('.main').attr('src', image.href);
	}
			 
	$('.videoPlay').hover(
		function() {
			$( '#videoModal' ).show( );
			document.getElementById('videoPlayer').play();
		}, function() {
			$( '#videoModal' ).hide( );
			document.getElementById('videoPlayer').pause();
		}
	);
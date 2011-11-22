jQuery(document).ready( function($) {
	$('.react-social-share').bind('click',	function(e) {
		e.preventDefault();

		var shareUrl = $(this).attr('href');
		$.fancybox({
			'type'				: 'iframe',
			'width'				: 621,
			'height'			: 510,
	        'autoScale'     	: false,
	        'transitionIn'		: 'none',
			'transitionOut'		: 'none',
			'scrolling'			: 'no',
			'href'				: shareUrl,
			'overlayOpacity'	: '0.66',
			'titleshow'			: 'false',
			'onComplete'		: function(){
				if(!$('#react-social-credentials').length)
					$('div#fancybox-wrap').append('<address id="react-social-credentials">Powered by <a href="http://react.com">React Social Connections</a></div>');
			}
		});
	});
});

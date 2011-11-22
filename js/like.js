jQuery(document).ready( function($) {
	$('.react-social-analytics-like-link').bind('click', function(){
		$(this).parent()
			.find('.react-social-analytics-like-not-logged-in-message').hide().fadeIn('fast');
	});

	$('form .react-social-analytics-like-link').bind('click', function(e){
		e.preventDefault();

		var link = $(this),
			container = link.parents('.react-social-analytics-links');
		link.addClass('loading');

		jQuery.post(document.location.href,
			container.find('form').serialize() + '&reactSocialAjaxPost=1',
			function(result) {
				if (result == 'success') {
					/* make sure the right message becomes visible */
					var nextSpan,
						span = container.find('.react-social-analytics-like-message > span.active');

					span.removeClass('active');

					if (span.hasClass('no-vote'))
						nextSpan = 'your-vote';
					else if (span.hasClass('other-vote'))
						nextSpan = 'your-other-vote';

					container.find('.react-social-analytics-like-message > span.' + nextSpan)
						.addClass('active');
				}
				else {
					container.find('.react-social-analytics-like-success-message')
						.html('<div class="message error">' + result + '</div>');
				}

			container.find('.react-social-analytics-like-success-message').fadeIn('fast');
			container.find('form').remove();
		});
	});
});

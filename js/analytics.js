ReactSocialAnalytics = {};

(function(){
	var $ = jQuery;

	this.init = function()
	{
		_networkButtonHandlers();
		_useAjax();
	}

	this.connectProvider = function(provider)
	{
		var query = window.location.search;
		$('#form-head').load(query + ' #hidden-form-fields, #socialmedia-networks');
	}

	var _networkButtonHandlers = function()
	{
		$('#form-head').delegate('#socialmedia-networks li', 'click', function(e) {
			e.preventDefault();

			if (!$(this).hasClass('connected'))
			{
				var connectUrl = '?action=requestToken&shareConnect&provider=';
				var provider = $(this).find('input').val();
				var windowFeatures = 'menubar=no,location=no,resizable=yes,status=no';
				window.open(connectUrl + provider, 'reactsocial', windowFeatures);
			}
			else
				_toggleShareProvider($(this));
		});
	}

	var _toggleShareProvider = function(provider)
	{
		provider.toggleClass('selected');
		var input = provider.children('input');
		var providerName = provider.find('label span span').text();

		if (provider.hasClass('selected')) {
			input.attr('checked', true);
			provider.attr('title', 'Remove ' + providerName + ' from selection');
		}
		else {
			input.attr('checked', false);
			provider.attr('title', 'Add ' + providerName + ' to selection');
		}
	}

	var _useAjax = function()
	{
		$('#share-button').bind('click', function(e){
			e.preventDefault();

			var button = $(this),
				form = $(this.form),
				data = form.serialize() + '&reactSocialAjaxPost=1';

			jQuery.post(document.location.href, data, function(result) {
				if (result == 'success') {
					form.find('textarea').attr('disabled', 'disabled');
					button.remove();
					$('#react-social-share-error-detail').hide();
					$('#react-social-share-success').fadeIn('fast');
				}
				else {
					$('#react-social-share-error-detail').html(result).fadeIn('fast');
				}
			});
		});

		$('#ajax-indicator')
			.ajaxStart(function(){ $('#share-button').hide(); $(this).show(); })
			.ajaxStop(function(){ $('#share-button').show(); $(this).hide(); });
	}

	this.fixIE6 = function()
	{
		_ie6CssClasses();
		$(document).ajaxStop(_ie6CssClasses);
	}

	var _ie6CssClasses = function()
	{
		var providers = $('#socialmedia-networks').find('li'), len, item, newClass;

		if((len = providers.length) == 0)
			return;

		while(len--)
		{
			item = providers.get(len);
			newClass = item.className.replace(' ', '-'); // Non-global replacement

			if($(item).hasClass(newClass))
				continue;

			$(item).addClass(newClass);
			$(item).hover(
				function(){ $(this).addClass('hover') },
				function(){ $(this).removeClass('hover') });
		}
	}
}).apply(ReactSocialAnalytics);

ReactSocialAnalytics.init();

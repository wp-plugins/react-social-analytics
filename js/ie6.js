jQuery(document).ready( function($) {
	var providers = $('#loginform').find('.providers li'), len, item;

	if((len = providers.length) == 0)
		return;

	while(len--)
	{
		item = providers.get(len);
		item.className = item.className.replace(/\s/g, '-');
	}
});

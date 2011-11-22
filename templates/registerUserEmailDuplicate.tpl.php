<?php
	get_header();
?>
<div id="content">
<?php

	_e('<h2>Sorry, registration was unsuccessful!</h2>', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN);
	printf(__('<p>The email address \'%1$s\' is already in use by an existing account.</p>', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN), htmlentities(urldecode($userEmail)));
	printf(__('<ul><li>Please <a href="%1$s">login</a> with the existing account in order to connect with %2$s.</li><li><a href="%3$s">Retrieve your password</a> if you don\'t have the login details any more.</li></ul>', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN), site_url('wp-login.php'), htmlentities($provider), site_url('wp-login.php?action=lostpassword&email=' . urlencode($userEmail)));
?>
</div>
<?php
	get_footer();

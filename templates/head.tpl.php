<?php

wp_enqueue_script('jquery', 'wp-includes/js/jquery/jquery.js', array(), false, true);

if ($this->_config['reactShareServiceEnabled'])
{
	wp_enqueue_script(
		'fancybox',
		REACT_SOCIAL_ANALYTICS_APPLICATION_URL . '/js/fancybox/jquery.fancybox.pack.js',
		array(),
		false,
		true
	);
}

if ($this->_config['reactLikeServiceEnabled'])
{
	wp_enqueue_script('react_social_analytics_like',
		REACT_SOCIAL_ANALYTICS_APPLICATION_URL . '/js/like.js',
		array(),
		REACT_SOCIAL_ANALYTICS_VERSION,
		true);
}

if ($this->_config['reactShareServiceEnabled'])
{
	wp_enqueue_script(
		'react_social_analytics_share',
		REACT_SOCIAL_ANALYTICS_APPLICATION_URL . '/js/share.js',
		array(),
		REACT_SOCIAL_ANALYTICS_VERSION,
		true
	);
	wp_enqueue_style(
		'fancybox_style',
		REACT_SOCIAL_ANALYTICS_APPLICATION_URL . '/js/fancybox/jquery.fancybox.css',
		array(),
		REACT_SOCIAL_ANALYTICS_VERSION,
		'screen'
	);
}

wp_enqueue_style(
	'react_social_analytics',
	REACT_SOCIAL_ANALYTICS_APPLICATION_URL . '/css/reactsocialanalytics.css',
	array(),
	REACT_SOCIAL_ANALYTICS_VERSION,
	'screen'
);
wp_enqueue_style(
	'react_social_analytics_social',
	REACT_SOCIAL_ANALYTICS_APPLICATION_URL . '/css/social-network-icons.css',
	array(),
	REACT_SOCIAL_ANALYTICS_VERSION,
	'screen'
);

=== React Social Analytics ===
Contributors: React.com
Tags: social, analytics, login, register, facebook, twitter, hyves, linkedin, google, like button, oauth
Requires at least: 3.1
Tested up to: 3.4
Stable tag: 1.0.4.0

Integrate social networks into your site, making it more personal and social. As a bonus, you get more insight in who visits your site.

== Description ==

React Social Analytics integrates social networks into your site in a few simple steps. Offer social sign-in and sharing features to your visitors to lower registration barriers and simplify sharing to social networks.

Use your personal dashboard at http://account.react.com to get more insight into who's visiting your blog or website!

= Official site =

Visit React.com (http://react.com) if you're looking for more information or have any (support) questions. Register and manage your account at https://account.react.com .

= Services =

OAuth service: Adds buttons to register and login using a social network such as Facebook, Twitter and LinkedIn. This makes it very easy for new users to register. More users for your site!

Like service: Adds a like button to articles to let people vote for it.

Share service: Let your users easily share any article on any of their social networks. You can even share on multiple networks in one go. URLs are automaticly shortened and tracked.

= Supported social networks =

Currently, React.com supports 10 different (inter)national social networks:

* Facebook
* LinkedIn
* Twitter
* Google
* Hyves
* Foursquare
* Windows Live
* Netlog
* Yahoo
* Myspace

== Installation ==

1. Upload everything into the "/wp-content/plugins/" directory of your WordPress site.
2. Activate in the "Plugins" admin panel.
3. Create an account and application on account.react.com.
4. Enter your application key and secret at the "Settings | OpenReact Social Analytics" page.

You can also make use of two template tags anywhere in your templates:

* Share: react_social_analytics_share('url-to-share', 'optional title', 'optional-image-url', 'optional comment')
* Like: react_social_analytics_like('category', 'resource-uri')

== Frequently Asked Questions ==

http://react.com

== Screenshots ==

1. Connect using many different networks.
2. Sharing a post.

== Changelog ==

= 1.0.0.0 =
Initial release.

= 1.0.1.0 =

* Fixed an issue with Facebook not supporting Site URLs without paths

= 1.0.2.0 =

* Made some messages in the admin stand out more
* Tweaked code so that it is backward compatible with PHP 5.2
* Registration page messages tweaked

= 1.0.3.0 =

* Don't try to connect to the services if no React application key/secret is set
* Adjusted user agent for XML-RPC requests to include wordpress and plugin version

= 1.0.3.1 =

* Adjust share overlay to also cover brand header in the default WP 3.3 theme

= 1.0.4.0 =

* Improved compatibility with `W3 total cache` and many other plugins
* Updated copyright year

== Upgrade Notice ==

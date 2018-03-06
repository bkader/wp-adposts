=== WP AdPosts by Kader Bouyakoub ===
Contributors: bkader
Donate link: http://bit.ly/2FrdpOg
Tags: WP-AdPosts, WordPress.com, ads, banner, plugin, widget, management, stats
Requires at least: 4.6
Tested up to: 4.9.4
Stable tag: 1.2.0
Requires PHP: 5.2.4
License: GPLv3 or later
License URI: https://opensource.org/licenses/GPL-3.0

Easy ads creation and management using images or codes. display your ads anywhere, automatically or using shortcodes, with views and clicks counters.

== Description ==

WP AdPosts plugin allows you to create ads using codes or images and provides views and clicks counters, as well as locations management. You can even use the provided **shortcode** to insert ads into posts and pages.

= Manage Ads =
You have two ways of creating ads:

* By uploading featured images only and provided ads links.
* By pasting ads codes (JavaScript) or full HTML.

**Note**: If you use full HTML, it is better to enter your ads links into the "Ad Link" field.

In order to display an ad on your front-end, you can use the provided php function on your theme's files like so:
`<?php echo wp_adposts( 'location' ); ?>`
Location here stands for the created location's **slug**. So if you created a **Header** location, it may have the **header** slug, so you do:
`<?php echo wp_adposts( 'header' ); ?>`

WP AdPosts enabled shortcodes on text widgets, so you can as well use a widget in which you can use it like so:
`[wp-adposts location="header"]`

= Manage Locations =
They are much more like "sizes" limitations. A location can have a specified ads sizes so that ads will be contained in them. For instance, if a location has 728 pixles by 90 pixels (728x90), anything that is beyond that is simply hidden.

= Views and Clicks Counters =
You will see on ads management page views and clicks counters. These are useful to keep an eye on your ads.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wp-adposts` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress.
1. Use the Settings->WP AdPosts screen to configure the plugin.
1. Add as many locations as you want, but please do not forget to select ads sizes because it is important.
1. Add you ads by uploading featured images or pasting code into the editor and don't forget to add the ads links in order to track clicks.

== Screenshots ==

1. After activation, you will see the plugin menu item on your dashboard.
2. On your settings page, make sure to select only desired ads sizes to avoid creating multiple useless thumbnails for uploaded ads images.
3. This is where you can manage your ads locations, or as some like to call them "sizes limitations".
4. Creating ads is as easy as creating WordPress posts or pages.
5. On the ads management page, you can track your ads views (displays) and clicks.
6. An example of displayed automatic ads or using shortcodes (within the blog post).

== Changelog ==

= 1.2.0 =
* The default location meta box was replaced with a custom one.

= 1.0.1 =
* Some input and output sanitizing were added.

= 1.0.0 =
* First plugin commit.

== Upgrade Notice ==

= 1.2.0 =
The default location meta box was replaced with a custom one.

= 1.0.1 =
For more security, inputs and outputs were sanitized.

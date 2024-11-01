=== YouTube Video Importer Lite ===
Contributors: constantin.boiangiu
Tags: YouTube channel, YouTube video post, YouTube importer, video import, video post
Requires at least: 4.6
Tested up to: 5.3
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Import YouTube videos as WordPress posts and increase your YouTube channel audience.

== Description ==

**YouTube Video Importer Lite** is a WordPress plugin developed to help you synchronize your YouTube channel feed (or any other feed) with WordPress and create video posts that help boost the channel and WordPress website audience.

https://youtu.be/Qbq5uC5TyHs

The plugin can create video posts from all kind of resources, for example:

* YouTube search queries - search for any topic and choose the videos that you want to import as WordPress posts;
* YouTube playlists - enter any public playlist ID to import videos;
* YouTube channel - import videos from any channel by inserting the ID;
* User uploads - enter a user ID to get all videos uploaded over time.

The plugin allows importing of single video or bulk importing a list of videos from a given feed ID (YouTube channel, search, playlist or user). After importing, the video embed is done automatically by the plugin and all additional details (post title, description, category, tags, featured image) are filled according to your options.

**Features**

* Import videos as plugin custom post type - Videos retrieved from YouTube will be imported into WordPress as custom post type **video**;
* Single video import - create new WordPress post from a given YouTube video ID;
* Bulk import videos - import YouTube videos as WordPress posts by using the manual bulk import feature;
* Bulk import into WordPress categories - import YouTube videos into your existing post categories;
* General video embedding option - set global video embed options for all your new videos from plugin settings;
* Post video embedding options - customize video embed settings for each individual post created from a YouTube video;
* Video title and description import - the post created from YouTube video will automatically have the title and post content and/or excerpt filled with the details retrieved from YouTube;
* Single video shortcode embed - a simple shortcode that embeds imported videos into any post or page;
* Video playlist shortcode embed - a playlist shortcode that embeds playlists made from existing posts into any post or page.

**Links**

* [YouTube video importer homepage](https://wpythub.com/?utm_source=readme&utm_medium=doc_link&utm_campaign=youtube-video-importer-lite "WordPress YouTube video importer" );
* [YouTube video importer documentation](https://wpythub.com/documents/getting-started/?utm_source=readme&utm_medium=doc_link&utm_campaign=youtube-video-importer-lite "WordPress YouTube video importer online documentation" );

== Installation ==
-------------------------

The plugin can be installed manually or automatically from WordPress installation Plugins page.

**Before being able to import YouTube videos as WordPress posts** you will need to create a project in Google Cloud Platform and enable YouTube Data API in it.

See the video tutorial on how to set client ID and secret:

https://www.youtube.com/watch?v=OBvxXzp1BZI

== Changelog ==

=1.0.3=
* Solved a bug that in some cases caused Google error "invalid_client" on API consent screen;
* Updated documentation links to point to updated video describing how to set up YouTube API client ID and client secret.

=1.0.2=
* Added privacy policy information which is displayed into WordPress's Privacy Policy Guide.

= 1.0.1 =
* Updated translations domain to WP repository slug;
* Solved an error that was generating a PHP notice in WP admin when navigating in plugin admin menu Videos;
* Removed unused PHP file.

= 1.0=
* Initial release.
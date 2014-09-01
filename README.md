# Send Images to RSS

__NOTE: this plugin is totally a work in progress, and it is up to you to check that your feed output is still working, especially in your email system of choice, once it's installed.__ I've attempted to set it up to handle XHTML or HTML5, and function even if your feed is wonky, but __please__ double check, and let me know if you have issues, and if so, what specifically they are.

WordPress plugin that replaces images with an email friendly size image in RSS feeds. I like this for sending images from a WordPress gallery, for example--instead of sending thumbnails to the RSS readers, they get the full size images. Also, even if you like to upload large images to your site, this plugin will hopefully prevent you from blowing up people's email accounts.

## Description

The plugin adds a new email friendly image size to WordPress. Any large images uploaded to your site with this plugin activated will automatically have a new copy generated which is 560 pixels wide. If this image exists, it will be sent to your RSS feed, so we avoid the issue of overlarge images going out in email. (Images uploaded prior to activating this plugin will not be affected unless you regenerate thumbnails on your site. But seriously, I wouldn't bother regenerating thumbnails, because you won't be sending old posts out via an RSS email.)

## Requirements
* WordPress 3.8, tested up to 4.0RC1

## Installation

### Upload

1. Download the latest tagged archive (choose the "zip" option).
2. Go to the __Plugins -> Add New__ screen and click the __Upload__ tab.
3. Upload the zipped archive directly.
4. Go to the Plugins screen and click __Activate__.

### Manual

1. Download the latest tagged archive (choose the "zip" option).
2. Unzip the archive.
3. Copy the folder to your `/wp-content/plugins/` directory.
4. Go to the Plugins screen and click __Activate__.

Check out the Codex for more information about [installing plugins manually](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

### Git

Using git, browse to your `/wp-content/plugins/` directory and clone this repository:

`git clone git@github.com:robincornett/send-images-rss.git`

Then go to your Plugins screen and click __Activate__.

## Frequently Asked Questions

### How can I change the size of the image being sent to the RSS?

Most users should not need to change this. The plugin is designed with a default image size of 560 pixels for the width of the new image. If, however, your RSS email template is more or less than 600 pixels wide, or you're using a template with a sidebar, you may need to change this setting. What number you choose is up to you.

__Note:__ If you use an email template with a sidebar, I strongly recommend that you opt to use the Alternate Feed for your emails, as your images will be too small to be attractive on services like Flipboard and Feedly.

### Does this plugin work with excerpts?

Nope. This plugin is intended for use with full content feeds only. There are several plugins which will resize and/or add your post's featured image to your feed; those work best if your feed is excerpts.

### What about smaller images?

Smaller images will still be small. WordPress handles image alignment differently than email clients (set by class v. inline align). If your smaller image has an right/left alignment set in post, the plugin will copy that alignment in your email as well, and add a margin.

### I have funky characters in my RSS feed and emails. Why?

Because you have funky characters and/or invalid markup in your posts. The plugin attempts to process your feed and encode wonky markup, but if your server doesn't have certain packages installed, the fallback is to process your feed as is, warts and all.

### I installed this plugin and the email that I sent out five minutes later still had giant images.

The plugin only generates properly sized images for new uploads--anything you uploaded before the plugin was active will still be giant, if that's what you uploaded and your email client ignores a max-width setting. You can re-upload the images and they should behave as desired.

### What is this Alternate Feed?

Because I use Feedly, and as a former photographer, it bothers me to see the freshly rendered email sized images blown up and soft to fit Feedly/Feedburner specs. So this gives you the option of having your main feed(s) with large images (galleries will be converted, too), but a special email-only feed which you can use with an email service like MailChimp, Campaign Monitor, or FeedBurner.

### I selected Alternate Feed, clicked the link for my new feed, and got a 404 (Page Not Found). Help?

If this happens, your permalink for the new feed may not have been updated. Visit Settings > Permalinks in your admin. Save Changes if you like, and refresh your feed page.

### What is Simplify Feed?

If you use native WordPress galleries in your posts, they're sent to your feed as thumbnails. Even if you do not use an RSS/email service, you can still use this plugin to sort out your galleries for subscribers who use an RSS reader. If you select Simplify Feed, your galleries will be converted, but there will not be an email sized image created, and no alternate feed will be created.

## Screenshots ##
![Screenshot of the optional plugin settings in Settings > Media.](https://github.com/robincornett/send-images-rss/blob/develop/assets/screenshot-1.png)  
__Screenshot of the optional plugin settings in Settings > Media.__

## Credits

* Built by [Robin Cornett](http://robincornett.com/)
* With major insight [Gary Jones](http://gamajo.com)
* Inspired by [Erik Teichmann](http://www.eriktdesign.com/) and [Chris Coyier, CSS-Tricks](http://css-tricks.com/dealing-content-images-email/)

## Changelog

###2.4.2
* updated for new WordPress version.
* added plugin icon.
* moved, but did not change, main image function.

###2.4.1
* Sanitization bug fix.

###2.4.0
* Added a simplify feed method, which allows user to clean up galleries only, without creating an email friendly feed.
* Many much refactoring and input and tail kicking from the incomparable [Gary Jones](http://gamajo.com)
* Help tab added to media settings page.

###2.3.0
* Added an alternate feed method so that original feed could serve up full sized images while alternate feed would be used for email.

###2.2.0
* Added a image width setting to the Settings > Media screen so that the MailChimp size image can be changed.

###2.1.1
* Revised for class
* integrated gallery scan into main function

###2.1.0
* Much revising--set conditional to use MailChimp size image if exists
* Updated function to retrieve image URL to not use guid
* Changed filter to the_content instead of the_content_rss due to shortcode explosions
* If an image is smaller than MailChimp size, left/right alignment will be honored; otherwise, alignment will be set to center.
* If a post has a gallery, an additional scan occurs to pull full size images and use those (if a MailChimp size image exists, it will still be used).

###2.0.0beta
* Total rewrite
* Adds a new image size called 'mailchimp' to WordPress so that it can be used instead of trying to shoehorn the large images.
* strips out GravityForm shortcodes, but if others exist, that could be problematic.

###1.1.1
* simplified immensely. Dropped need for user to edit plugin files.
* deals with captions.
<?php
/**
 * Send Images to RSS
 *
 * @package   SendImagesRSS
 * @author    Robin Cornett <hello@robincornett.com>
 * @author    Gary Jones <gary@garyjones.co.uk>
 * @link      https://github.com/robincornett/send-images-rss
 * @copyright 2014 Robin Cornett
 * @license   GPL-2.0+
 */

/**
 * Fix content in a feed.
 *
 * @package SendImagesRSS
 */
class SendImagesRSS_Feed_Fixer {
	/**
	 * Fix parts of a feed.
	 *
	 * This function is applied as a callback to the_content filter.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content Existing feed content.
	 *
	 * @return string Amended feed content.
	 */
	public function fix( $content ) {
		$content = '<div>' . $content . '</div>'; // set up something you can scan

		$doc = $this->load_html( $content );

		$this->modify_images( $doc );

		// Strip extra div added by new DOMDocument
		$content = substr( $doc->saveHTML( $doc->getElementsByTagName( 'div' )->item( 0 ) ), 5, -6 );

		return $content;
	}


	/**
	 * Try and load HTML as an HTML document with special characters, etc. intact.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content Feed content.
	 *
	 * @return DOMDocument
	 */
	protected function load_html( $content ) {
		$doc = new DOMDocument();

		// Populate the document, hopefully cleanly, but otherwise, still load.
		libxml_use_internal_errors( true ); // turn off errors for HTML5
		// best option due to special character handling
		if ( function_exists( 'mb_convert_encoding' ) ) {
			$currentencoding = mb_internal_encoding();
			$content = mb_convert_encoding( $content, 'HTML-ENTITIES', $currentencoding ); // convert the feed from XML to HTML
		}
		// not sure this is an improvement over straight load (for special characters)
		elseif ( function_exists( 'iconv' ) ) {
			$currentencoding = iconv_get_encoding( 'internal_encoding' );
			$content = iconv( $currentencoding, 'ISO-8859-1//IGNORE', $content );
		}
		$doc->LoadHTML( $content );
		libxml_clear_errors(); // now that it's loaded, go ahead

		return $doc;
	}


	/**
	 * Modify images in content.
	 *
	 * Argument passed by reference, so no return needed.
	 *
	 * @since 1.1.1
	 *
	 * @param DOMDocument $doc
	 */
	protected function modify_images( DOMDocument &$doc ) {

		// Now work on the images, which is why we're really here.
		$images  = $doc->getElementsByTagName( 'img' );

		foreach ( $images as $image ) {

			$item = $this->get_image_variables( $image );

			if ( false === $item->image_id ) {
				return; // bail early if the image is not part of our WP site
			}

			$image->removeAttribute( 'height' );
			$image->removeAttribute( 'style' );

			$this->replace_images( $image );
		}

	}


	/**
	 * Set variables for replace_images, fix_other_images, and fix_captions to use.
	 *
	 * Argument passed by reference, so no return needed.
	 *
	 * @since 2.5.0
	 *
	 * @param $image
	 */
	protected function get_image_variables( $image ) {
		$item            = new stdClass();
		$item->image_url = $image->getAttribute( 'src' );
		$item->image_id  = $this->get_image_id( $item->image_url ); // use the image URL to get the image ID
		$item->mailchimp = wp_get_attachment_image_src( $item->image_id, 'mailchimp' ); // retrieve the new MailChimp sized image
		$item->original  = wp_get_attachment_image_src( $item->image_id, 'original' ); // retrieve the original image size
		$item->caption   = $image->parentNode->getAttribute( 'class' ); // to cover captions
		$item->class     = $image->getAttribute( 'class' );
		$item->width     = $image->getAttribute( 'width' );
		$item->maxwidth  = get_option( 'sendimagesrss_image_size', 560 );
		$item->halfwidth = floor( $item->maxwidth / 2 );

		return $item;
	}

	/**
	 * Replace large images with new email sized images.
	 *
	 * Argument passed by reference, so no return needed.
	 *
	 * @since 2.5.0
	 *
	 * @param $image
	 */
	protected function replace_images( $image ) {

		$item = $this->get_image_variables( $image );

		// use the MailChimp size image if it exists.
		if ( isset( $item->mailchimp[3] ) && $item->mailchimp[3] ) {
			if ( false !== strpos( $item->caption, 'wp-caption' ) ) {
				$image->parentNode->removeAttribute( 'style' ); // remove the style from parentNode, only if it's a caption.
			}
			$image->setAttribute( 'src', esc_url( $item->mailchimp[0] ) ); // use the MC size image for source
			$image->setAttribute( 'width', absint( $item->mailchimp[1] ) );
			$image->setAttribute( 'style', esc_attr( 'display:block;margin:10px auto;' ) );
		}

		else {
			$this->fix_other_images( $image );
			$this->fix_captions( $image );
		}
	}


	/**
	 * Modify images in content.
	 *
	 * Argument passed by reference, so no return needed.
	 *
	 * @since 2.5.0
	 *
	 * @param $image
	 */
	protected function fix_other_images( $image ) {

		$item = $this->get_image_variables( $image );

		// first check: only images uploaded before plugin activation in [gallery] should have had the width stripped out
		if ( empty( $item->width ) ) {
			$image->setAttribute( 'width', esc_attr( $item->original[1] ) );
		}
		// now, if it's a small image, aligned right. since images with captions don't have alignment, we have to check the caption alignment also.
		if ( ( ( false !== strpos( $item->class, 'alignright' ) ) || ( false !== strpos( $item->caption, 'alignright' ) ) ) && ( $item->width < $item->maxwidth ) ) {
			$image->setAttribute( 'align', 'right' );
			$image->setAttribute( 'style', esc_attr( 'margin:0px 0px 10px 10px;max-width:' . $item->halfwidth . 'px;' ) );
		}
		// or if it's a small image, aligned left
		elseif ( ( ( false !== strpos( $item->class, 'alignleft' ) ) || ( false !== strpos( $item->caption, 'alignleft' ) ) ) && ( $item->width < $item->maxwidth ) ) {
			$image->setAttribute( 'align', 'left' );
			$image->setAttribute( 'style', esc_attr( 'margin:0px 10px 10px 0px;max-width:' . $item->halfwidth . 'px;' ) );
		}
		// now what's left are large images which don't have a MailChimp sized image, so set a max-width
		else {
			$image->setAttribute( 'style', esc_attr( 'display:block;margin:10px auto;max-width:' . $item->maxwidth . 'px;' ) );
		}

	}


	/**
	 * Modify captions in content.
	 *
	 * Argument passed by reference, so no return needed.
	 *
	 * @since 2.5.0
	 *
	 * @param $image
	 */
	protected function fix_captions( $image ) {

		$item = $this->get_image_variables( $image );

		// now one last check if there are captions O.o
		if ( false === strpos( $item->caption, 'wp-caption' ) ) {
			return; // theoretically, no caption, so skip forward and finish up.
		}
		else { // we has captions and have to deal with their mess.
			$image->parentNode->removeAttribute( 'style' );

			// if it's a small image with a caption, aligned right
			if ( false !== strpos( $item->caption, 'alignright' ) && $item->width < $item->maxwidth ) {
				$image->parentNode->setAttribute( 'style', esc_attr( 'float:right;max-width:' . $item->halfwidth . 'px;' ) );
			}
			// or if it's a small image with a caption, aligned left
			elseif ( false !== strpos( $item->caption, 'alignleft' ) && $item->width < $item->maxwidth ) {
				$image->parentNode->setAttribute( 'style', esc_attr( 'float:left;max-width:' . $item->halfwidth . 'px;' ) );
			}
			// at this point, we have left large images, and smaller images with alignnone or center.
			// and if someone used aligncenter/none with a small image, they deserve what they get.
			else {
				$image->parentNode->setAttribute( 'style', esc_attr( 'margin:0 auto;max-width:' . $item->maxwidth . 'px;' ) );
			}
		}
	}


	/**
	 * Get the ID of each image dynamically.
	 *
	 * @since 2.1.0
	 *
	 * @author Philip Newcomer
	 * @link   http://philipnewcomer.net/2012/11/get-the-attachment-id-from-an-image-url-in-wordpress/
	 */
	protected function get_image_id( $attachment_url ) {
		global $wpdb;
		$attachment_id = false;

		// Get the upload directory paths
		$upload_dir_paths = wp_upload_dir();

		// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
		if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

			// If this is the URL of an auto-generated thumbnail, get the URL of the original image
			$attachment_url = preg_replace( '(-\d{3,4}x\d{3,4}.)', '.', $attachment_url );

			// Remove the upload path base directory from the attachment URL
			$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

			// Finally, run a custom database query to get the attachment ID from the modified attachment URL
			$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );

		}

		return $attachment_id;
	}
}

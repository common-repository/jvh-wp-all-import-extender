<?php

namespace JVH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VcImage
{
	public $shortcode;	

	public function __construct( $shortcode )
	{
		$this->shortcode = $shortcode;
	}

	public function addImageUrl() : void
	{
		$image_url = $this->getImageUrlById();

		$this->shortcode = str_replace( ']', " image_url=\"$image_url\"]", $this->shortcode );
	}

	public function addMissingImage() : void
	{
		if ( $this->hasAddedImageUrl() && $this->isMissingImage()  ) {
			$new_image_id = $this->uploadMissingImage();

			if ( is_numeric( $new_image_id ) ) {
				$this->changeImageId( $new_image_id );
			}
			if ( is_wp_error( $new_image_id ) ) {
				error_log( 'Can\'t upload image: ' . $this->getAddedImageUrl() );
			}
		}

		$this->removeImageUrl();
	}

	private function hasAddedImageUrl() : bool
	{
		return strpos( $this->shortcode, 'image_url=' ) !== false;
	}

	private function uploadMissingImage()
	{
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		if ( $this->isSvgAddedImage() ) {
			$this->addSvgSupport();
		}

		return media_sideload_image( $this->getAddedImageUrl(), null, null, 'id' );
	}

	private function isSvgAddedImage() : bool
	{
		return strpos( $this->getAddedImageUrl(), '.svg' ) !== false;
	}

	private function addSvgSupport() : void
	{
		add_filter( 'image_sideload_extensions', function( array $allowed_extensions ) {
			$allowed_extensions[] = 'svg';

			return $allowed_extensions;
		} );
	}

	private function getAddedImageUrl() : string
	{
		preg_match( '/image_url="(.*)"/U', $this->shortcode, $matches );

		return $matches[1];
	}

	private function changeImageId( int $new_image_id ) : void
	{
		$this->shortcode = preg_replace( '/image=".*"/U', "image=\"$new_image_id\"", $this->shortcode );
	}

	private function removeImageUrl() : void
	{
		$this->shortcode = preg_replace( '/ image_url=".*"/U', '', $this->shortcode );
	}

	public function isMissingImage() : bool
	{
		if ( ! wp_attachment_is_image( $this->getId() ) ) {
			return true;
		}

		if ( basename( $this->getAddedImageUrl() ) !== basename( $this->getImageUrlById() ) ) {
			return true;
		}

		return false;
	}

	private function getImageUrlById() : string
	{
		return wp_get_attachment_url( $this->getId() );
	}

	private function getId() : int
	{
		preg_match( '/image="(.*)"/U', $this->shortcode, $matches );

		return (int) $matches[1];
	}
}

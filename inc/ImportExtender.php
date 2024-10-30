<?php

namespace JVH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ImportExtender
{
	public $post_content;

	public function __construct( $post_content )
	{
		$this->post_content = $post_content;
	}

	public function importMissingData() : void
	{
		$this->importMissingVcImages();
		$this->importMissingVcSnippets();
	}

	private function importMissingVcImages() : void
	{
		$this->post_content = preg_replace_callback( '/\[vc_single_image.*]/U', function( array $matches ) {
			$vc_image = new \JVH\VcImage( $matches[0] );
			$vc_image->addMissingImage();

			return $vc_image->shortcode;
		}, $this->post_content );
	}

	private function importMissingVcSnippets() : void
	{
		$this->post_content = preg_replace_callback( '/\[vc-vc-snippet.*]/U', function( array $matches ) {
			$vc_snippet = new \JVH\VcSnippet( $matches[0] );
			$vc_snippet->addMissingSnippet();

			return $vc_snippet->shortcode;
		}, $this->post_content );
	}
}

add_filter( 'pmxi_article_data', function( $article, $import, $post_to_update, $current_xml_node ) {
	$import_extender = new \JVH\ImportExtender( $article['post_content'] );
	$import_extender->importMissingData();

	$article['post_content'] = $import_extender->post_content;

    return $article;
}, 10, 4 );

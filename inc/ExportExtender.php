<?php

namespace JVH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ExportExtender
{
	public $post_content;

	public function __construct( $post_content )
	{
		$this->post_content = $post_content;
	}

	public function addExtraData() : void
	{
		$this->addVcImageUrls();
		$this->addVcSnippetContents();
	}

	private function addVcImageUrls() : void
	{
		$this->post_content = preg_replace_callback( '/\[vc_single_image.*]/U', function( array $matches ) {
			$vc_image = new \JVH\VcImage( $matches[0] );
			$vc_image->addImageUrl();

			return $vc_image->shortcode;
		}, $this->post_content );
	}

	private function addVcSnippetContents() : void
	{
		$this->post_content = preg_replace_callback( '/\[vc-vc-snippet.*]/U', function( array $matches ) {
			$vc_snippet = new \JVH\VcSnippet( $matches[0] );
			$vc_snippet->addContentBase64();
			$vc_snippet->addTitleBase64();

			return $vc_snippet->shortcode;
		}, $this->post_content );
	}
}

add_filter( 'wp_all_export_csv_rows', function( $articles, $options, $export_id ) {
	foreach ( $articles as $key => $article ) {
		$export_extender = new \JVH\ExportExtender( $articles[$key]['Content'] );
		$export_extender->addExtraData();

		$articles[$key]['Content'] = $export_extender->post_content;
	}

	return $articles;
}, 10, 3 );

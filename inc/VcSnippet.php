<?php

namespace JVH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VcSnippet
{
	public $shortcode;	

	public function __construct( $shortcode )
	{
		$this->shortcode = $shortcode;
	}

	public function addContentBase64() : void
	{
		$content_base64 = $this->getContentBase64ById();

		$this->shortcode = str_replace( ']', " content_base64=\"$content_base64\"]", $this->shortcode );
	}

	public function addTitleBase64() : void
	{
		$title_base64 = $this->getTitleBase64ById();

		$this->shortcode = str_replace( ']', " title_base64=\"$title_base64\"]", $this->shortcode );
	}

	public function addMissingSnippet() : void
	{
		if ( $this->isSnippetWithAddedTitle() ) {
			$snippet_post = $this->getSnippetWithExtraTitle();

			$this->changeSnippetId( $snippet_post->ID );
		}
		elseif ( $this->hasAddedSnippetContent() && $this->isMissingSnippet() ) {
			$new_snippet_id = $this->insertMissingSnippet();

			$this->changeSnippetId( $new_snippet_id );
		}

		$this->removeSnippetContent();
		$this->removeSnippetTitle();
	}

	private function hasAddedSnippetContent() : bool
	{
		return strpos( $this->shortcode, 'content_base64=' ) !== false;
	}

	private function isMissingSnippet() : bool
	{
		return get_post_type( $this->getId() ) !== 'vc_snippet';
	}

	private function isSnippetWithAddedTitle() : bool
	{
		return ! is_null( $this->getSnippetWithExtraTitle() );
	}

	private function getSnippetWithExtraTitle()
	{
		return get_page_by_title( $this->getAddedTitle(), OBJECT, 'vc_snippet' );
	}

	private function insertMissingSnippet() : int
	{
		$import_extender = new \JVH\ImportExtender( $this->getAddedContent() );
		$import_extender->importMissingData();

		return wp_insert_post( [
			'post_title' => $this->getAddedTitle(),
			'post_content' => $import_extender->post_content,
			'post_type' => 'vc_snippet',
			'post_status' => 'publish',
		] );
	}

	private function changeSnippetId( int $new_snippet_id ) : void
	{
		$this->shortcode = preg_replace( '/id=".*"/U', "id=\"$new_snippet_id\"", $this->shortcode );
	}

	private function getAddedTitle() : string
	{
		return base64_decode( $this->getAddedTitleBase64() );
	}

	private function getAddedTitleBase64() : string
	{
		preg_match( '/title_base64="(.*)"/U', $this->shortcode, $matches );

		return $matches[1];
	}

	private function getAddedContent() : string
	{
		return base64_decode( $this->getAddedContentBase64() );
	}

	private function getAddedContentBase64() : string
	{
		preg_match( '/content_base64="(.*)"/U', $this->shortcode, $matches );

		return $matches[1];
	}

	private function removeSnippetContent() : void
	{
		$this->shortcode = preg_replace( '/ content_base64=".*"/U', '', $this->shortcode );
	}

	private function removeSnippetTitle() : void
	{
		$this->shortcode = preg_replace( '/ title_base64=".*"/U', '', $this->shortcode );
	}

	private function getContentBase64ById() : string
	{
		return base64_encode( $this->getExtendedContentById() );
	}

	/*
	 * To do: recursive snippet in snippet in snippet...
	 */
	private function getExtendedContentById() : string
	{
		$export_extender = new \JVH\ExportExtender( $this->getContentById() );
		$export_extender->addExtraData();

		return $export_extender->post_content;
	}

	private function getContentById() : string
	{
		return get_post_field( 'post_content', $this->getId() );
	}

	private function getTitleBase64ById() : string
	{
		return base64_encode( $this->getTitleById() );
	}

	private function getTitleById() : string
	{
		return get_the_title( $this->getId() );
	}

	private function getId() : int
	{
		preg_match( '/id="(.*)"/U', $this->shortcode, $matches );

		return (int) $matches[1];
	}
}

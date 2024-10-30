<?php

namespace JVH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists('ACF') ) {
    return;
}

class ImportAcfRepeaterExtender
{
	private $post_id;
	private $repeater_json;
	private $repeater_data;

	public function __construct( $post_id, $repeater_json )
	{
		$this->post_id = $post_id;
		$this->repeater_json = $repeater_json;
		$this->repeater_data = $this->getRepeaterData();
	}

	public function addData() : void
	{
		$this->safeJson();

		foreach ( $this->repeater_data as $field_name => $repeater_data ) {
			update_field( $field_name, $repeater_data, $this->post_id );
		}
	}

	private function safeJson() : void
	{
		add_post_meta( $this->post_id, 'repeater_data', $this->repeater_json );
	}

	private function getRepeaterData() : array
	{
		return json_decode( $this->repeater_json, true );
	}
}

add_action( 'pmxi_saved_post', function( $post_id, $xml_node, $is_update ) {
	// Xml object to array
    $row = json_decode( json_encode( ( array ) $xml_node ), 1 );
	$repeater_json = $row['acf_repeater_data'];

	if ( empty( $repeater_json ) ) {
		return;
	}

	$import_repater_extender = new \JVH\ImportAcfRepeaterExtender( $post_id, $repeater_json );
	$import_repater_extender->addData();
}, 10, 3 );

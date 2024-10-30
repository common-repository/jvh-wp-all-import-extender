<?php

namespace JVH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists('ACF') ) {
    return;
}

class ExportAcfRepeaterExtender
{
	private $post_id;
	private $repeater_fields;

	public function __construct( $post_id )
	{
		$this->post_id = $post_id;
		$this->repeater_fields = $this->getRepeaterFields();
	}

	public function hasRepeaterFields() : bool
	{
		return count( $this->repeater_fields ) > 0;
	}

	public function getRepeaterDataJson() : string
	{
		return json_encode( $this->getRepeaterData() );
	}

	private function getRepeaterData() : array
	{
		$data = [];

		foreach ( $this->repeater_fields as $repeater_field ) {
			$data[$repeater_field['name']] = get_field( $repeater_field['name'], $this->post_id, false );
		}

		return $data;
	}

	private function getRepeaterFields() : array
	{
		$repeaters = [];
		
		foreach ( $this->getAcfFields() as $field ) {
			if ( $field['type'] !== 'repeater' ) {
				continue;
			}
			
			$repeaters[] = $field;
		}
		
		return $repeaters;
	}

	private function getAcfFields() : array
	{
		return get_field_objects( $this->post_id, false );
	}
}

add_filter( 'wp_all_export_csv_headers', function( $headers ) {
    $headers[] = 'acf_repeater_data';
    
	return $headers;
}); 

add_filter( 'wp_all_export_csv_rows', function( $articles, $options, $export_id ) {
	foreach ( $articles as $key => $article ) {
		$export_repeater_extender = new \JVH\ExportAcfRepeaterExtender( $articles[$key]['ID'] );
        
		if ( $export_repeater_extender->hasRepeaterFields() ) {
			$articles[$key]['acf_repeater_data'] = $export_repeater_extender->getRepeaterDataJson();
		}
        else {
            $articles[$key]['acf_repeater_data'] = '';
        }
	}

	return $articles;
}, 11, 3 );

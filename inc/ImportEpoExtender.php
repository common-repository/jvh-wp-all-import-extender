<?php

namespace JVH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ImportEpoExtender
{
	public $tm_meta;
    
	public function __construct( $tm_meta )
	{
		$this->tm_meta = $tm_meta;
	}

	public function correctData()
	{
		$this->correctProductIdsBySkus();
		$this->importMissingDataSubtitle();
	}
    
	private function correctProductIdsBySkus() : void
	{   
        foreach ( $this->tm_meta['tmfbuilder']['product_productskus'] as $skus_key => $product_skus ) {            
            foreach ( $product_skus as $sku_key => $product_sku ) {
                $new_product_id = wc_get_product_id_by_sku( $product_sku );
                
                if ( $new_product_id != 0) {
                	$this->tm_meta['tmfbuilder']['product_productids'][$skus_key][$sku_key] = $new_product_id;
                }
            }
        }

        foreach ( $this->tm_meta['tmfbuilder']['product_default_value_sku'] as $key => $sku ) {            
			$new_product_id = wc_get_product_id_by_sku( $sku );

			if ( $new_product_id != 0) {
				$this->tm_meta['tmfbuilder']['product_default_value'][$key] = $new_product_id;
			}
        }
	}

	private function importMissingDataSubtitle()
	{
        foreach ( $this->tm_meta['tmfbuilder']['product_header_subtitle'] as $key => $subtitle_content ) {
			$import_extender = new \JVH\ImportExtender( $subtitle_content );
			$import_extender->importMissingData();

			$this->tm_meta['tmfbuilder']['product_header_subtitle'][$key] = $import_extender->post_content;
        }
	}
}

add_filter( 'pmxi_custom_field', function( $value, $post_id, $key, $original_value, $existing_meta_keys, $import_id ) {
    if ( $key !== 'tm_meta' ) {
        return $value;
    }
        
	$import_epo_extender = new \JVH\ImportEpoExtender( $value );
	$import_epo_extender->correctData();
  
	return $import_epo_extender->tm_meta;
}, 10, 6 );

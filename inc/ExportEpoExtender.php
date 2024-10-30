<?php

namespace JVH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ExportEpoExtender
{
	public $tm_meta;
    
	public function __construct( $tm_meta )
	{
		$this->tm_meta = $tm_meta;
	}

	public function addExtraData() : void
	{
		$this->addSkus();
		$this->addExtraDataSubtitle();
	}
    
	private function addSkus() : void
	{
        $tm_meta = unserialize( $this->tm_meta );

        $tm_meta['tmfbuilder']['product_productskus'] = [];
        $tm_meta['tmfbuilder']['product_default_value_sku'] = [];
        
        foreach ( $tm_meta['tmfbuilder']['product_productids'] as $product_ids ) {
            $skus = [];
            
            foreach ( $product_ids as $product_id ) {
                $product = wc_get_product( $product_id );

				if ( is_bool( $product ) ) {
					$skus[] = 'no-product-found';
				}
				else {
					$skus[] = $product->get_sku();
				}
            }
            
            $tm_meta['tmfbuilder']['product_productskus'][] = $skus;
        }

        foreach ( $tm_meta['tmfbuilder']['product_default_value'] as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( is_bool( $product ) ) {
				$tm_meta['tmfbuilder']['product_default_value_sku'][] = 'no-product-found';
			}
			else {
				$tm_meta['tmfbuilder']['product_default_value_sku'][] = $product->get_sku();
			}
        }
        
		$this->tm_meta = serialize( $tm_meta );
	}

	/*
	 * Subtitle often contains VC snippets.
	 * These are stored as id and don't work on import.
	 * Add snippet content and title so the snippet can be imported.
	 */
	private function addExtraDataSubtitle() : void
	{
        $tm_meta = unserialize( $this->tm_meta );
        
        foreach ( $tm_meta['tmfbuilder']['product_header_subtitle'] as $key => $subtitle_content ) {
			$export_extender = new \JVH\ExportExtender( $subtitle_content );
			$export_extender->addExtraData();

			$tm_meta['tmfbuilder']['product_header_subtitle'][$key] = $export_extender->post_content;
        }
        
		$this->tm_meta = serialize( $tm_meta );
	}
}

add_filter( 'wp_all_export_csv_rows', function( $articles, $options, $export_id ) {
	foreach ( $articles as $key => $article ) {
		$export_epo_extender = new \JVH\ExportEpoExtender( $articles[$key]['tm_meta'] );
		$export_epo_extender->addExtraData();
        
		$articles[$key]['tm_meta'] = $export_epo_extender->tm_meta;
	}
	return $articles;
}, 10, 3 );

<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;


/**
 * Wrapper for wc_get_products(), add some custom args.
 *
 * @since 4.4.0
 *
 * @see wc_get_products()
 *
 * @param array $args
 *
 * @return array|stdClass
 */
function aw_get_products( $args ) {
	add_filter( 'woocommerce_product_data_store_cpt_get_products_query', 'aw_filter_get_products_query_args', 10, 2 );

	$products = wc_get_products( $args );

	remove_filter( 'woocommerce_product_data_store_cpt_get_products_query', 'aw_filter_get_products_query_args', 10 );

	return $products;
}


/**
 * Filters the WC get products query args.
 *
 * Adds orderby popularity option.
 *
 * @since 4.4.0
 *
 * @param array $query      Args for WP_Query.
 * @param array $query_vars Query vars from WC_Product_Query.
 *
 * @return array
 */
function aw_filter_get_products_query_args( $query, $query_vars ) {
	if ( $query_vars['orderby'] === 'popularity' ) {
		$query['meta_key'] = 'total_sales';
		$query['orderby']  = 'meta_value_num';
	}
	return $query;
}


/**
 * Function that returns an array containing the IDs of the recent products.
 *
 * @since 2.1.0
 *
 * @param int $limit
 * @return array
 */
function aw_get_recent_product_ids( $limit = -1 ) {
	$query = new WP_Query([
		'post_type' => 'product',
		'posts_per_page' => $limit,
		'post_status' => 'publish',
		'ignore_sticky_posts' => 1,
		'no_found_rows' => true,
		'orderby' => 'date',
		'order' => 'desc',
		'fields' => 'ids',
		'meta_query' => WC()->query->get_meta_query(),
		'tax_query' => WC()->query->get_tax_query()
	]);
	return $query->posts;
}


/**
 * Function that returns an array containing the IDs of the recent products.
 *
 * @since 3.2.5
 *
 * @param int $limit
 * @return array
 */
function aw_get_top_selling_product_ids( $limit = -1 ) {
	$query = new WP_Query([
		'post_type' => 'product',
		'posts_per_page' => $limit,
		'post_status' => 'publish',
		'ignore_sticky_posts' => 1,
		'no_found_rows' => true,
		'fields' => 'ids',
		'meta_key' => 'total_sales',
		'orderby' => 'meta_value_num',
		'order' => 'desc',
		'tax_query' => WC()->query->get_tax_query(),
		'meta_query' => WC()->query->get_meta_query()
	]);

	return $query->posts;
}

<?php
/*
Plugin Name: Books To Products View
Description: Creates a view for easy access to books associated with products and vice versa
Author: Vladislav K.
Version: 1.0
*/

/**
 * Class Books2Products
 * A 'book' is a custom post type. A 'product' is a standard Woocommerce product type. A book has a one-to-one
 * relationship with a product, but needs to be distinct. This class allows us to show some product properties
 * on a book-type page and vice versa.
 */

class Books2Products {

    /**
     * Shows some book and product meta properties based on a custom field 'product_ref' that belongs to
     * 'book' post type.
     * @return mixed
     */
    public static function get_book_to_product_array() {
        global $wpdb;
        $query = "SELECT sel1.book_id, sel1.product_id, meta_price.meta_value AS price FROM
                    (
                      SELECT books.ID AS book_id, books.post_title AS product_name, products.ID AS product_id
                      FROM $wpdb->posts books
                      INNER JOIN $wpdb->postmeta AS postmeta ON books.ID = postmeta.post_id
                      INNER JOIN $wpdb->posts products ON products.ID = postmeta.meta_value
                      WHERE postmeta.meta_key = 'product_ref'
                    ) AS sel1
                  INNER JOIN $wpdb->postmeta AS meta_price ON sel1.product_id = meta_price.post_id
                  WHERE meta_price.meta_key = '_regular_price';";
        $result = $wpdb->get_results($query, ARRAY_A);
        return $result;
    }

    /**
     * A helper function used in order to avoid unnecessary loops in a template file. It rearranges the result of
     * get_book_to_product_array() by replacing generic keys with book or product ids.
     * @param $column
     * @return array|bool
     */
    public static function result_by_column($column) {
        if (false === in_array($column, ['product_id', 'book_id'])) {
            return false;
        }
        $result = self::get_book_to_product_array();
        if (false === is_array($result)) {
            return false;
        }
        $new_result = [];
        foreach ($result as $record) {
            $new_result[$record[$column]] = $record;
        }
        return $new_result;
    }

    /**
     * Allows to get formats of all downloadable files associated with a product based on book or product ID.
     * @param WC_Product $wc_product_obj
     * @return bool|string
     */
    public static function get_product_file_format(WC_Product $wc_product_obj) {
        $product_files = [];
        $product_files = $wc_product_obj->get_files();
        $formats = [];
        if (false === sizeof($product_files)) {
            return false;
        }
        foreach ($product_files as $product_file) {
            $formats[] = preg_replace('/^.*\\.(\\w+)$/i', '$1', $product_file['file']);
        }
        $format = strtoupper(implode(' / ', $formats));
        return $format;
    }
}
?>

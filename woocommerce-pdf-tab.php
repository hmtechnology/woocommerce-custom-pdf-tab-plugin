<?php 
/*
Plugin Name: WooCommerce Custom PDF Tab
Description: Add a custom tab to My Account page where users can download personalized PDF Documents [Don't forget to save permalink settings after installing this plugin!]
Version: 1.0
Author: hmtechnology
Author URI: https://github.com/hmtechnology
License: GNU General Public License v3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
Plugin URI: https://github.com/hmtechnology/woocommerce-custom-pdf-tab-plugin
*/

// Add endpoint for the "Documents" tab
function custom_add_documents_endpoint() {
    add_rewrite_endpoint( 'documents', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'custom_add_documents_endpoint' );

// Add query var for the "documents" endpoint
function custom_documents_query_vars( $vars ) {
    $vars[] = 'documents';
    return $vars;
}
add_filter( 'query_vars', 'custom_documents_query_vars', 0 );

// Add "Documents" link to My Account menu
function custom_add_documents_link_my_account( $items ) {
    $items['documents'] = 'Documents';
    return $items;
}
add_filter( 'woocommerce_account_menu_items', 'custom_add_documents_link_my_account' );

// Display Custom PDF Documents Content for every user
function custom_documents_content() {
    $user_id = get_current_user_id();

    $documents = get_posts( array(
        'post_type'      => 'attachment',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => 'customer_id',
                'value' => $user_id,
            ),
        ),
        'post_mime_type' => 'application/pdf',
    ) );

    if ( $documents ) {
        echo '<h2>Documenti</h2>';
        echo '<ul>';
        foreach ( $documents as $document ) {
            $document_url = wp_get_attachment_url( $document->ID );
            echo '<li><a href="' . esc_url( $document_url ) . '" download>' . esc_html( $document->post_title ) . '</a></li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No PDF Available.</p>';
    }
}
add_action( 'woocommerce_account_documents_endpoint', 'custom_documents_content' );

// Add a custom field "customer_id" to media documents
function add_customer_id_field_to_media( $form_fields, $post ) {
    $form_fields['customer_id'] = array(
        'label' => 'Customer ID',
        'input' => 'text',
        'value' => get_post_meta( $post->ID, 'customer_id', true ),
    );
    return $form_fields;
}
add_filter( 'attachment_fields_to_edit', 'add_customer_id_field_to_media', 10, 2 );

// Save the value of the custom field "customer_id" when loading a media document
function save_customer_id_field_to_media( $post, $attachment ) {
    if( isset( $attachment['customer_id'] ) ) {
        update_post_meta( $post['ID'], 'customer_id', sanitize_text_field( $attachment['customer_id'] ) );
    }
    return $post;
}
add_filter( 'attachment_fields_to_save', 'save_customer_id_field_to_media', 10, 2 );

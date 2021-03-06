<?php
/**
 * Plugin Name:       Our Metabox
 * Plugin URI:        https://example.com/plugins/our-metabox/
 * Description:       This is practise plugin.
 * Version:           1.0
 * Author:            Sohan
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       our-metabox
 * Domain Path:       /languages
 */

 class OurMetabox{
    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'omb_load_textdomain' ) );
        // add_action( 'plugins_loaded', 'pqrc_load_textdomain');
        add_action( 'admin_menu', array( $this, 'omb_add_metabox' ) );
        add_action( 'save_post', array( $this, 'omb_save_metabox' ) );
    }

    private function is_secured( $nonce_field, $action, $post_id ) {
        $nonce = isset($_POST[$nonce_field]) ? $_POST[$nonce_field] : '';
        
        if ( $nonce == '' ) {
            return false;
        }
        if ( !wp_verify_nonce( $nonce, $action ) ) {
            return false;
        }
        if ( !current_user_can( 'edit_post', $post_id ) ) {
            return false;
        }
        if ( wp_is_post_autosave($post_id) ) {
            return false;
        } 
        if ( wp_is_post_revision($post_id) ) {
            return false;
        }

        return true;

    }

    // After Submit Post; After Saved Page;
    function omb_save_metabox( $post_id ){

        if ( !$this->is_secured( 'omb_location_field', 'omb_location', $post_id ) ) {
            return $post_id;
        }

        $location       = isset( $_POST['omb_location'] ) ? $_POST['omb_location'] : '';
        $country        = isset( $_POST['omb_country'] ) ? $_POST['omb_country'] : '';
        $is_favorite    = isset( $_POST['omb_is_favorite'] ) ? $_POST['omb_is_favorite'] : 0;
        $colors         = isset( $_POST['omb_clr'] ) ? $_POST['omb_clr'] : array();

        if ( $location == '' || $country == '') {
            return $post_id;
        }
        $location = sanitize_text_field( $location );
        $country = sanitize_text_field( $country );
        
        update_post_meta( $post_id, 'omb_location', $location );
        update_post_meta( $post_id, 'omb_country', $country );
        update_post_meta( $post_id, 'omb_is_favorite', $is_favorite );
        update_post_meta( $post_id, 'omb_clr', $colors );
    }  

    function omb_add_metabox() {
        add_meta_box(
            'omb_post_location',
            __( 'Location Info', 'our-metabox' ),
            array( $this, 'omb_display_metabox' ),
            array( 'post', 'page' )
        );
    }

    function omb_display_metabox ( $post ) {
        $location       = get_post_meta ( $post->ID, 'omb_location', true );
        $country        = get_post_meta ( $post->ID, 'omb_country', true );
        $is_favorite    = get_post_meta ( $post->ID, 'omb_is_favorite', true );
        $checked        = $is_favorite == 1 ? 'checked' : '';

        $saved_colors = get_post_meta ( $post->ID, 'omb_clr', true );

        $label1 = __( 'Location ', 'our-metabox' );
        $label2 = __( 'Country ', 'our-metabox' );
        $label3 = __( 'Is favorite', 'our-metabox' );
        $label4 = __( 'Colors', 'our-metabox' );

        $colors = array( 'red', 'green', 'blue', 'yellow', 'black', 'megenta', 'pink' );

        wp_nonce_field( 'omb_location', 'omb_location_field' );
        wp_nonce_field( 'omb_country', 'omb_location_field' );

        $metabox_html = <<<EOD
        <p>
            <label for="omb_location">{$label1}: </label>
            <input type="text" name="omb_location" id="omb_location" value="{$location}" />
        </p>
        <p>
            <label for="omb_country">{$label2}: </label>
            <input type="text" name="omb_country" id="omb_country" value="{$country}" />
        </p>
        <p>
            <label for="omb_is_favorite">{$label3}:</label>
            <input type="checkbox" name="omb_is_favorite" id="omb_is_favorite" value="1" {$checked} />
        </p>
        <p>
            <label>{$label4}: </label>
        EOD;

        foreach ( $colors as $color ) {
            $checked1 = in_array( $color, $saved_colors ) ? 'checked' : '';
            $metabox_html .= <<<EOD
            <label for="omb_clr_{$color}">{$color}</label>
            <input type="checkbox" name="omb_clr[]" id="omb_clr_{$color}" value="{$color}" {$checked1} />
            EOD;
        }

        $metabox_html .= "</p>"; 
        echo $metabox_html;
    }
    // 4.46 mins

    function omb_load_textdomain(){
        load_plugin_textdomain( 'posts-to-qrcode', false, dirname(__FILE__) . "/languages" );
    }
 }

 new OurMetabox();
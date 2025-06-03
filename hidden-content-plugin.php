<?php
/*
Plugin Name: Gizli İçerik Eklentisi
Description: Belirlenen yazı veya kategorilerin içeriğini gizler ve yerine özel mesaj ve resim gösterir.
Version: 1.0
Author: Yiğit Serin
*/

if (!defined('ABSPATH')) {
    exit; // Do not allow direct access
}

// Ayar sayfası dosyasını dahil et
require_once plugin_dir_path(__FILE__) . 'admin-settings.php';

// İçeriği gizleme filtresi
function gice_gizli_icerik_filtre($content) {
    // Sadece frontend'de çalışsın
    if (is_user_logged_in() || (defined('REST_REQUEST') && REST_REQUEST) || (defined('WP_CLI') && WP_CLI) || (defined('EP_IS_DOING_INDEX') && EP_IS_DOING_INDEX)) {
        return $content;
    }
    // Ayarları al
    $options = get_option('gice_settings');
    if (!$options) return $content;

    // Eklenti aktif değilse gizleme yapma
    if (empty($options['gice_plugin_active'])) {
        return $content;
    }
    // Googlebot ayarı aktifse ve googlebot ise gizleme yapma
    if (!empty($options['gice_googlebot_active']) && isset($_SERVER['HTTP_USER_AGENT']) && strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'googlebot') !== false) {
        return $content;
    }

    global $post;
    if (!$post) return $content;

    // Yazı meta alanı (_gice_gizli) kontrolü
    $is_gizli = false;
    if (get_post_meta($post->ID, '_gice_gizli', true)) {
        $is_gizli = true;
    } else {
        // Kategori meta alanı (_gice_gizli) kontrolü
        $post_kategoriler = wp_get_post_categories($post->ID);
        foreach ($post_kategoriler as $cat_id) {
            if (get_term_meta($cat_id, '_gice_gizli', true)) {
                $is_gizli = true;
                break;
            }
        }
    }
    $ozel_mesaj = isset($options['ozel_mesaj']) ? $options['ozel_mesaj'] : '';
    $ozel_resim = isset($options['ozel_resim']) ? $options['ozel_resim'] : '';

    if ($is_gizli) {
        $output = '<div class="gice-gizli-icerik">';
        if (!empty($ozel_resim)) {
            $output .= '<img src="' . esc_url($ozel_resim) . '" alt="Gizli İçerik Resmi" style="max-width:100%;height:auto;" />';
        }
        if (!empty($ozel_mesaj)) {
            $output .= '<div class="gice-ozel-mesaj">' . wp_kses_post($ozel_mesaj) . '</div>';
        }
        $output .= '</div>';
        return $output;
    }
    return $content;
}
add_filter('the_content', 'gice_gizli_icerik_filtre');

// ElasticPress ile yazının veya kategorilerinin gizli olup olmadığını post meta.hidden_content olarak ekle
add_filter( 'ep_post_sync_args', function( $args, $post_id ) {
    if ( get_post_type( $post_id ) !== 'post' ) {
        return $args;
    }

    $gizli_post = get_post_meta( $post_id, '_gice_gizli', true );
    $gizli_kategori = false;
    if(!$gizli_post){
        $cat_ids = wp_get_post_categories( $post_id );
        foreach ( $cat_ids as $cat_id ) {
            if ( get_term_meta( $cat_id, '_gice_gizli', true ) ) {
                $gizli_kategori = true;
                break;
            }
        }
    }
    $value = ($gizli_post || $gizli_kategori) ? 1 : 0;
    $args['meta']['hidden_content'] =  [
        'value' => (string)$value,
        'raw' => $value,
        'boolean' => boolval($value)
    ];
    return $args;
}, 10, 2 );

// Stil ekle
// Medya kütüphanesi için script ekle (sadece admin)
if (is_admin()) {
    add_action('admin_enqueue_scripts', function() {
        wp_enqueue_media();
        wp_enqueue_script('gice-media', plugin_dir_url(__FILE__).'media.js', array('jquery'), null, true);
    });
}

function gice_gizli_icerik_styles() {
    echo '<style>.gice-gizli-icerik{text-align:center;padding:20px;background:#f7f7f7;border:1px solid #eee;border-radius:8px;}.gice-ozel-mesaj{margin-top:10px;font-size:18px;color:#333;}</style>';
}
add_action('wp_head', 'gice_gizli_icerik_styles');

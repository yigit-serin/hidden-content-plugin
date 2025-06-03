<?php
// Yönetici paneli ayar sayfası
add_action('admin_menu', 'gice_admin_menu');
function gice_admin_menu() {
    add_options_page(
        'Gizli İçerik Ayarları',
        'Gizli İçerik Ayarları',
        'manage_options',
        'gice-settings',
        'gice_settings_page'
    );
}

add_action('admin_init', 'gice_settings_init');
function gice_settings_init() {
    register_setting('gice_settings_group', 'gice_settings', array(
        'sanitize_callback' => function($input) {
            return array(
                'gice_plugin_active' => !empty($input['gice_plugin_active']) ? 1 : 0,
                'gice_googlebot_active' => !empty($input['gice_googlebot_active']) ? 1 : 0,
                'ozel_resim' => esc_url_raw($input['ozel_resim'] ?? ''),
                'ozel_resim_link' => esc_url_raw($input['ozel_resim_link'] ?? ''),
            );
        }
    ));

    add_settings_section(
        'gice_settings_section',
        'Gizli İçerik Ayarları',
        '',
        'gice-settings'
    );

    add_settings_field(
        'gice_plugin_active',
        'Eklenti Durumu',
        'gice_plugin_active_field',
        'gice-settings',
        'gice_settings_section'
    );
    add_settings_field(
        'gice_googlebot_active',
        'Google Bot İçin Gizleme',
        'gice_googlebot_active_field',
        'gice-settings',
        'gice_settings_section'
    );
    add_settings_field(
        'ozel_resim_ve_link',
        'Gizli İçerik Resmi ve Bağlantısı',
        'gice_resim_ve_link_field',
        'gice-settings',
        'gice_settings_section'
    );
}

function gice_resim_ve_link_field() {
    $options = get_option('gice_settings');
    ?>
    <label for="ozel_resim"><strong>Gizli İçerik Resmi:</strong></label><br />
    <input type="text" name="gice_settings[ozel_resim]" id="ozel_resim" value="<?php echo esc_attr($options['ozel_resim'] ?? ''); ?>" style="width:70%" />
    <button type="button" class="button" id="gice_resim_sec">Resim Seç</button>
    <p class="description">Medya kütüphanesinden bir resim seçin.</p>
    <script>
    jQuery(document).ready(function($){
        var frame;
        $('#gice_resim_sec').on('click', function(e){
            e.preventDefault();
            if (frame) { frame.open(); return; }
            frame = wp.media({
                title: 'Resim Seç',
                button: { text: 'Kullan' },
                multiple: false
            });
            frame.on('select', function(){
                var attachment = frame.state().get('selection').first().toJSON();
                $('#ozel_resim').val(attachment.url);
            });
            frame.open();
        });
    });
    </script>
    <br />
    <label for="ozel_resim_link"><strong>Resme Verilecek Bağlantı (URL):</strong></label><br />
    <input type="url" name="gice_settings[ozel_resim_link]" id="ozel_resim_link" value="<?php echo esc_attr($options['ozel_resim_link'] ?? ''); ?>" style="width:70%" />
    <p class="description">Resme tıklanınca gidilecek URL (isteğe bağlı).</p>
    <?php
}

function gice_plugin_active_field() {
    $options = get_option('gice_settings');
    $checked = !empty($options['gice_plugin_active']) ? 'checked' : '';
    echo '<input type="checkbox" name="gice_settings[gice_plugin_active]" value="1" ' . $checked . ' /> Aktif';
}

function gice_googlebot_active_field() {
    $options = get_option('gice_settings');
    $checked = !empty($options['gice_googlebot_active']) ? 'checked' : '';
    echo '<input type="checkbox" name="gice_settings[gice_googlebot_active]" value="1" ' . $checked . ' /> Google bot geldiğinde içerik gizlenmesin';
}

// Yazı ve kategori detayına meta kutu ekle
add_action('add_meta_boxes', function() {
    add_meta_box('gice_gizli_metabox', 'Gizli İçerik', 'gice_gizli_metabox_callback', 'post', 'side');
});
function gice_gizli_metabox_callback($post) {
    $checked = get_post_meta($post->ID, '_gice_gizli', true) ? 'checked' : '';
    echo '<label><input type="checkbox" name="gice_gizli" value="1" ' . $checked . '> Bu yazı gizli içerik olarak işaretlensin</label>';
}
add_action('save_post', function($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['gice_gizli'])) {
        update_post_meta($post_id, '_gice_gizli', 1);
    } else {
        delete_post_meta($post_id, '_gice_gizli');
    }
});
// Kategori detayına alan ekle
add_action('category_add_form_fields', function() {
    echo '<div class="form-field"><label><input type="checkbox" name="gice_gizli_kategori" value="1"> Gizli içerik kategorisi</label></div>';
});
add_action('category_edit_form_fields', function($term) {
    $checked = get_term_meta($term->term_id, '_gice_gizli', true) ? 'checked' : '';
    echo '<tr class="form-field"><th scope="row">Gizli İçerik</th><td><input type="checkbox" name="gice_gizli_kategori" value="1" ' . $checked . '> Bu kategori gizli içerik olarak işaretlensin</td></tr>';
});
add_action('created_category', function($term_id) {
    if (isset($_POST['gice_gizli_kategori'])) {
        update_term_meta($term_id, '_gice_gizli', 1);
    }
});
add_action('edited_category', function($term_id) {
    if (isset($_POST['gice_gizli_kategori'])) {
        update_term_meta($term_id, '_gice_gizli', 1);
    } else {
        delete_term_meta($term_id, '_gice_gizli');
    }
});

function gice_settings_page() {
    echo '<div class="wrap"><h1>Gizli İçerik Ayarları</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields('gice_settings_group');
    do_settings_sections('gice-settings');
    submit_button();
    echo '</form>';
    // Gizli yazıları ve kategorileri gösteren tablo
    echo '<h2>Gizli Yazılar</h2>';
    $gizli_posts = get_posts(array('meta_key'=>'_gice_gizli','meta_value'=>1,'post_type'=>'post','numberposts'=>-1));
    if ($gizli_posts) {
        echo '<table class="widefat"><tr><th>ID</th><th>Başlık</th></tr>';
        foreach($gizli_posts as $p) {
            echo '<tr><td>' . $p->ID . '</td><td>' . esc_html($p->post_title) . '</td></tr>';
        }
        echo '</table>';
    } else {
        echo '<p>Gizli yazı yok.</p>';
    }
    echo '<h2>Gizli Kategoriler</h2>';
    $gizli_kategoriler = get_terms(array('taxonomy'=>'category','hide_empty'=>false,'meta_query'=>array(array('key'=>'_gice_gizli','value'=>1))));
    if ($gizli_kategoriler) {
        echo '<table class="widefat"><tr><th>ID</th><th>İsim</th></tr>';
        foreach($gizli_kategoriler as $cat) {
            echo '<tr><td>' . $cat->term_id . '</td><td>' . esc_html($cat->name) . '</td></tr>';
        }
        echo '</table>';
    } else {
        echo '<p>Gizli kategori yok.</p>';
    }
    echo '</div>';
}
<?php
/**
 * Plugin Name: FANUC ER-4iA Robot Simulator
 * Plugin URI: https://www.davidebertolino.it
 * Description: Simulatore web interattivo del braccio robotico FANUC ER-4iA per la didattica della robotica industriale. Usa lo shortcode [fanuc_sim] per incorporare il simulatore in qualsiasi pagina.
 * Version: 1.1.0
 * Author: Davide "the Prof." Bertolino
 * Author URI: https://www.davidebertolino.it
 * License: GPL v2 or later
 * Text Domain: fanuc-sim
 */

if (!defined('ABSPATH')) exit;

define('FANUC_SIM_VERSION', '1.1.0');
define('FANUC_SIM_PATH', plugin_dir_path(__FILE__));
define('FANUC_SIM_URL', plugin_dir_url(__FILE__));

// ─────────────────────────────────────────────
// 1. SHORTCODE [fanuc_sim]
// ─────────────────────────────────────────────
function fanuc_sim_shortcode($atts) {
    $atts = shortcode_atts(array(
        'height' => '700px',
        'width'  => '100%',
        'class'  => '',
    ), $atts, 'fanuc_sim');

    $src    = FANUC_SIM_URL . 'assets/simulator.html';
    $height = esc_attr($atts['height']);
    $width  = esc_attr($atts['width']);
    $class  = esc_attr($atts['class']);

    return sprintf(
        '<div class="fanuc-sim-wrap %s" style="width:%s;height:%s;position:relative;border-radius:8px;overflow:hidden;background:#080c12;">
            <iframe src="%s"
                    style="width:100%%;height:100%%;border:none;display:block;"
                    allow="fullscreen"
                    allowfullscreen
                    loading="lazy"
                    title="">
            </iframe>
        </div>',
        $class, $width, $height, esc_url($src)
    );
}
add_shortcode('fanuc_sim', 'fanuc_sim_shortcode');

// ─────────────────────────────────────────────
// 2. PAGINE AUTOMATICHE
// ─────────────────────────────────────────────
function fanuc_sim_create_pages() {
    if (!get_page_by_path('simulatore-robotica')) {
        wp_insert_post(array(
            'post_title'   => 'Simulatore Robotica - FANUC ER-4iA',
            'post_name'    => 'simulatore-robotica',
            'post_content' => '<!-- wp:shortcode -->[fanuc_sim height="85vh"]<!-- /wp:shortcode -->',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_author'  => 1,
        ));
    }
    if (!get_page_by_path('esercizi-robotica')) {
        wp_insert_post(array(
            'post_title'   => 'Esercizi Robotica - Laboratorio FANUC',
            'post_name'    => 'esercizi-robotica',
            'post_content' => fanuc_sim_exercises_page_content(),
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_author'  => 1,
        ));
    }
}
register_activation_hook(__FILE__, 'fanuc_sim_create_pages');

// ─────────────────────────────────────────────
// 3. ADMIN MENU
// ─────────────────────────────────────────────
function fanuc_sim_admin_menu() {
    add_menu_page('FANUC Simulator', 'FANUC Sim', 'manage_options', 'fanuc-sim', 'fanuc_sim_admin_page', 'dashicons-hammer', 30);
}
add_action('admin_menu', 'fanuc_sim_admin_menu');

function fanuc_sim_admin_page() {
    $sim_file = FANUC_SIM_PATH . 'assets/simulator.html';
    $file_ok  = file_exists($sim_file);
    ?>
    <div class="wrap">
        <h1>FANUC ER-4iA - Simulatore Didattico</h1>

        <?php if (!$file_ok): ?>
        <div class="notice notice-error">
            <p><strong>File simulatore mancante!</strong>
            Rinomina <code>FANUC_ER4iA_Simulator_v3.html</code> in <code>simulator.html</code> e copialo in:<br>
            <code><?php echo esc_html(FANUC_SIM_PATH); ?>assets/</code></p>
        </div>
        <?php else: ?>
        <div class="notice notice-success"><p>File simulatore trovato e pronto.</p></div>
        <?php endif; ?>

        <div class="card" style="max-width:800px;padding:20px;">
            <h2>Shortcode</h2>
            <code style="display:block;padding:10px;background:#f0f0f0;border-radius:4px;font-size:14px;">[fanuc_sim height="700px" width="100%"]</code>

            <h3 style="margin-top:20px;">Parametri:</h3>
            <table class="widefat" style="max-width:500px;">
                <thead><tr><th>Parametro</th><th>Default</th><th>Descrizione</th></tr></thead>
                <tbody>
                    <tr><td><code>height</code></td><td>700px</td><td>Altezza (CSS)</td></tr>
                    <tr><td><code>width</code></td><td>100%</td><td>Larghezza (CSS)</td></tr>
                    <tr><td><code>class</code></td><td></td><td>Classe CSS extra</td></tr>
                </tbody>
            </table>

            <h3 style="margin-top:20px;">Pagine:</h3>
            <ul>
                <li><a href="<?php echo home_url('/simulatore-robotica/'); ?>" target="_blank">/simulatore-robotica/</a></li>
                <li><a href="<?php echo home_url('/esercizi-robotica/'); ?>" target="_blank">/esercizi-robotica/</a></li>
            </ul>

            <h3 style="margin-top:20px;">Setup:</h3>
            <ol>
                <li>Attiva il plugin</li>
                <li>Rinomina il file HTML del simulatore v3 in <code>simulator.html</code></li>
                <li>Caricalo via FTP/File Manager in <code>wp-content/plugins/fanuc-simulator/assets/</code></li>
                <li>Fatto! Le pagine vengono create automaticamente</li>
            </ol>

            <pre style="background:#f5f5f5;padding:10px;border-radius:4px;font-size:12px;">fanuc-simulator/
├── fanuc-simulator.php
├── assets/
│   └── simulator.html   ← Il file HTML v3 rinominato
└── readme.txt</pre>
        </div>
    </div>
    <?php
}

// ─────────────────────────────────────────────
// 4. FULL-WIDTH per pagina simulatore
// ─────────────────────────────────────────────
function fanuc_sim_body_class($classes) {
    if (is_page('simulatore-robotica')) $classes[] = 'fanuc-sim-fullwidth';
    return $classes;
}
add_filter('body_class', 'fanuc_sim_body_class');

function fanuc_sim_fullwidth_css() {
    if (is_page('simulatore-robotica')) {
        echo '<style>
            .fanuc-sim-fullwidth .site-content,
            .fanuc-sim-fullwidth .entry-content,
            .fanuc-sim-fullwidth .wp-block-post-content,
            .fanuc-sim-fullwidth .page-content,
            .fanuc-sim-fullwidth article .entry-content {
                max-width:100%!important;padding:0!important;margin:0!important;width:100%!important;
            }
        </style>';
    }
}
add_action('wp_head', 'fanuc_sim_fullwidth_css');

// ─────────────────────────────────────────────
// 5. CONTENUTO PAGINA ESERCIZI
// ─────────────────────────────────────────────
function fanuc_sim_exercises_page_content() {
    return '
<!-- wp:heading {"level":1} -->
<h1>Esercizi di Robotica - FANUC ER-4iA</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Completa gli esercizi in ordine progressivo sul <a href="/simulatore-robotica/">simulatore online</a> e poi sul braccio reale.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Classe III - Fondamenti</h2>
<!-- /wp:heading -->
<!-- wp:shortcode -->[fanuc_sim height="500px"]<!-- /wp:shortcode -->
<!-- wp:heading {"level":3} --><h3>Es. 1 - Scoperta degli assi</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Muovi un asse alla volta. Compila la tabella: quale parte del robot muove ogni asse?</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Es. 2 - Posizione Home</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Raggiungi J2=-30, J3=-60, J5=-90. Usa il tab Sfide per verificare.</p><!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Classe IV - Programmazione</h2>
<!-- /wp:heading -->
<!-- wp:shortcode -->[fanuc_sim height="500px"]<!-- /wp:shortcode -->
<!-- wp:heading {"level":3} --><h3>Es. 5 - Primo programma TP</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Registra 3 punti con TEACH, scrivi un programma J/L che li percorra.</p><!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Classe V - Cinematica Inversa</h2>
<!-- /wp:heading -->
<!-- wp:shortcode -->[fanuc_sim height="500px"]<!-- /wp:shortcode -->
<!-- wp:heading {"level":3} --><h3>Es. 8 - IK Challenge</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Inserisci X=300, Y=0, Z=200, W=180 e premi MUOVI IK.</p><!-- /wp:paragraph -->
';
}

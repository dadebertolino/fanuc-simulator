<?php
/**
 * Plugin Name: FANUC ER-4iA Robot Simulator
 * Plugin URI: https://www.davidebertolino.it
 * Description: Simulatore web interattivo del braccio robotico FANUC ER-4iA per la didattica della robotica industriale. Usa lo shortcode [fanuc_sim] per incorporare il simulatore in qualsiasi pagina.
 * Version: 1.2.0
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author: Davide "the Prof." Bertolino
 * Author URI: https://www.davidebertolino.it
 * License: GPL v2 or later
 * Text Domain: fanuc-sim
 */

if (!defined('ABSPATH')) exit;

define('FANUC_SIM_VERSION',  '1.2.0');
define('FANUC_SIM_FILE',     __FILE__);
define('FANUC_SIM_DIR',      plugin_dir_path(__FILE__));
define('FANUC_SIM_URL',      plugin_dir_url(__FILE__));
define('FANUC_SIM_BASENAME', plugin_basename(__FILE__));

// ─────────────────────────────────────────────
// 0. AGGIORNAMENTI DA GITHUB RELEASES
// ─────────────────────────────────────────────
if (file_exists(FANUC_SIM_DIR . 'inc/class-updater.php')) {
    require_once FANUC_SIM_DIR . 'inc/class-updater.php';
}
if (class_exists('DB_GitHub_Updater')) {
    new DB_GitHub_Updater(FANUC_SIM_FILE, 'dadebertolino', 'fanuc-simulator');
}

// ─────────────────────────────────────────────
// 1. SHORTCODE [fanuc_sim]
// ─────────────────────────────────────────────
function fanuc_sim_shortcode($atts) {
    $atts = shortcode_atts(array(
        'height' => '700px',
        'width'  => '100%',
        'class'  => '',
    ), $atts, 'fanuc_sim');

    // La versione nell'URL invalida la cache del browser a ogni aggiornamento
    $src    = add_query_arg('ver', FANUC_SIM_VERSION, FANUC_SIM_URL . 'assets/simulator.html');
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
                    title="Simulatore interattivo del robot FANUC ER-4iA">
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
    // L'utente 1 puo' non esistere: si usa chi attiva il plugin, con fallback
    // sul primo amministratore disponibile.
    $author = get_current_user_id();
    if (!$author) {
        $admins = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
        $author = $admins ? (int) $admins[0] : 1;
    }

    if (!get_page_by_path('simulatore-robotica')) {
        wp_insert_post(array(
            'post_title'   => 'Simulatore Robotica - FANUC ER-4iA',
            'post_name'    => 'simulatore-robotica',
            'post_content' => '<!-- wp:shortcode -->[fanuc_sim height="85vh"]<!-- /wp:shortcode -->',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_author'  => $author,
        ));
    }
    if (!get_page_by_path('esercizi-robotica')) {
        wp_insert_post(array(
            'post_title'   => 'Esercizi Robotica - Laboratorio FANUC',
            'post_name'    => 'esercizi-robotica',
            'post_content' => fanuc_sim_exercises_page_content(),
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_author'  => $author,
        ));
    }
}
register_activation_hook(__FILE__, 'fanuc_sim_create_pages');

// ─────────────────────────────────────────────
// 3. ADMIN MENU
// ─────────────────────────────────────────────
function fanuc_sim_admin_menu() {
    $hook = add_menu_page(
        'FANUC Simulator',
        'FANUC Sim',
        'manage_options',
        'fanuc-sim',
        'fanuc_sim_admin_page',
        'dashicons-hammer',
        30
    );
    $GLOBALS['fanuc_sim_hook'] = $hook;
}
add_action('admin_menu', 'fanuc_sim_admin_menu');

/**
 * CSS/JS solo sulla pagina del plugin.
 */
function fanuc_sim_admin_assets($hook) {
    if (empty($GLOBALS['fanuc_sim_hook']) || $hook !== $GLOBALS['fanuc_sim_hook']) {
        return;
    }

    wp_enqueue_style('db-admin-ui', FANUC_SIM_URL . 'assets/css/db-admin-ui.css', array(), FANUC_SIM_VERSION);
    wp_enqueue_style('fanuc-sim-admin', FANUC_SIM_URL . 'assets/css/admin.css', array('db-admin-ui', 'dashicons'), FANUC_SIM_VERSION);
    wp_enqueue_script('fanuc-sim-admin', FANUC_SIM_URL . 'assets/js/admin.js', array(), FANUC_SIM_VERSION, true);

    wp_localize_script(
        'fanuc-sim-admin',
        'fanucSimL10n',
        array(
            'copied'    => __('Shortcode copiato negli appunti.', 'fanuc-sim'),
            'copyError' => __('Copia non riuscita: seleziona il testo e usa Ctrl+C.', 'fanuc-sim'),
            'copy'      => __('Copia', 'fanuc-sim'),
        )
    );
}
add_action('admin_enqueue_scripts', 'fanuc_sim_admin_assets');

function fanuc_sim_admin_page() {
    $sim_file = FANUC_SIM_DIR . 'assets/simulator.html';
    $file_ok  = file_exists($sim_file);
    $three_ok = file_exists(FANUC_SIM_DIR . 'assets/vendor/three.min.js');
    $fonts_ok = file_exists(FANUC_SIM_DIR . 'assets/fonts/JetBrainsMono.woff2')
             && file_exists(FANUC_SIM_DIR . 'assets/fonts/IBMPlexSans.woff2');
    $ready    = $file_ok && $three_ok;
    ?>
    <div class="wrap fanuc-sim-wrap">

        <div class="db-ui-page-header">
            <h1><?php esc_html_e('FANUC ER-4iA — Simulatore didattico', 'fanuc-sim'); ?></h1>
            <div class="db-ui-actions">
                <span class="db-ui-badge db-ui-badge-muted">v<?php echo esc_html(FANUC_SIM_VERSION); ?></span>
                <a class="db-ui-btn db-ui-btn-primary" target="_blank" rel="noopener"
                   href="<?php echo esc_url(home_url('/simulatore-robotica/')); ?>">
                    <span class="dashicons dashicons-external" aria-hidden="true"></span>
                    <?php esc_html_e('Apri il simulatore', 'fanuc-sim'); ?>
                </a>
            </div>
        </div>

        <?php if (!$file_ok) : ?>
            <div class="db-ui-alert db-ui-alert-danger">
                <span class="db-ui-alert-icon dashicons dashicons-warning" aria-hidden="true"></span>
                <strong><?php esc_html_e('File del simulatore mancante.', 'fanuc-sim'); ?></strong>
                <?php
                printf(
                    /* translators: %s: percorso della cartella assets del plugin */
                    esc_html__('Il file assets/simulator.html non e stato trovato in %s. Reinstalla il plugin: il file fa parte del pacchetto e non va copiato a mano.', 'fanuc-sim'),
                    '<code>' . esc_html(FANUC_SIM_DIR) . 'assets/</code>'
                );
                ?>
            </div>
        <?php elseif (!$three_ok) : ?>
            <div class="db-ui-alert db-ui-alert-danger">
                <span class="db-ui-alert-icon dashicons dashicons-warning" aria-hidden="true"></span>
                <strong><?php esc_html_e('Libreria 3D mancante.', 'fanuc-sim'); ?></strong>
                <?php esc_html_e('Il file assets/vendor/three.min.js non e stato trovato: il simulatore non puo funzionare. Reinstalla il plugin.', 'fanuc-sim'); ?>
            </div>
        <?php else : ?>
            <div class="db-ui-alert db-ui-alert-success">
                <span class="db-ui-alert-icon dashicons dashicons-yes-alt" aria-hidden="true"></span>
                <?php esc_html_e('Simulatore pronto. Nessuna risorsa viene caricata da servizi esterni.', 'fanuc-sim'); ?>
            </div>
        <?php endif; ?>

        <div class="fanuc-sim-stats">
            <div class="db-ui-stat">
                <span class="db-ui-stat-icon <?php echo $file_ok ? 'db-ui-stat-icon-success' : 'db-ui-stat-icon-danger'; ?> dashicons <?php echo $file_ok ? 'dashicons-yes' : 'dashicons-no'; ?>" aria-hidden="true"></span>
                <span class="db-ui-stat-value"><?php echo $file_ok ? esc_html__('OK', 'fanuc-sim') : esc_html__('Assente', 'fanuc-sim'); ?></span>
                <span class="db-ui-stat-label"><?php esc_html_e('Simulatore', 'fanuc-sim'); ?></span>
            </div>
            <div class="db-ui-stat">
                <span class="db-ui-stat-icon <?php echo $three_ok ? 'db-ui-stat-icon-success' : 'db-ui-stat-icon-danger'; ?> dashicons <?php echo $three_ok ? 'dashicons-yes' : 'dashicons-no'; ?>" aria-hidden="true"></span>
                <span class="db-ui-stat-value"><?php echo $three_ok ? esc_html__('OK', 'fanuc-sim') : esc_html__('Assente', 'fanuc-sim'); ?></span>
                <span class="db-ui-stat-label"><?php esc_html_e('Libreria 3D (locale)', 'fanuc-sim'); ?></span>
            </div>
            <div class="db-ui-stat">
                <span class="db-ui-stat-icon <?php echo $fonts_ok ? 'db-ui-stat-icon-success' : 'db-ui-stat-icon-warning'; ?> dashicons dashicons-editor-textcolor" aria-hidden="true"></span>
                <span class="db-ui-stat-value"><?php echo $fonts_ok ? esc_html__('OK', 'fanuc-sim') : esc_html__('Fallback', 'fanuc-sim'); ?></span>
                <span class="db-ui-stat-label"><?php esc_html_e('Caratteri (locali)', 'fanuc-sim'); ?></span>
            </div>
            <div class="db-ui-stat">
                <span class="db-ui-stat-icon db-ui-stat-icon-primary dashicons dashicons-privacy" aria-hidden="true"></span>
                <span class="db-ui-stat-value"><?php esc_html_e('Nessuna', 'fanuc-sim'); ?></span>
                <span class="db-ui-stat-label"><?php esc_html_e('Richieste esterne', 'fanuc-sim'); ?></span>
            </div>
        </div>

        <div class="db-ui-card">
            <div class="db-ui-card-header"><?php esc_html_e('Shortcode', 'fanuc-sim'); ?></div>

            <div class="fanuc-sim-copy">
                <input type="text" class="db-ui-input" id="fanuc-sim-shortcode" readonly
                       value='[fanuc_sim height="700px" width="100%"]'
                       aria-label="<?php esc_attr_e('Shortcode da incorporare', 'fanuc-sim'); ?>">
                <button type="button" class="db-ui-btn db-ui-btn-sm" data-fanuc-copy="fanuc-sim-shortcode">
                    <span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
                    <?php esc_html_e('Copia', 'fanuc-sim'); ?>
                </button>
            </div>
            <p class="fanuc-sim-feedback" id="fanuc-sim-copy-msg" role="status" aria-live="polite"></p>

            <div class="db-ui-sep"></div>

            <table class="db-ui-table">
                <thead>
                    <tr>
                        <th scope="col"><?php esc_html_e('Parametro', 'fanuc-sim'); ?></th>
                        <th scope="col"><?php esc_html_e('Default', 'fanuc-sim'); ?></th>
                        <th scope="col"><?php esc_html_e('Descrizione', 'fanuc-sim'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>height</code></td><td><code>700px</code></td>
                        <td><?php esc_html_e('Altezza del riquadro, valore CSS. Nella pagina generata e 85vh.', 'fanuc-sim'); ?></td>
                    </tr>
                    <tr>
                        <td><code>width</code></td><td><code>100%</code></td>
                        <td><?php esc_html_e('Larghezza del riquadro, valore CSS.', 'fanuc-sim'); ?></td>
                    </tr>
                    <tr>
                        <td><code>class</code></td><td><em><?php esc_html_e('vuoto', 'fanuc-sim'); ?></em></td>
                        <td><?php esc_html_e('Classe CSS aggiuntiva sul contenitore.', 'fanuc-sim'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="db-ui-card">
            <div class="db-ui-card-header"><?php esc_html_e('Pagine create automaticamente', 'fanuc-sim'); ?></div>
            <ul class="fanuc-sim-links">
                <li>
                    <span class="dashicons dashicons-admin-page" aria-hidden="true"></span>
                    <a href="<?php echo esc_url(home_url('/simulatore-robotica/')); ?>" target="_blank" rel="noopener">/simulatore-robotica/</a>
                </li>
                <li>
                    <span class="dashicons dashicons-admin-page" aria-hidden="true"></span>
                    <a href="<?php echo esc_url(home_url('/esercizi-robotica/')); ?>" target="_blank" rel="noopener">/esercizi-robotica/</a>
                </li>
            </ul>
            <p class="fanuc-sim-note">
                <?php esc_html_e('Disinstallando il plugin queste pagine non vengono rimosse, perche possono contenere esercizi modificati dal docente. Vanno eliminate a mano dal menu Pagine.', 'fanuc-sim'); ?>
            </p>
        </div>

        <div class="db-ui-card">
            <div class="db-ui-card-header"><?php esc_html_e('Struttura del plugin', 'fanuc-sim'); ?></div>
            <pre class="fanuc-sim-tree">fanuc-simulator/
├── fanuc-simulator.php
├── readme.txt
├── inc/
│   └── class-updater.php      <?php esc_html_e('aggiornamenti da GitHub Releases', 'fanuc-sim'); ?>

└── assets/
    ├── simulator.html
    ├── css/  db-admin-ui.css, admin.css
    ├── js/   admin.js
    ├── fonts/   JetBrains Mono, IBM Plex Sans (SIL OFL)
    └── vendor/  three.min.js  (three.js r128, MIT)</pre>
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

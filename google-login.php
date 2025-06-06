<?php
/**
 * Plugin Name: Google Login Plugin
 * Plugin URI: https://seusite.com/google-login
 * Description: Permite login no WordPress usando contas do Google
 * Version: 0.1
 * Author: Fabrício Silva
 * Author URI: https://seusite.com
 * Text Domain: google-login
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// Prevenir acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes
define('GOOGLE_LOGIN_VERSION', '0.1');
define('GOOGLE_LOGIN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GOOGLE_LOGIN_PLUGIN_URL', plugin_dir_url(__FILE__));

// Classe principal do plugin
class Google_Login_Plugin {
    private static $instance = null;
    private $client_id;
    private $client_secret;
    private $redirect_uri;

    // Singleton
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init();
    }

    private function init() {
        // Carregar configurações
        $this->load_settings();

        // Hooks de ativação/desativação
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Hooks de admin
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));

        // Hooks de login
        add_action('login_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('login_form', array($this, 'add_login_button'));
        add_action('init', array($this, 'handle_google_callback'));

        // Hooks de AJAX
        add_action('wp_ajax_google_login', array($this, 'handle_ajax_login'));
        add_action('wp_ajax_nopriv_google_login', array($this, 'handle_ajax_login'));
    }

    public function activate() {
        // Criar tabelas ou opções necessárias
        add_option('google_login_client_id', '');
        add_option('google_login_client_secret', '');
    }

    public function deactivate() {
        // Limpar dados se necessário
    }

    private function load_settings() {
        $this->client_id = get_option('google_login_client_id');
        $this->client_secret = get_option('google_login_client_secret');
        $this->redirect_uri = admin_url('admin-ajax.php?action=google_login');
    }

    public function add_admin_menu() {
        add_options_page(
            'Google Login Settings',
            'Google Login',
            'manage_options',
            'google-login-settings',
            array($this, 'render_settings_page')
        );
    }

    public function register_settings() {
        register_setting('google_login_settings', 'google_login_client_id');
        register_setting('google_login_settings', 'google_login_client_secret');
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('google_login_settings');
                do_settings_sections('google_login_settings');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="google_login_client_id">Client ID</label>
                        </th>
                        <td>
                            <input type="text" id="google_login_client_id" 
                                   name="google_login_client_id" 
                                   value="<?php echo esc_attr(get_option('google_login_client_id')); ?>" 
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="google_login_client_secret">Client Secret</label>
                        </th>
                        <td>
                            <input type="password" id="google_login_client_secret" 
                                   name="google_login_client_secret" 
                                   value="<?php echo esc_attr(get_option('google_login_client_secret')); ?>" 
                                   class="regular-text">
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function enqueue_scripts() {
        wp_enqueue_style(
            'google-login-style',
            GOOGLE_LOGIN_PLUGIN_URL . 'assets/css/style.css',
            array(),
            GOOGLE_LOGIN_VERSION
        );

        wp_enqueue_script(
            'google-login-script',
            GOOGLE_LOGIN_PLUGIN_URL . 'assets/js/script.js',
            array('jquery'),
            GOOGLE_LOGIN_VERSION,
            true
        );

        wp_localize_script('google-login-script', 'googleLogin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('google_login_nonce')
        ));
    }

    public function add_login_button() {
        ?>
        <div class="google-login-container">
            <a href="<?php echo esc_url($this->get_google_auth_url()); ?>" 
               class="google-login-button">
                <img src="<?php echo esc_url(GOOGLE_LOGIN_PLUGIN_URL . 'assets/images/google-icon.png'); ?>" 
                     alt="Google Icon">
                <?php esc_html_e('Login com Google', 'google-login'); ?>
            </a>
        </div>
        <?php
    }

    private function get_google_auth_url() {
        $params = array(
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'response_type' => 'code',
            'scope' => 'email profile',
            'state' => wp_create_nonce('google_login_nonce')
        );

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    public function handle_google_callback() {
        if (!isset($_GET['action']) || $_GET['action'] !== 'google_login') {
            return;
        }

        if (!isset($_GET['code'])) {
            wp_die('Código de autorização não fornecido');
        }

        if (!isset($_GET['state']) || !wp_verify_nonce($_GET['state'], 'google_login_nonce')) {
            wp_die('Nonce inválido');
        }

        $code = sanitize_text_field($_GET['code']);
        $token_data = $this->get_google_token($code);

        if (is_wp_error($token_data)) {
            wp_die($token_data->get_error_message());
        }

        $user_data = $this->get_google_user_data($token_data['access_token']);

        if (is_wp_error($user_data)) {
            wp_die($user_data->get_error_message());
        }

        $user = $this->get_or_create_user($user_data);

        if (is_wp_error($user)) {
            wp_die($user->get_error_message());
        }

        wp_set_auth_cookie($user->ID);
        wp_redirect(home_url());
        exit;
    }

    private function get_google_token($code) {
        $response = wp_remote_post('https://oauth2.googleapis.com/token', array(
            'body' => array(
                'code' => $code,
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri' => $this->redirect_uri,
                'grant_type' => 'authorization_code'
            )
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            return new WP_Error('google_auth_error', $body['error_description']);
        }

        return $body;
    }

    private function get_google_user_data($access_token) {
        $response = wp_remote_get('https://www.googleapis.com/oauth2/v2/userinfo', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token
            )
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            return new WP_Error('google_user_error', $body['error']['message']);
        }

        return $body;
    }

    private function get_or_create_user($user_data) {
        $user = get_user_by('email', $user_data['email']);

        if ($user) {
            return $user;
        }

        $username = sanitize_user(current(explode('@', $user_data['email'])));
        $counter = 1;
        $base_username = $username;

        while (username_exists($username)) {
            $username = $base_username . $counter;
            $counter++;
        }

        $user_id = wp_create_user(
            $username,
            wp_generate_password(),
            $user_data['email']
        );

        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $user = get_user_by('id', $user_id);

        wp_update_user(array(
            'ID' => $user_id,
            'first_name' => $user_data['given_name'],
            'last_name' => $user_data['family_name'],
            'display_name' => $user_data['name']
        ));

        update_user_meta($user_id, 'google_id', $user_data['id']);

        return $user;
    }
}

// Inicializar o plugin
function google_login_init() {
    return Google_Login_Plugin::get_instance();
}

add_action('plugins_loaded', 'google_login_init'); 
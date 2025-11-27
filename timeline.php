<?php
/**
 * Plugin Name: Timeline
 * Description: Sistema de gestión de proyectos con login personalizado
 * Version: 1.0
 * Author: Tu Nombre
 */

if (!defined('ABSPATH')) exit;

class Timeline_Plugin {
    
    private $table_users;
    
    public function __construct() {
        global $wpdb;
        $this->table_users = $wpdb->prefix . 'timeline_users';
        
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('init', array($this, 'init'));
        add_action('template_redirect', array($this, 'handle_custom_pages'));
        add_action('admin_post_nopriv_timeline_login', array($this, 'handle_login'));
        add_action('admin_post_timeline_login', array($this, 'handle_login'));
        add_action('admin_post_timeline_logout', array($this, 'handle_logout'));
        add_action('admin_post_timeline_create_user', array($this, 'handle_create_user'));
        add_action('admin_post_timeline_change_password', array($this, 'handle_change_password'));
        
        // Reescribir URLs
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('init', array($this, 'add_rewrite_rules'));
    }
    
    public function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_users} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            username varchar(100) NOT NULL,
            password varchar(255) NOT NULL,
            email varchar(100) NOT NULL,
            role varchar(50) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY username (username)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Crear super admin si no existe
        $super_admin = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_users} WHERE username = %s",
            'administrador'
        ));
        
        if (!$super_admin) {
            $wpdb->insert(
                $this->table_users,
                array(
                    'username' => 'administrador',
                    'password' => password_hash('adminproyectos', PASSWORD_DEFAULT),
                    'email' => get_option('admin_email'),
                    'role' => 'super_admin'
                )
            );
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function add_query_vars($vars) {
        $vars[] = 'timeline_page';
        return $vars;
    }
    
    public function add_rewrite_rules() {
        add_rewrite_rule('^login-proyectos/?$', 'index.php?timeline_page=login', 'top');
        add_rewrite_rule('^timeline-dashboard/?$', 'index.php?timeline_page=dashboard', 'top');
        add_rewrite_rule('^timeline-usuarios/?$', 'index.php?timeline_page=users', 'top');
        add_rewrite_rule('^timeline-perfil/?$', 'index.php?timeline_page=profile', 'top');
    }
    
    public function init() {
        if (!session_id()) {
            session_start();
        }
    }
    
    public function is_logged_in() {
        return isset($_SESSION['timeline_user_id']);
    }
    
    public function get_current_user() {
        if (!$this->is_logged_in()) {
            return null;
        }
        
        global $wpdb;
        $user_id = $_SESSION['timeline_user_id'];
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_users} WHERE id = %d",
            $user_id
        ));
    }
    
    public function can_manage_users($user) {
        if (!$user) return false;
        return in_array($user->role, array('super_admin', 'administrador'));
    }
    
    public function handle_custom_pages() {
        $page = get_query_var('timeline_page');
        
        if (!$page) return;
        
        switch ($page) {
            case 'login':
                if ($this->is_logged_in()) {
                    wp_redirect(home_url('/timeline-dashboard'));
                    exit;
                }
                $this->load_template('login');
                break;
                
            case 'dashboard':
                if (!$this->is_logged_in()) {
                    wp_redirect(home_url('/login-proyectos'));
                    exit;
                }
                $this->load_template('dashboard');
                break;
                
            case 'users':
                if (!$this->is_logged_in()) {
                    wp_redirect(home_url('/login-proyectos'));
                    exit;
                }
                $current_user = $this->get_current_user();
                
                // Verificar permisos para acceder a usuarios
                if (!$this->can_manage_users($current_user)) {
                    wp_redirect(home_url('/timeline-dashboard'));
                    exit;
                }
                $this->load_template('users');
                break;
                
            case 'profile':
                if (!$this->is_logged_in()) {
                    wp_redirect(home_url('/login-proyectos'));
                    exit;
                }
                $this->load_template('profile');
                break;
        }
    }
    
    private function load_template($template) {
        $template_file = plugin_dir_path(__FILE__) . 'templates/' . $template . '.php';
        
        if (file_exists($template_file)) {
            // Variables disponibles para las plantillas
            $current_user = $this->get_current_user();
            
            if ($template === 'users') {
                global $wpdb;
                $users = $wpdb->get_results("SELECT * FROM {$this->table_users} ORDER BY created_at DESC");
            }
            
            include $template_file;
            exit;
        }
    }
    
    public function handle_login() {
        if (!isset($_POST['timeline_login_nonce']) || 
            !wp_verify_nonce($_POST['timeline_login_nonce'], 'timeline_login')) {
            wp_redirect(home_url('/login-proyectos?error=nonce'));
            exit;
        }
        
        global $wpdb;
        $username = sanitize_text_field($_POST['username']);
        $password = $_POST['password'];
        
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_users} WHERE username = %s",
            $username
        ));
        
        if ($user && password_verify($password, $user->password)) {
            $_SESSION['timeline_user_id'] = $user->id;
            $_SESSION['timeline_user_role'] = $user->role;
            $_SESSION['timeline_username'] = $user->username;
            
            wp_redirect(home_url('/timeline-dashboard'));
            exit;
        }
        
        wp_redirect(home_url('/login-proyectos?error=invalid'));
        exit;
    }
    
    public function handle_logout() {
        session_destroy();
        wp_redirect(home_url('/login-proyectos?logout=success'));
        exit;
    }
    
    public function handle_create_user() {
        if (!$this->is_logged_in()) {
            wp_redirect(home_url('/login-proyectos'));
            exit;
        }
        
        $current_user = $this->get_current_user();
        
        // Verificar que el usuario puede gestionar usuarios
        if (!$this->can_manage_users($current_user)) {
            wp_die('No tienes permisos para realizar esta acción.');
        }
        
        if (!isset($_POST['timeline_create_user_nonce']) || 
            !wp_verify_nonce($_POST['timeline_create_user_nonce'], 'timeline_create_user')) {
            wp_die('Error de seguridad');
        }
        
        global $wpdb;
        
        $username = sanitize_text_field($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $role = sanitize_text_field($_POST['role']);
        
        // Validar que los administradores solo puedan crear clientes
        if ($current_user->role === 'administrador' && $role !== 'cliente') {
            wp_redirect(home_url('/timeline-usuarios?error=permission'));
            exit;
        }
        
        // Solo el Super Admin puede crear administradores
        if ($role === 'administrador' && $current_user->role !== 'super_admin') {
            wp_redirect(home_url('/timeline-usuarios?error=permission'));
            exit;
        }
        
        // Generar contraseña aleatoria
        $password = wp_generate_password(12, false);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $result = $wpdb->insert(
            $this->table_users,
            array(
                'username' => $username,
                'password' => $password_hash,
                'email' => $email,
                'role' => $role
            )
        );
        
        if ($result) {
            // Enviar email
            $this->send_welcome_email($username, $email, $password);
            
            wp_redirect(home_url('/timeline-usuarios?success=created'));
        } else {
            wp_redirect(home_url('/timeline-usuarios?error=failed'));
        }
        exit;
    }
    
    public function handle_change_password() {
        if (!$this->is_logged_in()) {
            wp_redirect(home_url('/login-proyectos'));
            exit;
        }
        
        if (!isset($_POST['timeline_change_password_nonce']) || 
            !wp_verify_nonce($_POST['timeline_change_password_nonce'], 'timeline_change_password')) {
            wp_die('Error de seguridad');
        }
        
        global $wpdb;
        $current_user = $this->get_current_user();
        
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verificar contraseña actual
        if (!password_verify($current_password, $current_user->password)) {
            wp_redirect(home_url('/timeline-perfil?error=current_password'));
            exit;
        }
        
        // Verificar que las contraseñas coincidan
        if ($new_password !== $confirm_password) {
            wp_redirect(home_url('/timeline-perfil?error=match'));
            exit;
        }
        
        // Verificar longitud mínima
        if (strlen($new_password) < 8) {
            wp_redirect(home_url('/timeline-perfil?error=length'));
            exit;
        }
        
        // Actualizar contraseña
        $result = $wpdb->update(
            $this->table_users,
            array('password' => password_hash($new_password, PASSWORD_DEFAULT)),
            array('id' => $current_user->id)
        );
        
        if ($result !== false) {
            wp_redirect(home_url('/timeline-perfil?success=password_changed'));
        } else {
            wp_redirect(home_url('/timeline-perfil?error=failed'));
        }
        exit;
    }
    
    private function send_welcome_email($username, $email, $password) {
        $login_url = home_url('/login-proyectos');
        
        $subject = 'Bienvenido a tu área de proyectos';
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50;'>Bienvenido a tu área de proyectos</h2>
                <p>Hola <strong>{$username}</strong>,</p>
                <p>Se ha creado tu cuenta para acceder al seguimiento de tus proyectos.</p>
                
                <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <p style='margin: 5px 0;'><strong>Tus credenciales de acceso son:</strong></p>
                    <p style='margin: 5px 0;'><strong>Usuario:</strong> {$username}</p>
                    <p style='margin: 5px 0;'><strong>Contraseña:</strong> {$password}</p>
                </div>
                
                <p>Puedes iniciar sesión en: <a href='{$login_url}' style='color: #3498db;'>{$login_url}</a></p>
                
                <p style='color: #e74c3c; font-size: 14px;'><em>Te recomendamos cambiar tu contraseña después del primer inicio de sesión.</em></p>
                
                <p style='margin-top: 30px;'>Saludos,<br><strong>BeBuilt</strong></p>
            </div>
        </body>
        </html>
        ";
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($email, $subject, $message, $headers);
    }
}

new Timeline_Plugin();

// Activar las reglas de reescritura al activar el plugin
register_activation_hook(__FILE__, function() {
    flush_rewrite_rules();
});
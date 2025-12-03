<?php

/**
 * Plugin Name: Timeline - Sistema de Gestión de Proyectos
 * Description: Sistema completo de gestión de proyectos con línea de tiempo, hitos y auditoría
 * Version: 2.1
 * Author: BeBuilt
 */

if (!defined('ABSPATH')) exit;

// Definir constantes
define('TIMELINE_VERSION', '2.1');
define('TIMELINE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TIMELINE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Verificar y cargar clases
$required_files = array(
    'includes/database.php',
    'includes/class-projects.php',
    'includes/class-milestones.php',
    'includes/class-documents.php',
    'includes/class-audit-log.php',  // NUEVO: Sistema de auditoría
    'includes/handlers.php'
);

foreach ($required_files as $file) {
    $file_path = TIMELINE_PLUGIN_DIR . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    } else {
        add_action('admin_notices', function () use ($file) {
            echo '<div class="notice notice-error"><p><strong>Timeline Plugin Error:</strong> Falta el archivo: ' . esc_html($file) . '</p></div>';
        });
    }
}

class Timeline_Plugin
{
    private $table_users;
    private $db;
    private $projects;
    private $milestones;
    private $audit_log;  // NUEVO

    public function __construct()
    {
        global $wpdb;
        $this->table_users = $wpdb->prefix . 'timeline_users';

        // Inicializar clases
        if (class_exists('Timeline_Database')) {
            $this->db = Timeline_Database::get_instance();
        }
        if (class_exists('Timeline_Projects')) {
            $this->projects = Timeline_Projects::get_instance();
        }
        if (class_exists('Timeline_Milestones')) {
            $this->milestones = Timeline_Milestones::get_instance();
        }
        if (class_exists('Timeline_Audit_Log')) {
            $this->audit_log = Timeline_Audit_Log::get_instance();
        }

        // Hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('init', array($this, 'init'));
        add_action('template_redirect', array($this, 'handle_custom_pages'));

        // Login/logout
        add_action('admin_post_nopriv_timeline_login', array($this, 'handle_login'));
        add_action('admin_post_timeline_login', array($this, 'handle_login'));
        add_action('admin_post_timeline_logout', array($this, 'handle_logout'));

        // Usuarios
        add_action('admin_post_timeline_create_user', array($this, 'handle_create_user'));
        add_action('admin_post_timeline_change_password', array($this, 'handle_change_password'));

        // Proyectos
        add_action('admin_post_timeline_create_project', array($this, 'handle_create_project'));
        add_action('admin_post_timeline_update_project', array($this, 'handle_update_project'));

        // URLs
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('init', array($this, 'add_rewrite_rules'));
    }

    public function activate()
    {
        global $wpdb;

        // Crear todas las tablas
        if ($this->db) {
            $this->db->create_tables();
        }

        // Crear tabla de usuarios
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

        flush_rewrite_rules();
    }

    public function add_query_vars($vars)
    {
        $vars[] = 'timeline_page';
        $vars[] = 'timeline_id';
        return $vars;
    }

    public function add_rewrite_rules()
    {
        add_rewrite_rule('^login-proyectos/?$', 'index.php?timeline_page=login', 'top');
        add_rewrite_rule('^timeline-logout/?$', 'index.php?timeline_page=logout', 'top'); // NUEVA LÍNEA
        add_rewrite_rule('^timeline-dashboard/?$', 'index.php?timeline_page=dashboard', 'top');
        add_rewrite_rule('^timeline-usuarios/?$', 'index.php?timeline_page=users', 'top');
        add_rewrite_rule('^timeline-perfil/?$', 'index.php?timeline_page=profile', 'top');
        add_rewrite_rule('^timeline-proyectos/?$', 'index.php?timeline_page=projects', 'top');
        add_rewrite_rule('^timeline-mis-proyectos/?$', 'index.php?timeline_page=my_projects', 'top');
        add_rewrite_rule('^timeline-proyecto-nuevo/?$', 'index.php?timeline_page=project_new', 'top');
        add_rewrite_rule('^timeline-proyecto-editar/([0-9]+)/?$', 'index.php?timeline_page=project_edit&timeline_id=$matches[1]', 'top');
        add_rewrite_rule('^timeline-proyecto/([0-9]+)/?$', 'index.php?timeline_page=project_view&timeline_id=$matches[1]', 'top');
        add_rewrite_rule('^timeline-proyecto-admin/([0-9]+)/?$', 'index.php?timeline_page=project_admin&timeline_id=$matches[1]', 'top');
        add_rewrite_rule('^timeline-documentos/([0-9]+)/?$', 'index.php?timeline_page=project_documents&timeline_id=$matches[1]', 'top');
        add_rewrite_rule('^timeline-audit-log/?$', 'index.php?timeline_page=audit_log', 'top');  // NUEVO
    }

    public function init()
    {
        if (!session_id()) {
            session_start();
        }
    }

    public function is_logged_in()
    {
        return isset($_SESSION['timeline_user_id']);
    }

    public function get_current_user()
    {
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

    public function can_manage_users($user)
    {
        if (!$user) return false;
        return in_array($user->role, array('super_admin', 'administrador'));
    }

    public function can_manage_projects($user)
    {
        if (!$user) return false;
        return in_array($user->role, array('super_admin', 'administrador'));
    }

    public function handle_custom_pages()
    {
        $page = get_query_var('timeline_page');
        if (!$page) return;

        switch ($page) {
            case 'login':
                if ($this->is_logged_in()) {
                    $current_user = $this->get_current_user();
                    if ($current_user->role === 'cliente') {
                        wp_redirect(home_url('/timeline-mis-proyectos'));
                    } else {
                        wp_redirect(home_url('/timeline-dashboard'));
                    }
                    exit;
                }
                $this->load_template('login');
                break;

            case 'logout':
                $this->handle_logout();
                break;

            case 'dashboard':
                if (!$this->is_logged_in()) {
                    wp_redirect(home_url('/login-proyectos'));
                    exit;
                }
                $current_user = $this->get_current_user();
                if ($current_user->role === 'cliente') {
                    wp_redirect(home_url('/timeline-mis-proyectos'));
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

            case 'projects':
                if (!$this->is_logged_in()) {
                    wp_redirect(home_url('/login-proyectos'));
                    exit;
                }
                $current_user = $this->get_current_user();
                if (!$this->can_manage_projects($current_user)) {
                    wp_redirect(home_url('/timeline-mis-proyectos'));
                    exit;
                }
                $this->load_template('projects');
                break;

            case 'my_projects':
                if (!$this->is_logged_in()) {
                    wp_redirect(home_url('/login-proyectos'));
                    exit;
                }
                $this->load_template('my-projects');
                break;

            case 'project_new':
                if (!$this->is_logged_in()) {
                    wp_redirect(home_url('/login-proyectos'));
                    exit;
                }
                $current_user = $this->get_current_user();
                if (!$this->can_manage_projects($current_user)) {
                    wp_redirect(home_url('/timeline-dashboard'));
                    exit;
                }
                $this->load_template('project-form');
                break;

            case 'project_edit':
                if (!$this->is_logged_in()) {
                    wp_redirect(home_url('/login-proyectos'));
                    exit;
                }
                $current_user = $this->get_current_user();
                if (!$this->can_manage_projects($current_user)) {
                    wp_redirect(home_url('/timeline-dashboard'));
                    exit;
                }
                $project_id = get_query_var('timeline_id');
                if (!$project_id) {
                    wp_redirect(home_url('/timeline-proyectos'));
                    exit;
                }
                $this->load_template('project-form');
                break;

            case 'project_view':
                if (!$this->is_logged_in()) {
                    wp_redirect(home_url('/login-proyectos'));
                    exit;
                }
                $project_id = get_query_var('timeline_id');
                if (!$project_id) {
                    wp_redirect(home_url('/timeline-dashboard'));
                    exit;
                }

                // REGISTRAR VISUALIZACIÓN DEL PROYECTO
                $current_user = $this->get_current_user();
                if ($this->audit_log) {
                    $this->audit_log->log(
                        $current_user->id,
                        'view',
                        'project',
                        $project_id,
                        'Proyecto visualizado'
                    );
                }

                $this->load_template('project-timeline');
                break;

            case 'project_admin':
                if (!$this->is_logged_in()) {
                    wp_redirect(home_url('/login-proyectos'));
                    exit;
                }
                $current_user = $this->get_current_user();
                if (!$this->can_manage_projects($current_user)) {
                    wp_redirect(home_url('/timeline-dashboard'));
                    exit;
                }
                $project_id = get_query_var('timeline_id');
                if (!$project_id) {
                    wp_redirect(home_url('/timeline-proyectos'));
                    exit;
                }
                $this->load_template_admin('project-timeline-admin');
                break;

            case 'project_documents':
                if (!$this->is_logged_in()) {
                    wp_redirect(home_url('/login-proyectos'));
                    exit;
                }
                $current_user = $this->get_current_user();
                if (!$this->can_manage_projects($current_user)) {
                    wp_redirect(home_url('/timeline-dashboard'));
                    exit;
                }
                $project_id = get_query_var('timeline_id');
                if (!$project_id) {
                    wp_redirect(home_url('/timeline-proyectos'));
                    exit;
                }
                $this->load_template_admin('project-documents');
                break;

            // NUEVO: Página de audit log
            case 'audit_log':
                if (!$this->is_logged_in()) {
                    wp_redirect(home_url('/login-proyectos'));
                    exit;
                }
                $current_user = $this->get_current_user();
                if (!$this->can_manage_users($current_user)) {
                    wp_redirect(home_url('/timeline-dashboard'));
                    exit;
                }
                $this->load_template_admin('audit-log');
                break;
        }
    }

    private function load_template($template)
    {
        $template_file = TIMELINE_PLUGIN_DIR . 'templates/' . $template . '.php';

        if (file_exists($template_file)) {
            $current_user = $this->get_current_user();
            $projects_class = $this->projects;
            $milestones_class = $this->milestones;

            $GLOBALS['timeline_plugin'] = $this;

            switch ($template) {
                case 'users':
                    global $wpdb;
                    $users = $wpdb->get_results("SELECT * FROM {$this->table_users} ORDER BY created_at DESC");
                    break;

                case 'projects':
                    $projects = $this->projects ? $this->projects->get_all_projects() : array();
                    break;

                case 'my-projects':
                    $projects = $this->projects ? $this->projects->get_client_projects($current_user->id) : array();
                    break;

                case 'project-form':
                    $project_id = get_query_var('timeline_id');
                    $project = null;
                    $available_clients = array();
                    $assigned_clients = array();

                    if ($this->projects) {
                        $project = $project_id ? $this->projects->get_project($project_id) : null;
                        $available_clients = $this->projects->get_available_clients();
                        $assigned_clients = $project_id ? $this->projects->get_project_clients($project_id) : array();
                    }
                    break;
            }

            include $template_file;
            exit;
        } else {
            wp_die('Template no encontrado: ' . $template);
        }
    }

    private function load_template_admin($template)
    {
        $template_file = TIMELINE_PLUGIN_DIR . 'admin/' . $template . '.php';

        if (file_exists($template_file)) {
            $current_user = $this->get_current_user();
            $projects_class = $this->projects;
            $milestones_class = $this->milestones;
            $GLOBALS['timeline_plugin'] = $this;

            include $template_file;
            exit;
        } else {
            wp_die('Template de admin no encontrado: ' . $template);
        }
    }

    public function handle_login()
    {
        if (
            !isset($_POST['timeline_login_nonce']) ||
            !wp_verify_nonce($_POST['timeline_login_nonce'], 'timeline_login')
        ) {
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

            // REGISTRAR LOGIN
            if ($this->audit_log) {
                $this->audit_log->log(
                    $user->id,
                    'login',
                    'user',
                    $user->id,
                    'Inicio de sesión exitoso'
                );
            }

            if ($user->role === 'cliente') {
                wp_redirect(home_url('/timeline-mis-proyectos'));
            } else {
                wp_redirect(home_url('/timeline-dashboard'));
            }
            exit;
        }

        wp_redirect(home_url('/login-proyectos?error=invalid'));
        exit;
    }

    public function handle_logout()
    {
        // REGISTRAR LOGOUT ANTES DE DESTRUIR SESIÓN
        if ($this->is_logged_in() && $this->audit_log) {
            $current_user = $this->get_current_user();
            $this->audit_log->log(
                $current_user->id,
                'logout',
                'user',
                $current_user->id,
                'Cierre de sesión'
            );
        }

        session_destroy();
        wp_redirect(home_url('/login-proyectos?logout=success'));
        exit;
    }

    public function handle_create_user()
    {
        if (!$this->is_logged_in()) {
            wp_redirect(home_url('/login-proyectos'));
            exit;
        }

        $current_user = $this->get_current_user();

        if (!$this->can_manage_users($current_user)) {
            wp_die('No tienes permisos.');
        }

        if (
            !isset($_POST['timeline_create_user_nonce']) ||
            !wp_verify_nonce($_POST['timeline_create_user_nonce'], 'timeline_create_user')
        ) {
            wp_die('Error de seguridad');
        }

        global $wpdb;

        $username = sanitize_text_field($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $role = sanitize_text_field($_POST['role']);

        if ($current_user->role === 'administrador' && $role !== 'cliente') {
            wp_redirect(home_url('/timeline-usuarios?error=permission'));
            exit;
        }

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
            $new_user_id = $wpdb->insert_id;

            // REGISTRAR CREACIÓN DE USUARIO
            if ($this->audit_log) {
                $this->audit_log->log(
                    $current_user->id,
                    'create',
                    'user',
                    $new_user_id,
                    "Usuario creado: {$username} ({$role})"
                );
            }

            $this->send_welcome_email($username, $email, $password);
            wp_redirect(home_url('/timeline-usuarios?success=created'));
        } else {
            wp_redirect(home_url('/timeline-usuarios?error=failed'));
        }
        exit;
    }

    public function handle_change_password()
    {
        if (!$this->is_logged_in()) {
            wp_redirect(home_url('/login-proyectos'));
            exit;
        }

        if (
            !isset($_POST['timeline_change_password_nonce']) ||
            !wp_verify_nonce($_POST['timeline_change_password_nonce'], 'timeline_change_password')
        ) {
            wp_die('Error de seguridad');
        }

        global $wpdb;
        $current_user = $this->get_current_user();

        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (!password_verify($current_password, $current_user->password)) {
            wp_redirect(home_url('/timeline-perfil?error=current_password'));
            exit;
        }

        if ($new_password !== $confirm_password) {
            wp_redirect(home_url('/timeline-perfil?error=match'));
            exit;
        }

        if (strlen($new_password) < 8) {
            wp_redirect(home_url('/timeline-perfil?error=length'));
            exit;
        }

        $result = $wpdb->update(
            $this->table_users,
            array('password' => password_hash($new_password, PASSWORD_DEFAULT)),
            array('id' => $current_user->id)
        );

        if ($result !== false) {
            // REGISTRAR CAMBIO DE CONTRASEÑA
            if ($this->audit_log) {
                $this->audit_log->log(
                    $current_user->id,
                    'update',
                    'user',
                    $current_user->id,
                    'Contraseña actualizada'
                );
            }

            wp_redirect(home_url('/timeline-perfil?success=password_changed'));
        } else {
            wp_redirect(home_url('/timeline-perfil?error=failed'));
        }
        exit;
    }

    public function handle_create_project()
    {
        if (!$this->is_logged_in() || !$this->projects) {
            wp_redirect(home_url('/login-proyectos'));
            exit;
        }

        $current_user = $this->get_current_user();

        if (!$this->can_manage_projects($current_user)) {
            wp_die('No tienes permisos.');
        }

        if (
            !isset($_POST['timeline_project_nonce']) ||
            !wp_verify_nonce($_POST['timeline_project_nonce'], 'timeline_project_form')
        ) {
            wp_die('Error de seguridad');
        }

        $featured_image = '';
        if (!empty($_POST['featured_image'])) {
            $image_data = $_POST['featured_image'];
            if (strpos($image_data, 'data:image') === 0) {
                $featured_image = $this->save_base64_image($image_data, 'project');
            } else {
                $featured_image = $image_data;
            }
        }

        $project_data = array(
            'name' => $_POST['name'],
            'address' => $_POST['address'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'description' => $_POST['description'],
            'featured_image' => $featured_image,
            'project_status' => isset($_POST['project_status']) ? $_POST['project_status'] : 'en_proceso'
        );

        $project_id = $this->projects->create_project($project_data, $current_user->id);

        if ($project_id) {
            if (isset($_POST['clients']) && is_array($_POST['clients'])) {
                foreach ($_POST['clients'] as $client_id) {
                    $this->projects->assign_client_to_project($project_id, $client_id, $current_user->id);
                }
            }

            wp_redirect(home_url('/timeline-proyectos?success=created'));
        } else {
            wp_redirect(home_url('/timeline-proyecto-nuevo?error=failed'));
        }
        exit;
    }

    public function handle_update_project()
    {
        if (!$this->is_logged_in() || !$this->projects) {
            wp_redirect(home_url('/login-proyectos'));
            exit;
        }

        $current_user = $this->get_current_user();

        if (!$this->can_manage_projects($current_user)) {
            wp_die('No tienes permisos.');
        }

        if (
            !isset($_POST['timeline_project_nonce']) ||
            !wp_verify_nonce($_POST['timeline_project_nonce'], 'timeline_project_form')
        ) {
            wp_die('Error de seguridad');
        }

        $project_id = intval($_POST['project_id']);

        $featured_image = '';
        if (!empty($_POST['featured_image'])) {
            $image_data = $_POST['featured_image'];
            if (strpos($image_data, 'data:image') === 0) {
                $featured_image = $this->save_base64_image($image_data, 'project');
            } else {
                $featured_image = $image_data;
            }
        }

        $project_data = array(
            'name' => $_POST['name'],
            'address' => $_POST['address'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'description' => $_POST['description'],
            'featured_image' => $featured_image,
            'project_status' => isset($_POST['project_status']) ? $_POST['project_status'] : 'en_proceso'
        );

        $result = $this->projects->update_project($project_id, $project_data, $current_user->id);

        if ($result) {
            global $wpdb;
            $table = $this->db->get_table_name('project_clients');
            $wpdb->delete($table, array('project_id' => $project_id));

            if (isset($_POST['clients']) && is_array($_POST['clients'])) {
                foreach ($_POST['clients'] as $client_id) {
                    $this->projects->assign_client_to_project($project_id, $client_id, $current_user->id);
                }
            }

            wp_redirect(home_url('/timeline-proyectos?success=updated'));
        } else {
            wp_redirect(home_url('/timeline-proyecto-editar/' . $project_id . '?error=failed'));
        }
        exit;
    }

    private function save_base64_image($base64_string, $prefix = 'image')
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64_string, $type)) {
            $base64_string = substr($base64_string, strpos($base64_string, ',') + 1);
            $type = strtolower($type[1]);

            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                return '';
            }

            $base64_string = base64_decode($base64_string);
            if ($base64_string === false) {
                return '';
            }

            $upload_dir = wp_upload_dir();
            $timeline_dir = $upload_dir['basedir'] . '/timeline-projects';

            if (!file_exists($timeline_dir)) {
                wp_mkdir_p($timeline_dir);
            }

            $filename = $prefix . '_' . uniqid() . '.' . $type;
            $filepath = $timeline_dir . '/' . $filename;

            if (file_put_contents($filepath, $base64_string)) {
                return $upload_dir['baseurl'] . '/timeline-projects/' . $filename;
            }
        }

        return '';
    }

    private function send_welcome_email($username, $email, $password)
    {
        $login_url = home_url('/login-proyectos');
        $subject = 'Bienvenido a tu área de proyectos - BeBuilt';

        $message = "
        <!DOCTYPE html>
        <html>
        <head><meta charset='UTF-8'></head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50;'>Bienvenido a tu área de proyectos</h2>
                <p>Hola <strong>{$username}</strong>,</p>
                <p>Se ha creado tu cuenta para acceder al seguimiento de tus proyectos.</p>
                <div style='background-color: #f8f9fa; padding: 15px; margin: 20px 0;'>
                    <p style='margin: 5px 0;'><strong>Usuario:</strong> {$username}</p>
                    <p style='margin: 5px 0;'><strong>Contraseña:</strong> {$password}</p>
                </div>
                <p>Accede en: <a href='{$login_url}'>{$login_url}</a></p>
                <p style='margin-top: 30px;'>Saludos,<br><strong>BeBuilt</strong></p>
            </div>
        </body>
        </html>
        ";

        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($email, $subject, $message, $headers);
    }
}

// Inicializar plugin
$GLOBALS['timeline_plugin'] = new Timeline_Plugin();

// Hook de activación
register_activation_hook(__FILE__, function () {
    $plugin = new Timeline_Plugin();
    $plugin->activate();
    flush_rewrite_rules();
});

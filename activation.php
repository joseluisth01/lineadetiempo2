<?php
/**
 * Archivo de Activación del Plugin
 * Este archivo crea todas las tablas necesarias y el super admin inicial
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Función principal de activación
 */
function gp_activate_plugin() {
    global $wpdb;
    
    // Obtener el prefijo de la tabla
    $prefix = $wpdb->prefix;
    $charset_collate = $wpdb->get_charset_collate();
    
    // Tabla de usuarios del sistema
    $table_users = $prefix . 'gp_users';
    $sql_users = "CREATE TABLE IF NOT EXISTS $table_users (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        nombre varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        username varchar(100) NOT NULL,
        password varchar(255) NOT NULL,
        user_type enum('super_admin','admin','cliente') NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        created_by bigint(20) DEFAULT NULL,
        last_login datetime DEFAULT NULL,
        status enum('active','inactive') DEFAULT 'active',
        PRIMARY KEY (id),
        UNIQUE KEY username (username),
        UNIQUE KEY email (email)
    ) $charset_collate;";
    
    // Tabla de proyectos/obras
    $table_projects = $prefix . 'gp_projects';
    $sql_projects = "CREATE TABLE IF NOT EXISTS $table_projects (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        nombre varchar(255) NOT NULL,
        direccion text,
        fecha_inicio date NOT NULL,
        fecha_fin date NOT NULL,
        fecha_fin_real date DEFAULT NULL,
        imagen_principal varchar(255),
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        created_by bigint(20) DEFAULT NULL,
        updated_at datetime DEFAULT NULL,
        updated_by bigint(20) DEFAULT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    // Tabla de asignación de proyectos a clientes
    $table_project_users = $prefix . 'gp_project_users';
    $sql_project_users = "CREATE TABLE IF NOT EXISTS $table_project_users (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        project_id bigint(20) NOT NULL,
        user_id bigint(20) NOT NULL,
        assigned_at datetime DEFAULT CURRENT_TIMESTAMP,
        assigned_by bigint(20) DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY project_user (project_id, user_id)
    ) $charset_collate;";
    
    // Tabla de hitos
    $table_milestones = $prefix . 'gp_milestones';
    $sql_milestones = "CREATE TABLE IF NOT EXISTS $table_milestones (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        project_id bigint(20) NOT NULL,
        titulo varchar(255) NOT NULL,
        fecha date NOT NULL,
        descripcion text,
        estado enum('pendiente','en_proceso','finalizada') DEFAULT 'pendiente',
        icono varchar(255),
        orden int(11) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        created_by bigint(20) DEFAULT NULL,
        updated_at datetime DEFAULT NULL,
        updated_by bigint(20) DEFAULT NULL,
        PRIMARY KEY (id),
        KEY project_id (project_id)
    ) $charset_collate;";
    
    // Tabla de imágenes de hitos
    $table_milestone_images = $prefix . 'gp_milestone_images';
    $sql_milestone_images = "CREATE TABLE IF NOT EXISTS $table_milestone_images (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        milestone_id bigint(20) NOT NULL,
        imagen varchar(255) NOT NULL,
        orden int(11) DEFAULT 0,
        PRIMARY KEY (id),
        KEY milestone_id (milestone_id)
    ) $charset_collate;";
    
    // Tabla de documentos del proyecto
    $table_documents = $prefix . 'gp_documents';
    $sql_documents = "CREATE TABLE IF NOT EXISTS $table_documents (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        project_id bigint(20) NOT NULL,
        nombre varchar(255) NOT NULL,
        archivo varchar(255) NOT NULL,
        tipo varchar(100),
        uploaded_at datetime DEFAULT CURRENT_TIMESTAMP,
        uploaded_by bigint(20) DEFAULT NULL,
        PRIMARY KEY (id),
        KEY project_id (project_id)
    ) $charset_collate;";
    
    // Tabla de auditoría
    $table_audit = $prefix . 'gp_audit_log';
    $sql_audit = "CREATE TABLE IF NOT EXISTS $table_audit (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        accion varchar(100) NOT NULL,
        tabla varchar(100),
        registro_id bigint(20),
        detalles text,
        ip_address varchar(45),
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY created_at (created_at)
    ) $charset_collate;";
    
    // Ejecutar las queries
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_users);
    dbDelta($sql_projects);
    dbDelta($sql_project_users);
    dbDelta($sql_milestones);
    dbDelta($sql_milestone_images);
    dbDelta($sql_documents);
    dbDelta($sql_audit);
    
    // Crear super admin si no existe
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_users WHERE username = %s",
        'administrador'
    ));
    
    if ($exists == 0) {
        $wpdb->insert($table_users, [
            'nombre' => 'Super Administrador',
            'email' => get_option('admin_email'),
            'username' => 'administrador',
            'password' => password_hash('adminproyectos', PASSWORD_DEFAULT),
            'user_type' => 'super_admin',
            'status' => 'active'
        ]);
        
        // Log de creación del super admin
        $user_id = $wpdb->insert_id;
        $wpdb->insert($table_audit, [
            'user_id' => $user_id,
            'accion' => 'plugin_activated',
            'detalles' => 'Plugin activado y super admin creado',
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ]);
    }
    
    // Crear página de login si no existe
    $login_page = get_page_by_path('login-proyectos');
    
    if (!$login_page) {
        wp_insert_post([
            'post_title' => 'Login Proyectos',
            'post_name' => 'login-proyectos',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '[gp_login_form]'
        ]);
    }
    
    // Limpiar rewrite rules
    flush_rewrite_rules();
}

/**
 * Función de desactivación
 */
function gp_deactivate_plugin() {
    flush_rewrite_rules();
}

/**
 * NOTA: No eliminamos las tablas en la desactivación para preservar los datos.
 * Si deseas eliminar las tablas completamente, crea una función de desinstalación.
 */
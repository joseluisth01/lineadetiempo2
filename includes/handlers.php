<?php
/**
 * Handlers para acciones de hitos
 * Archivo: includes/handlers.php
 */

if (!defined('ABSPATH')) exit;

class Timeline_Handlers {
    
    private $milestones;
    
    public function __construct() {
        $this->milestones = Timeline_Milestones::get_instance();
        
        // Registrar handlers
        add_action('admin_post_timeline_save_milestone', array($this, 'handle_save_milestone'));
        add_action('admin_post_timeline_delete_milestone', array($this, 'handle_delete_milestone'));
    }
    
    /**
     * Guardar hito (crear o actualizar)
     */
    public function handle_save_milestone() {
        // Verificar login
        if (!isset($_SESSION['timeline_user_id'])) {
            wp_redirect(home_url('/login-proyectos'));
            exit;
        }
        
        // Verificar nonce
        if (!isset($_POST['timeline_milestone_nonce']) || 
            !wp_verify_nonce($_POST['timeline_milestone_nonce'], 'timeline_milestone')) {
            wp_die('Error de seguridad');
        }
        
        global $wpdb;
        $user_id = $_SESSION['timeline_user_id'];
        $project_id = intval($_POST['project_id']);
        $milestone_id = isset($_POST['milestone_id']) && !empty($_POST['milestone_id']) 
            ? intval($_POST['milestone_id']) 
            : null;
        
        // Verificar permisos
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}timeline_users WHERE id = %d",
            $user_id
        ));
        
        if (!in_array($user->role, array('super_admin', 'administrador'))) {
            wp_die('No tienes permisos para realizar esta acción.');
        }
        
        // Preparar datos
        $milestone_data = array(
            'project_id' => $project_id,
            'title' => sanitize_text_field($_POST['title']),
            'date' => sanitize_text_field($_POST['date']),
            'description' => sanitize_textarea_field($_POST['description']),
            'status' => sanitize_text_field($_POST['status'])
        );
        
        // Crear o actualizar hito
        if ($milestone_id) {
            // Actualizar
            $result = $this->milestones->update_milestone($milestone_id, $milestone_data, $user_id);
            
            if ($result) {
                // Eliminar imágenes antiguas si hay nuevas
                if (!empty($_POST['images_data'])) {
                    $db = Timeline_Database::get_instance();
                    $wpdb->delete(
                        $db->get_table_name('milestone_images'),
                        array('milestone_id' => $milestone_id)
                    );
                }
                
                $final_milestone_id = $milestone_id;
            } else {
                wp_redirect(home_url('/timeline-proyecto-admin/' . $project_id . '?error=update_failed'));
                exit;
            }
        } else {
            // Crear nuevo
            $final_milestone_id = $this->milestones->create_milestone($milestone_data, $user_id);
            
            if (!$final_milestone_id) {
                wp_redirect(home_url('/timeline-proyecto-admin/' . $project_id . '?error=create_failed'));
                exit;
            }
        }
        
        // Guardar imágenes
        if (!empty($_POST['images_data'])) {
            $images = json_decode(stripslashes($_POST['images_data']), true);
            
            if (is_array($images)) {
                foreach ($images as $index => $base64_image) {
                    $image_url = $this->save_base64_image($base64_image, 'milestone_' . $final_milestone_id);
                    
                    if ($image_url) {
                        $this->milestones->add_milestone_image($final_milestone_id, $image_url, $index);
                    }
                }
            }
        }
        
        wp_redirect(home_url('/timeline-proyecto-admin/' . $project_id . '?success=saved'));
        exit;
    }
    
    /**
     * Eliminar hito
     */
    public function handle_delete_milestone() {
        // Verificar login
        if (!isset($_SESSION['timeline_user_id'])) {
            wp_redirect(home_url('/login-proyectos'));
            exit;
        }
        
        // Verificar nonce
        if (!isset($_GET['_wpnonce']) || 
            !wp_verify_nonce($_GET['_wpnonce'], 'delete_milestone')) {
            wp_die('Error de seguridad');
        }
        
        global $wpdb;
        $user_id = $_SESSION['timeline_user_id'];
        $milestone_id = intval($_GET['milestone_id']);
        $project_id = intval($_GET['project_id']);
        
        // Verificar permisos
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}timeline_users WHERE id = %d",
            $user_id
        ));
        
        if (!in_array($user->role, array('super_admin', 'administrador'))) {
            wp_die('No tienes permisos para realizar esta acción.');
        }
        
        // Eliminar hito
        $result = $this->milestones->delete_milestone($milestone_id, $user_id);
        
        if ($result) {
            wp_redirect(home_url('/timeline-proyecto-admin/' . $project_id . '?success=deleted'));
        } else {
            wp_redirect(home_url('/timeline-proyecto-admin/' . $project_id . '?error=delete_failed'));
        }
        exit;
    }
    
    /**
     * Guardar imagen base64 como archivo
     */
    private function save_base64_image($base64_string, $prefix = 'image') {
        // Extraer el tipo de imagen y los datos
        if (preg_match('/^data:image\/(\w+);base64,/', $base64_string, $type)) {
            $base64_string = substr($base64_string, strpos($base64_string, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif
            
            // Validar tipo de imagen
            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                return '';
            }
            
            $base64_string = base64_decode($base64_string);
            
            if ($base64_string === false) {
                return '';
            }
            
            // Crear directorio si no existe
            $upload_dir = wp_upload_dir();
            $timeline_dir = $upload_dir['basedir'] . '/timeline-milestones';
            
            if (!file_exists($timeline_dir)) {
                wp_mkdir_p($timeline_dir);
            }
            
            // Generar nombre único
            $filename = $prefix . '_' . uniqid() . '_' . time() . '.' . $type;
            $filepath = $timeline_dir . '/' . $filename;
            
            // Guardar archivo
            if (file_put_contents($filepath, $base64_string)) {
                // Retornar URL
                return $upload_dir['baseurl'] . '/timeline-milestones/' . $filename;
            }
        }
        
        return '';
    }
}

// Inicializar handlers
new Timeline_Handlers();
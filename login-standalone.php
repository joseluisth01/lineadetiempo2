<?php
/**
 * Template Standalone: Login (Sin Header ni Footer)
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestión de Proyectos</title>
    
    <!-- CSS del plugin -->
    <link rel="stylesheet" href="<?php echo GP_PLUGIN_URL; ?>assets/css/styles.css?v=<?php echo GP_VERSION; ?>">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        /* Reset básico */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        /* Ocultar cualquier elemento de WordPress */
        #wpadminbar {
            display: none !important;
        }
    </style>
</head>
<body>
    
    <div class="gp-login-container">
        <div class="gp-login-box">
            <div class="gp-login-header">
                <h1>Sistema de Gestión de Proyectos</h1>
                <p>Inicia sesión para continuar</p>
            </div>
            
            <form id="gp-login-form" class="gp-login-form">
                <div class="gp-form-group">
                    <label for="gp-username">Usuario</label>
                    <input type="text" id="gp-username" name="username" required placeholder="Ingresa tu usuario" autocomplete="username">
                </div>
                
                <div class="gp-form-group">
                    <label for="gp-password">Contraseña</label>
                    <input type="password" id="gp-password" name="password" required placeholder="Ingresa tu contraseña" autocomplete="current-password">
                </div>
                
                <div class="gp-form-message" style="display: none;"></div>
                
                <button type="submit" class="gp-btn gp-btn-primary">
                    <span class="gp-btn-text">Iniciar Sesión</span>
                    <span class="gp-btn-loader" style="display: none;">Cargando...</span>
                </button>
            </form>
            
            <div class="gp-login-footer">
                <p>¿Problemas para iniciar sesión? Contacta con tu administrador.</p>
            </div>
        </div>
    </div>
    
    <!-- JavaScript del plugin -->
    <script>
        var gpAjax = {
            ajaxurl: '<?php echo admin_url('admin-ajax.php'); ?>',
            nonce: '<?php echo wp_create_nonce('gp_nonce'); ?>'
        };
    </script>
    <script src="<?php echo GP_PLUGIN_URL; ?>assets/js/scripts.js?v=<?php echo GP_VERSION; ?>"></script>
    
</body>
</html>
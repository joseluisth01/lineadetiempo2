<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Timeline</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', sans-serif;
            background: #0a0a0a;
            color: #ffffff;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-brand {
            font-size: 18px;
            font-weight: 300;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .navbar-menu {
            display: flex;
            gap: 40px;
            align-items: center;
        }
        
        .navbar-menu a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 12px;
            letter-spacing: 2px;
            text-transform: uppercase;
            transition: color 0.3s;
            font-weight: 300;
        }
        
        .navbar-menu a:hover,
        .navbar-menu a.active {
            color: rgba(200, 150, 100, 0.9);
        }
        
        .navbar-user {
            display: flex;
            align-items: center;
            gap: 30px;
        }
        
        .user-info {
            text-align: right;
        }
        
        .user-name {
            font-size: 13px;
            font-weight: 300;
            color: rgba(255, 255, 255, 0.9);
            letter-spacing: 1px;
        }
        
        .user-role {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.4);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-top: 2px;
        }
        
        .btn-logout {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.7);
            padding: 8px 20px;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            font-weight: 300;
        }
        
        .btn-logout:hover {
            border-color: rgba(200, 150, 100, 0.5);
            color: rgba(200, 150, 100, 0.9);
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 60px 40px;
        }
        
        .page-header {
            margin-bottom: 50px;
        }
        
        .page-header h1 {
            font-size: 42px;
            font-weight: 200;
            color: rgba(255, 255, 255, 0.95);
            letter-spacing: 1px;
        }
        
        .alert {
            padding: 15px 25px;
            margin-bottom: 40px;
            font-size: 12px;
            border-left: 1px solid;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            letter-spacing: 0.5px;
        }
        
        .alert-success {
            color: rgba(81, 207, 102, 0.9);
            border-color: rgba(81, 207, 102, 0.5);
        }
        
        .alert-error {
            color: rgba(255, 107, 107, 0.9);
            border-color: rgba(255, 107, 107, 0.5);
        }
        
        .card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 50px 40px;
            margin-bottom: 30px;
        }
        
        .card h2 {
            font-size: 20px;
            font-weight: 300;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 30px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        .info-row {
            display: flex;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            width: 180px;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 300;
        }
        
        .info-value {
            flex: 1;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 300;
        }
        
        .form-group {
            margin-bottom: 35px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 12px;
            color: rgba(255, 255, 255, 0.6);
            font-weight: 300;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 0;
            background: transparent;
            border: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            font-size: 14px;
            font-weight: 300;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-bottom-color: rgba(200, 150, 100, 0.6);
        }
        
        .help-text {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.3);
            margin-top: 8px;
            letter-spacing: 1px;
        }
        
        .btn-primary {
            width: 100%;
            padding: 16px;
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 11px;
            font-weight: 400;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        
        .btn-primary:hover {
            background: rgba(200, 150, 100, 0.15);
            border-color: rgba(200, 150, 100, 0.5);
        }
        
        @media (max-width: 768px) {
            .navbar {
                padding: 20px;
                flex-direction: column;
                gap: 20px;
            }
            
            .navbar-menu {
                gap: 20px;
            }
            
            .container {
                padding: 40px 20px;
            }
            
            .page-header h1 {
                font-size: 32px;
            }
            
            .card {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">BeBuilt</div>
        <div class="navbar-menu">
            <a href="<?php echo home_url('/timeline-dashboard'); ?>">Dashboard</a>
            <?php if ($current_user && ($current_user->role === 'super_admin' || $current_user->role === 'administrador')): ?>
                <a href="<?php echo home_url('/timeline-usuarios'); ?>">Usuarios</a>
            <?php endif; ?>
            <a href="<?php echo home_url('/timeline-perfil'); ?>" class="active">Perfil</a>
        </div>
        <div class="navbar-user">
            <div class="user-info">
                <div class="user-name"><?php echo esc_html($current_user->username); ?></div>
                <div class="user-role">
                    <?php 
                        switch($current_user->role) {
                            case 'super_admin':
                                echo 'Super Administrador';
                                break;
                            case 'administrador':
                                echo 'Administrador';
                                break;
                            case 'cliente':
                                echo 'Cliente';
                                break;
                        }
                    ?>
                </div>
            </div>
            <a href="<?php echo admin_url('admin-post.php?action=timeline_logout'); ?>" class="btn-logout">
                Salir
            </a>
        </div>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <h1>Mi Perfil</h1>
        </div>
        
        <?php if (isset($_GET['success']) && $_GET['success'] == 'password_changed'): ?>
            <div class="alert alert-success">
                Contraseña cambiada correctamente.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?php
                switch($_GET['error']) {
                    case 'current_password':
                        echo 'La contraseña actual es incorrecta.';
                        break;
                    case 'match':
                        echo 'Las contraseñas no coinciden.';
                        break;
                    case 'length':
                        echo 'La contraseña debe tener al menos 8 caracteres.';
                        break;
                    case 'failed':
                        echo 'Error al cambiar la contraseña. Intenta de nuevo.';
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Información Personal</h2>
            
            <div class="info-row">
                <div class="info-label">Usuario</div>
                <div class="info-value"><?php echo esc_html($current_user->username); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Email</div>
                <div class="info-value"><?php echo esc_html($current_user->email); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Rol</div>
                <div class="info-value">
                    <?php 
                        switch($current_user->role) {
                            case 'super_admin':
                                echo 'Super Administrador';
                                break;
                            case 'administrador':
                                echo 'Administrador';
                                break;
                            case 'cliente':
                                echo 'Cliente';
                                break;
                        }
                    ?>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Miembro desde</div>
                <div class="info-value"><?php echo date('d/m/Y', strtotime($current_user->created_at)); ?></div>
            </div>
        </div>
        
        <div class="card">
            <h2>Cambiar Contraseña</h2>
            
            <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="timeline_change_password">
                <?php wp_nonce_field('timeline_change_password', 'timeline_change_password_nonce'); ?>
                
                <div class="form-group">
                    <label for="current_password">Contraseña Actual</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">Nueva Contraseña</label>
                    <input type="password" id="new_password" name="new_password" required>
                    <div class="help-text">Mínimo 8 caracteres</div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Nueva Contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn-primary">Cambiar Contraseña</button>
            </form>
        </div>
    </div>
</body>
</html>
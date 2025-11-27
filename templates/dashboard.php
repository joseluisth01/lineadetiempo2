<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Timeline</title>
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
            max-width: 1400px;
            margin: 0 auto;
            padding: 60px 40px;
        }
        
        .welcome-section {
            margin-bottom: 80px;
        }
        
        .welcome-section h1 {
            font-size: 42px;
            font-weight: 200;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        
        .welcome-section p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.4);
            font-weight: 300;
            letter-spacing: 1px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 80px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 40px 30px;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            border-color: rgba(200, 150, 100, 0.3);
            background: rgba(255, 255, 255, 0.03);
        }
        
        .stat-title {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.4);
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 300;
        }
        
        .stat-value {
            font-size: 56px;
            font-weight: 200;
            margin-bottom: 10px;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .stat-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.3);
            font-weight: 300;
            letter-spacing: 1px;
        }
        
        .info-section {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 50px 40px;
        }
        
        .info-section h2 {
            font-size: 24px;
            font-weight: 300;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 20px;
            letter-spacing: 1px;
        }
        
        .info-section p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
            line-height: 1.8;
            font-weight: 300;
            letter-spacing: 0.5px;
        }
        
        .info-section ul {
            list-style: none;
            margin-top: 30px;
            padding: 0;
        }
        
        .info-section li {
            padding: 12px 0;
            color: rgba(255, 255, 255, 0.6);
            font-size: 13px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-weight: 300;
            letter-spacing: 0.5px;
        }
        
        .info-section li:last-child {
            border-bottom: none;
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
            
            .welcome-section h1 {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">BeBuilt</div>
        <div class="navbar-menu">
            <a href="<?php echo home_url('/timeline-dashboard'); ?>" class="active">Dashboard</a>
            <?php if ($current_user && ($current_user->role === 'super_admin' || $current_user->role === 'administrador')): ?>
                <a href="<?php echo home_url('/timeline-usuarios'); ?>">Usuarios</a>
            <?php endif; ?>
            <a href="<?php echo home_url('/timeline-perfil'); ?>">Perfil</a>
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
        <div class="welcome-section">
            <h1>Bienvenido, <?php echo esc_html($current_user->username); ?></h1>
            <p>Gestión de proyectos y seguimiento de actividades</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">Proyectos Activos</div>
                <div class="stat-value">0</div>
                <div class="stat-label">En progreso</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Tareas Pendientes</div>
                <div class="stat-value">0</div>
                <div class="stat-label">Por completar</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Completadas</div>
                <div class="stat-value">0</div>
                <div class="stat-label">Este mes</div>
            </div>
        </div>
        
        <div class="info-section">
            <h2>Sistema Timeline</h2>
            <p>Plataforma de gestión integral de proyectos de construcción para BeBuilt. Próximas funcionalidades en desarrollo:</p>
            <ul>
                <li>Gestión completa de proyectos de construcción</li>
                <li>Sistema de tareas y subtareas con seguimiento</li>
                <li>Timeline visual de actividades y milestones</li>
                <li>Notificaciones en tiempo real</li>
                <li>Reportes y análisis detallados</li>
            </ul>
        </div>
    </div>
</body>
</html>
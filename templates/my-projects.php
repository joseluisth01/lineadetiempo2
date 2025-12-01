<?php

/**
 * Template: Mis Proyectos (Vista Cliente)
 * Explora tus proyectos - Diseño según imagen proporcionada
 */
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Proyectos - Timeline</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #ffffff;
            color: #000000;
        }

        .navbar {
            background: #ffffff;
            border-bottom: 1px solid #e0e0e0;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            font-size: 18px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #000;
        }

        .navbar-menu {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .navbar-menu a {
            color: #666;
            text-decoration: none;
            font-size: 12px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            transition: color 0.3s;
            font-weight: 500;
        }

        .navbar-menu a:hover,
        .navbar-menu a.active {
            color: #000;
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
            font-size: 14px;
            font-weight: 500;
            color: #000;
        }

        .btn-logout {
            background: transparent;
            border: 1px solid #000;
            color: #000;
            padding: 8px 20px;
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-logout:hover {
            background: #000;
            color: #fff;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 80px 40px;
        }

        .intro-section {
            text-align: center;
            margin-bottom: 80px;
        }

        .intro-section h1 {
            font-size: 48px;
            font-weight: 400;
            margin-bottom: 20px;
        }

        .intro-section h1 .highlight {
            font-weight: 700;
        }

        .intro-section p {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin: 0 auto;
        }

        .intro-section p strong {
            font-weight: 600;
            color: #000;
        }

        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(28%, 1fr));
            gap: 40px;
        }

        /* Primer proyecto ocupa todo el ancho */
        .project-card:first-child {
            grid-column: 1 / -1;
            margin-bottom: 30px;
        }

        

        .project-card {
            position: relative;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }


        .project-status {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #000;
            color: #fff;
            padding: 8px 16px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            z-index: 10;
            border-radius: 10px;
        }

        .project-status.en-proceso {
            color: #FDC425;
        }

        .project-status.finalizado {
            color: #FFDE88;
        }

        .project-image {
            width: 100%;
            height: 350px;
            object-fit: cover;
            display: block;
            border-radius: 20px;
        }

        .project-content {
            background: #fff;
            text-align: center;
        }

        .project-title {
            font-size: 25px;
            font-weight: bold;
            color: #000;
            margin-bottom: 30px;
            margin-top: 20px;
            line-height: 1.3;
            text-align: center;
        }

        .btn-view-project {
            display: inline-block;
            background: #FDC425;
            color: #000;
            padding: 14px 30px;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            width: 100%;
            text-align: center;
            margin: 0 auto;
            border-radius: 10px;
        }

        .btn-view-project:hover {
            background: #e5b01f;
        }

        .empty-state {
            text-align: center;
            padding: 100px 20px;
            color: #999;
        }

        .empty-state h2 {
            font-size: 24px;
            font-weight: 300;
            margin-bottom: 15px;
        }

        .empty-state p {
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 20px;
            }

            .container {
                padding: 40px 20px;
            }

            .intro-section h1 {
                font-size: 32px;
            }

            .projects-grid {
                grid-template-columns: 1fr;
            }

            .project-card:first-child .project-image {
                height: 350px;
            }

            .project-card:first-child .project-title {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="navbar-brand">BeBuilt</div>
        <div class="navbar-menu">
            <a href="<?php echo home_url('/timeline-mis-proyectos'); ?>" class="active">Mis Proyectos</a>
            <a href="<?php echo home_url('/timeline-perfil'); ?>">Mi Perfil</a>
        </div>
        <div class="navbar-user">
            <div class="user-info">
                <div class="user-name"><?php echo esc_html($current_user->username); ?></div>
            </div>
            <a href="<?php echo admin_url('admin-post.php?action=timeline_logout'); ?>" class="btn-logout">
                Salir
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="intro-section">
            <h1>Explora <span class="highlight">tus proyectos</span></h1>
            <p>Esta es tu <strong>área de proyectos</strong>, aquí puedes acceder a <strong>toda la información</strong> sobre cada obra, ya esté finalizada o en proceso.</p>
        </div>

        <div class="projects-grid">
            <?php if (count($projects) > 0): ?>
                <?php foreach ($projects as $project): ?>
                    <?php 
                    $image = $project->featured_image ? $project->featured_image : 'https://via.placeholder.com/400x350/cccccc/666666?text=Sin+Imagen';
                    
                    // DEBUG: Ver qué trae el proyecto
                    // error_log('Proyecto ID: ' . $project->id . ' - project_status: ' . (isset($project->project_status) ? $project->project_status : 'NO DEFINIDO'));
                    
                    // Mapear el estado del proyecto
                    $status_map = [
                        'en_proceso' => ['label' => 'EN PROCESO', 'class' => 'en-proceso'],
                        'pendiente' => ['label' => 'PENDIENTE', 'class' => 'pendiente'],
                        'finalizado' => ['label' => 'FINALIZADO', 'class' => 'finalizado']
                    ];
                    
                    // Usar project_status, no status
                    $current_status = isset($project->project_status) && !empty($project->project_status) 
                        ? $project->project_status 
                        : 'en_proceso';
                    
                    $project_status = isset($status_map[$current_status]) 
                        ? $status_map[$current_status] 
                        : $status_map['en_proceso'];
                    
                    $status_label = $project_status['label'];
                    $status_class = $project_status['class'];
                    ?>
                    <div class="project-card">
                        <div class="project-status <?php echo $status_class; ?>"><?php echo $status_label; ?></div>
                        <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($project->name); ?>" class="project-image">
                        <div class="project-content">
                            <h2 class="project-title"><?php echo esc_html($project->name); ?></h2>
                            <a href="<?php echo home_url('/timeline-proyecto/' . $project->id); ?>" class="btn-view-project">
                                Ver información del proyecto
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h2>No tienes proyectos asignados</h2>
                    <p>Contacta con tu administrador para más información</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
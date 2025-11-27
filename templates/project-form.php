<?php
/**
 * Template: Formulario de Proyecto (Crear/Editar)
 */
$is_edit = isset($project) && $project;
$page_title = $is_edit ? 'Editar Proyecto' : 'Nuevo Proyecto';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Timeline</title>
    <?php wp_head(); ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
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
        }
        
        .navbar-menu a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 12px;
            letter-spacing: 2px;
            text-transform: uppercase;
            transition: color 0.3s;
        }
        
        .navbar-menu a.active {
            color: rgba(200, 150, 100, 0.9);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 40px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 50px;
        }
        
        .page-header h1 {
            font-size: 42px;
            font-weight: 200;
            color: rgba(255, 255, 255, 0.95);
        }
        
        .btn-back {
            padding: 12px 24px;
            background: transparent;
            color: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            border-color: rgba(255, 255, 255, 0.5);
            color: rgba(255, 255, 255, 0.9);
        }
        
        .form-container {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 50px;
        }
        
        .form-section {
            margin-bottom: 50px;
        }
        
        .form-section:last-child {
            margin-bottom: 0;
        }
        
        .form-section-title {
            font-size: 16px;
            font-weight: 300;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 30px;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }
        
        .form-group {
            margin-bottom: 30px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 12px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 300;
        }
        
        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group input[type="url"],
        .form-group textarea,
        .form-group select {
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
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
            padding: 14px;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: rgba(253, 196, 37, 0.6);
        }
        
        .help-text {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.3);
            margin-top: 8px;
            letter-spacing: 1px;
        }
        
        .clients-selection {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .client-checkbox {
            display: flex;
            align-items: center;
            padding: 12px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }
        
        .client-checkbox:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(253, 196, 37, 0.3);
        }
        
        .client-checkbox input[type="checkbox"] {
            margin-right: 12px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .client-checkbox label {
            margin: 0;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
            letter-spacing: 0.5px;
            text-transform: none;
            cursor: pointer;
            flex: 1;
        }
        
        .form-actions {
            display: flex;
            gap: 20px;
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn-submit {
            flex: 1;
            padding: 16px;
            background: rgba(253, 196, 37, 0.15);
            color: #FDC425;
            border: 1px solid #FDC425;
            font-size: 12px;
            font-weight: 400;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background: rgba(253, 196, 37, 0.25);
        }
        
        .btn-cancel {
            padding: 16px 40px;
            background: transparent;
            color: rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 12px;
            font-weight: 400;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
            text-align: center;
        }
        
        .btn-cancel:hover {
            border-color: rgba(255, 255, 255, 0.5);
            color: rgba(255, 255, 255, 0.8);
        }
        
        .image-preview {
            margin-top: 20px;
            max-width: 400px;
        }
        
        .image-preview img {
            width: 100%;
            height: auto;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 40px 20px;
            }
            
            .form-container {
                padding: 30px 20px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">BeBuilt</div>
        <div class="navbar-menu">
            <a href="<?php echo home_url('/timeline-dashboard'); ?>">Dashboard</a>
            <a href="<?php echo home_url('/timeline-proyectos'); ?>" class="active">Proyectos</a>
            <a href="<?php echo home_url('/timeline-usuarios'); ?>">Usuarios</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <h1><?php echo $page_title; ?></h1>
            <a href="<?php echo home_url('/timeline-proyectos'); ?>" class="btn-back">← Volver</a>
        </div>
        
        <div class="form-container">
            <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>" id="project-form">
                <input type="hidden" name="action" value="<?php echo $is_edit ? 'timeline_update_project' : 'timeline_create_project'; ?>">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="project_id" value="<?php echo $project->id; ?>">
                <?php endif; ?>
                <?php wp_nonce_field('timeline_project_form', 'timeline_project_nonce'); ?>
                
                <!-- Información Básica -->
                <div class="form-section">
                    <h2 class="form-section-title">Información Básica</h2>
                    
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="name">Nombre del Proyecto *</label>
                            <input type="text" id="name" name="name" required 
                                   value="<?php echo $is_edit ? esc_attr($project->name) : ''; ?>">
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="address">Dirección</label>
                            <input type="text" id="address" name="address" 
                                   value="<?php echo $is_edit ? esc_attr($project->address) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="start_date">Fecha de Inicio *</label>
                            <input type="date" id="start_date" name="start_date" required 
                                   value="<?php echo $is_edit ? esc_attr($project->start_date) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="end_date">Fecha de Fin Prevista *</label>
                            <input type="date" id="end_date" name="end_date" required 
                                   value="<?php echo $is_edit ? esc_attr($project->end_date) : ''; ?>">
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="description">Descripción</label>
                            <textarea id="description" name="description"><?php echo $is_edit ? esc_textarea($project->description) : ''; ?></textarea>
                            <div class="help-text">Descripción general del proyecto</div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="featured_image">URL de Imagen Destacada</label>
                            <input type="url" id="featured_image" name="featured_image" 
                                   value="<?php echo $is_edit ? esc_attr($project->featured_image) : ''; ?>"
                                   onchange="previewImage(this.value)">
                            <div class="help-text">URL completa de la imagen principal del proyecto</div>
                            
                            <?php if ($is_edit && $project->featured_image): ?>
                            <div class="image-preview" id="image-preview">
                                <img src="<?php echo esc_url($project->featured_image); ?>" alt="Preview">
                            </div>
                            <?php else: ?>
                            <div class="image-preview" id="image-preview" style="display: none;">
                                <img src="" alt="Preview" id="preview-img">
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Asignación de Clientes -->
                <div class="form-section">
                    <h2 class="form-section-title">Asignación de Clientes</h2>
                    
                    <?php if (!empty($available_clients)): ?>
                    <div class="clients-selection">
                        <?php foreach ($available_clients as $client): ?>
                            <?php 
                            $is_assigned = false;
                            if ($is_edit && !empty($assigned_clients)) {
                                foreach ($assigned_clients as $assigned) {
                                    if ($assigned->id == $client->id) {
                                        $is_assigned = true;
                                        break;
                                    }
                                }
                            }
                            ?>
                            <div class="client-checkbox">
                                <input type="checkbox" 
                                       id="client_<?php echo $client->id; ?>" 
                                       name="clients[]" 
                                       value="<?php echo $client->id; ?>"
                                       <?php echo $is_assigned ? 'checked' : ''; ?>>
                                <label for="client_<?php echo $client->id; ?>">
                                    <?php echo esc_html($client->username); ?>
                                    <br><small style="color: rgba(255,255,255,0.4);"><?php echo esc_html($client->email); ?></small>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p style="color: rgba(255,255,255,0.5); font-size: 13px;">
                        No hay clientes disponibles. <a href="<?php echo home_url('/timeline-usuarios'); ?>" style="color: #FDC425;">Crear un cliente</a>
                    </p>
                    <?php endif; ?>
                </div>
                
                <!-- Acciones -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <?php echo $is_edit ? 'Actualizar Proyecto' : 'Crear Proyecto'; ?>
                    </button>
                    <a href="<?php echo home_url('/timeline-proyectos'); ?>" class="btn-cancel">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function previewImage(url) {
            const preview = document.getElementById('image-preview');
            const img = document.getElementById('preview-img');
            
            if (url) {
                img.src = url;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
    <?php wp_footer(); ?>
</body>
</html>
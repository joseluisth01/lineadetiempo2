<?php
/**
 * Template: Gestión de Timeline del Proyecto (Vista Administrador)
 * Archivo: admin/project-timeline-admin.php
 */

$project_id = get_query_var('timeline_id');
$projects_class = Timeline_Projects::get_instance();
$milestones_class = Timeline_Milestones::get_instance();

$project = $projects_class->get_project($project_id);
if (!$project) {
    wp_redirect(home_url('/timeline-proyectos'));
    exit;
}

$milestones = $milestones_class->get_project_milestones_with_images($project_id);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline - <?php echo esc_html($project->name); ?></title>
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
        
        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 60px 40px;
        }
        
        .page-header {
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            font-size: 36px;
            font-weight: 200;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 10px;
        }
        
        .page-header p {
            color: rgba(255, 255, 255, 0.4);
            font-size: 13px;
        }
        
        .btn-add-milestone {
            padding: 14px 30px;
            background: rgba(253, 196, 37, 0.15);
            color: #FDC425;
            border: 1px solid #FDC425;
            font-size: 11px;
            font-weight: 400;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 40px;
        }
        
        .btn-add-milestone:hover {
            background: rgba(253, 196, 37, 0.25);
        }
        
        .milestones-list {
            display: grid;
            gap: 20px;
        }
        
        .milestone-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 30px;
            display: grid;
            grid-template-columns: 150px 1fr auto;
            gap: 30px;
            align-items: start;
        }
        
        .milestone-date {
            text-align: center;
        }
        
        .milestone-date-day {
            font-size: 48px;
            font-weight: 200;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .milestone-date-month {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.4);
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .milestone-content h3 {
            font-size: 20px;
            font-weight: 300;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 10px;
        }
        
        .milestone-description {
            color: rgba(255, 255, 255, 0.5);
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .milestone-status {
            display: inline-block;
            padding: 6px 14px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        
        .status-pendiente { background: #EDEDED; color: #666; }
        .status-en_proceso { background: #FDC425; color: #000; }
        .status-finalizado { background: #FFDE88; color: #000; }
        
        .milestone-images {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .milestone-images img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .milestone-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .btn-small {
            padding: 10px 20px;
            font-size: 10px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: transparent;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-small:hover {
            border-color: rgba(253, 196, 37, 0.5);
            color: #FDC425;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: rgba(255, 255, 255, 0.3);
        }
        
        /* Modal de crear/editar hito */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            overflow-y: auto;
        }
        
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: #0a0a0a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            width: 90%;
            max-width: 800px;
            padding: 50px;
            position: relative;
            margin: 40px 0;
        }
        
        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 30px;
            color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .modal-close:hover {
            color: rgba(255, 255, 255, 0.9);
        }
        
        .modal h2 {
            font-size: 24px;
            font-weight: 300;
            margin-bottom: 40px;
            letter-spacing: 2px;
        }
        
        .form-group {
            margin-bottom: 30px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 12px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            font-size: 14px;
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: rgba(253, 196, 37, 0.15);
            color: #FDC425;
            border: 1px solid #FDC425;
            font-size: 12px;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }
        
        .btn-submit:hover {
            background: rgba(253, 196, 37, 0.25);
        }
        
        .images-upload-area {
            border: 2px dashed rgba(255, 255, 255, 0.2);
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .images-upload-area:hover {
            border-color: rgba(253, 196, 37, 0.5);
        }
        
        .preview-images {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .preview-image-item {
            position: relative;
        }
        
        .preview-image-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .remove-preview {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 107, 107, 0.9);
            color: #fff;
            border: none;
            width: 25px;
            height: 25px;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">BeBuilt</div>
        <a href="<?php echo home_url('/timeline-proyectos'); ?>" class="btn-back">← Volver a Proyectos</a>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <h1><?php echo esc_html($project->name); ?></h1>
            <p>Gestión de Timeline y Hitos</p>
        </div>
        
        <button class="btn-add-milestone" onclick="openMilestoneModal()">+ Añadir Hito</button>
        
        <div class="milestones-list">
            <?php if (count($milestones) > 0): ?>
                <?php foreach ($milestones as $milestone): ?>
                    <?php 
                    $date = new DateTime($milestone->date);
                    $status_class = 'status-' . $milestone->status;
                    $status_label = [
                        'pendiente' => 'Pendiente',
                        'en_proceso' => 'En Proceso',
                        'finalizado' => 'Finalizado'
                    ][$milestone->status] ?? $milestone->status;
                    ?>
                    <div class="milestone-card">
                        <div class="milestone-date">
                            <div class="milestone-date-day"><?php echo $date->format('d'); ?></div>
                            <div class="milestone-date-month"><?php echo $date->format('M Y'); ?></div>
                        </div>
                        
                        <div class="milestone-content">
                            <h3><?php echo esc_html($milestone->title); ?></h3>
                            <p class="milestone-description"><?php echo esc_html($milestone->description); ?></p>
                            <span class="milestone-status <?php echo $status_class; ?>"><?php echo $status_label; ?></span>
                            
                            <?php if (!empty($milestone->images)): ?>
                            <div class="milestone-images">
                                <?php foreach ($milestone->images as $image): ?>
                                    <img src="<?php echo esc_url($image->image_url); ?>" alt="Imagen">
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="milestone-actions">
                            <button class="btn-small" onclick="editMilestone(<?php echo $milestone->id; ?>)">Editar</button>
                            <button class="btn-small" onclick="deleteMilestone(<?php echo $milestone->id; ?>)">Eliminar</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>No hay hitos creados en este proyecto</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal para crear/editar hito -->
    <div id="milestoneModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeMilestoneModal()">&times;</span>
            <h2 id="modal-title">Nuevo Hito</h2>
            
            <form id="milestone-form" method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="timeline_save_milestone">
                <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                <input type="hidden" id="milestone_id" name="milestone_id" value="">
                <?php wp_nonce_field('timeline_milestone', 'timeline_milestone_nonce'); ?>
                
                <div class="form-group">
                    <label for="title">Título del Hito *</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="date">Fecha *</label>
                    <input type="date" id="date" name="date" required>
                </div>
                
                <div class="form-group">
                    <label for="status">Estado *</label>
                    <select id="status" name="status" required>
                        <option value="pendiente">Pendiente</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="finalizado">Finalizado</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Imágenes (5-7 recomendadas)</label>
                    <input type="file" id="milestone_images" accept="image/*" multiple style="display: none;">
                    <div class="images-upload-area" onclick="document.getElementById('milestone_images').click()">
                        <p>Haz clic para seleccionar imágenes</p>
                        <small style="color: rgba(255,255,255,0.4);">Puedes seleccionar varias a la vez</small>
                    </div>
                    <div id="preview-images" class="preview-images"></div>
                    <input type="hidden" id="images_data" name="images_data" value="">
                </div>
                
                <button type="submit" class="btn-submit">Guardar Hito</button>
            </form>
        </div>
    </div>
    
    <script>
        let imagesArray = [];
        
        function openMilestoneModal() {
            document.getElementById('milestoneModal').classList.add('active');
            document.getElementById('modal-title').textContent = 'Nuevo Hito';
            document.getElementById('milestone-form').reset();
            document.getElementById('milestone_id').value = '';
            imagesArray = [];
            document.getElementById('preview-images').innerHTML = '';
        }
        
        function closeMilestoneModal() {
            document.getElementById('milestoneModal').classList.remove('active');
        }
        
        function editMilestone(id) {
            // Implementar edición
            alert('Editar hito ' + id + ' - Por implementar');
        }
        
        function deleteMilestone(id) {
            if (confirm('¿Estás seguro de eliminar este hito?')) {
                window.location.href = '<?php echo admin_url('admin-post.php'); ?>?action=timeline_delete_milestone&milestone_id=' + id + '&project_id=<?php echo $project_id; ?>&_wpnonce=<?php echo wp_create_nonce('delete_milestone'); ?>';
            }
        }
        
        // Manejo de imágenes
        document.getElementById('milestone_images').addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            
            files.forEach(file => {
                if (file.type.match('image.*')) {
                    const reader = new FileReader();
                    
                    reader.onload = function(event) {
                        const base64 = event.target.result;
                        imagesArray.push(base64);
                        renderPreview();
                        updateImagesData();
                    };
                    
                    reader.readAsDataURL(file);
                }
            });
        });
        
        function renderPreview() {
            const container = document.getElementById('preview-images');
            container.innerHTML = '';
            
            imagesArray.forEach((img, index) => {
                const div = document.createElement('div');
                div.className = 'preview-image-item';
                div.innerHTML = `
                    <img src="${img}" alt="Preview">
                    <button type="button" class="remove-preview" onclick="removeImage(${index})">×</button>
                `;
                container.appendChild(div);
            });
        }
        
        function removeImage(index) {
            imagesArray.splice(index, 1);
            renderPreview();
            updateImagesData();
        }
        
        function updateImagesData() {
            document.getElementById('images_data').value = JSON.stringify(imagesArray);
        }
        
        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('milestoneModal');
            if (event.target == modal) {
                closeMilestoneModal();
            }
        }
    </script>
</body>
</html>
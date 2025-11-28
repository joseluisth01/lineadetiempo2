<?php

/**
 * Template: Vista del Timeline del Proyecto (Cliente)
 * Archivo: templates/project-timeline.php
 */

$project_id = get_query_var('timeline_id');
$projects_class = Timeline_Projects::get_instance();
$milestones_class = Timeline_Milestones::get_instance();

$project = $projects_class->get_project($project_id);
if (!$project) {
    wp_redirect(home_url('/timeline-mis-proyectos'));
    exit;
}

// Verificar que el cliente tiene acceso
$current_user = $GLOBALS['timeline_plugin']->get_current_user();
if ($current_user->role === 'cliente') {
    $client_projects = $projects_class->get_client_projects($current_user->id);
    $has_access = false;
    foreach ($client_projects as $cp) {
        if ($cp->id == $project_id) {
            $has_access = true;
            break;
        }
    }
    if (!$has_access) {
        wp_redirect(home_url('/timeline-mis-proyectos'));
        exit;
    }
}

$milestones = $milestones_class->get_project_milestones_with_images($project_id);

// Calcular duración del proyecto
$start = new DateTime($project->start_date);
$end = new DateTime($project->end_date);
$actual_end = $project->actual_end_date ? new DateTime($project->actual_end_date) : $end;
$total_days = $start->diff($actual_end)->days;
$is_extended = $project->actual_end_date && $actual_end > $end;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($project->name); ?> - Timeline</title>
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

        .btn-back {
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

        .btn-back:hover {
            background: #000;
            color: #fff;
        }

        .header-section {
            background: #0a0a0a;
            color: #ffffff;
            padding: 60px 40px;
            text-align: center;
        }

        .header-section h1 {
            font-size: 48px;
            font-weight: 400;
            margin-bottom: 15px;
        }

        .header-section p {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.6);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 80px 40px;
        }

        .timeline-wrapper {
            position: relative;
            margin: 80px 0;
        }

        /* Barra de timeline horizontal */
        .timeline-bar-container {
            position: relative;
            height: 120px;
            margin-bottom: 50px;
        }

        .timeline-bar {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(to right, #FDC425 0%, #FDC425 <?php echo $is_extended ? (($end->diff($start)->days / $total_days) * 100) : 100; ?>%, #ff6b6b <?php echo $is_extended ? (($end->diff($start)->days / $total_days) * 100) : 100; ?>%, #ff6b6b 100%);
            transform: translateY(-50%);
        }

        /* Fechas en la barra */
        .timeline-date {
            position: absolute;
            top: -40px;
            transform: translateX(-50%);
            text-align: center;
        }

        .timeline-date-marker {
            width: 16px;
            height: 16px;
            background: #0a0a0a;
            border: 3px solid #FDC425;
            border-radius: 50%;
            margin: 0 auto 10px;
        }

        .timeline-date-text {
            font-size: 11px;
            font-weight: 600;
            color: #333;
            white-space: nowrap;
        }

        /* Hitos en la barra */
        .milestone-marker {
            position: absolute;
            top: 50%;
            transform: translate(-50%, -50%);
            cursor: pointer;
            transition: all 0.3s;
            z-index: 10;
        }

        .milestone-marker:hover {
            transform: translate(-50%, -50%) scale(1.2);
        }

        .milestone-marker-dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 3px solid #0a0a0a;
            background: #fff;
        }

        .milestone-marker.status-pendiente .milestone-marker-dot {
            background: #EDEDED;
        }

        .milestone-marker.status-en_proceso .milestone-marker-dot {
            background: #FDC425;
        }

        .milestone-marker.status-finalizado .milestone-marker-dot {
            background: #FFDE88;
        }

        /* LÍNEA VERTICAL DEL TIMELINE */
        .vertical-timeline {
            position: relative;
            margin-top: 60px;
        }

        .vertical-timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 2px;
            background: black;
            transform: translateX(-50%);
            z-index: 0;
        }

        /* Tarjetas de hitos con línea vertical */
        .milestone-card {
            position: relative;
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s forwards;
        }

        /* Alternar posición - Hitos impares a la DERECHA */
        .milestone-card:nth-child(odd) {
            justify-content: flex-end;
            padding-left: calc(50% + 40px);
        }

        /* Alternar posición - Hitos pares a la IZQUIERDA */
        .milestone-card:nth-child(even) {
            justify-content: flex-start;
            padding-right: calc(50% + 40px);
        }

        /* Contenedor interno del hito */
        .milestone-inner {
            display: flex;
            align-items: center;
            gap: 30px;
            background: #FFF9E6;
            border: 0px;
            border-radius: 15px;
            padding: 15px;
            max-width: 650px;
            width: 100%;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .status-finalizado .milestone-inner{
            background-color: #FFDE88;
        }

        .milestone-card:nth-child(even) .milestone-inner {
            flex-direction: row-reverse;
        }

        .milestone-card:nth-child(odd) .milestone-card-content {
            text-align: left;
        }

        .milestone-card:nth-child(even) .milestone-card-content {
            text-align: right;
        }

        /* Punto de conexión en la línea central */
        .milestone-card::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 20%;
            transform: translate(-50%, -50%);
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: black;
            box-shadow: 0 0 0 2px black;
            z-index: 5;
        }

        .status-pendiente .milestone-inner{
            background-color: #EDEDED;
        }
        .status-en_proceso .milestone-inner{
            background-color: #FDC425;
        }

        .milestone-card.status-pendiente::after {
            background: #EDEDED;
        }

        .milestone-card.status-en_proceso::after {
            background: #FDC425;
        }

        .milestone-card.status-finalizado::after {
            background: #FFDE88;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .milestone-card-image {
            position: relative;
            width: 180px;
            height: 120px;
            flex-shrink: 0;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
            transition: all 0.3s;
        }

        .milestone-card-image:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
        }

        .milestone-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .milestone-card-btn {
            position: absolute;
            bottom: 0px;
            left: 50%;
            transform: translateX(-50%);
            background: #000;
            color: #fff;
            padding: 10px 20px;
            font-size: 10px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            border: none;
            cursor: pointer;
            white-space: nowrap;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
        }


        .milestone-card-content {
            flex: 1;
            padding: 0;
        }

        .milestone-card:nth-child(even) .milestone-card-content {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-content: flex-end;
            flex-wrap: wrap;
        }

        .milestone-card:nth-child(even) .milestone-card-content .milestone-card-date{
            order: 2;
        }
        .milestone-card:nth-child(even) .milestone-card-content .milestone-card-date{
            order: 1;
        }

        .milestone-card-date {
            font-size: 12px;
            color: black;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .milestone-card-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .milestone-card-title .highlight {
            font-weight: 400;
        }

        .milestone-card-description {
            font-size: 14px;
            line-height: 1.6;
            color: black;
            margin-bottom: 0;
        }

        .milestone-card-description strong {
            font-weight: 700;
            color: #000;
        }

        /* Modal de hito */
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            overflow-y: auto;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: #ffffff;
            width: 90%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            margin: 40px 0;
            border-radius: 10px;
        }

        .modal-close {
            position: absolute;
            top: 30px;
            right: 30px;
            font-size: 40px;
            color: #000;
            cursor: pointer;
            z-index: 10;
            background: rgba(255, 255, 255, 0.9);
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .modal-close:hover {
            background: #000;
            color: #fff;
        }

        .modal-carousel {
            position: relative;
            width: 100%;
            height: 500px;
            overflow: hidden;
            background: #f5f5f5;
        }

        .carousel-slide {
            display: none;
            width: 100%;
            height: 100%;
        }

        .carousel-slide.active {
            display: block;
        }

        .carousel-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .carousel-prev,
        .carousel-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            border: none;
            width: 60px;
            height: 60px;
            font-size: 30px;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 10;
        }

        .carousel-prev:hover,
        .carousel-next:hover {
            background: #000;
        }

        .carousel-prev {
            left: 20px;
        }

        .carousel-next {
            right: 20px;
        }

        .carousel-indicators {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        }

        .carousel-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s;
        }

        .carousel-indicator.active {
            background: #FDC425;
            transform: scale(1.2);
        }

        .modal-info {
            padding: 50px;
        }

        .modal-date {
            font-size: 14px;
            color: #999;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .modal-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.3;
        }

        .modal-status {
            display: inline-block;
            padding: 8px 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 30px;
            border-radius: 5px;
        }



        .modal-description {
            font-size: 16px;
            line-height: 1.8;
            color: #666;
        }

        .modal-description strong {
            font-weight: 700;
            color: #000;
        }

        .milestone-nav-arrows {
            display: flex;
            justify-content: space-between;
            padding: 30px 50px;
            border-top: 1px solid #e0e0e0;
        }

        .milestone-nav-btn {
            background: #000;
            color: #fff;
            border: none;
            padding: 12px 30px;
            font-size: 12px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }

        .milestone-nav-btn:hover:not(:disabled) {
            background: #FDC425;
            color: #000;
        }

        .milestone-nav-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .vertical-timeline::before {
                left: 30px;
            }

            .milestone-card {
                padding-left: 60px !important;
                padding-right: 0 !important;
                justify-content: flex-start !important;
            }

            .milestone-card::after {
                left: 30px;
            }

            .milestone-inner {
                flex-direction: column !important;
                max-width: 100%;
            }

            .milestone-card:nth-child(odd) .milestone-card-content,
            .milestone-card:nth-child(even) .milestone-card-content {
                text-align: center;
                width: 100%;
            }

            .milestone-card-image {
                width: 100%;
                height: 220px;
            }

            .modal-content {
                width: 95%;
            }

            .modal-info {
                padding: 30px;
            }

            .modal-carousel {
                height: 300px;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="navbar-brand">BeBuilt</div>
        <a href="<?php echo home_url('/timeline-mis-proyectos'); ?>" class="btn-back">← Mis Proyectos</a>
    </nav>


    <div class="container">
        <!-- Barra de Timeline horizontal (superior) -->
        <div class="timeline-bar-container">
            <div class="timeline-bar"></div>

            <!-- Fecha inicio -->
            <div class="timeline-date" style="left: 0;">
                <div class="timeline-date-marker"></div>
                <div class="timeline-date-text"><?php echo $start->format('d/m/Y'); ?></div>
            </div>

            <!-- Fecha fin prevista -->
            <div class="timeline-date" style="left: <?php echo $is_extended ? (($end->diff($start)->days / $total_days) * 100) : 100; ?>%;">
                <div class="timeline-date-marker" style="border-color: <?php echo $is_extended ? '#ff6b6b' : '#FDC425'; ?>;"></div>
                <div class="timeline-date-text"><?php echo $end->format('d/m/Y'); ?></div>
            </div>

            <?php if ($is_extended): ?>
                <!-- Fecha fin real (extendida) -->
                <div class="timeline-date" style="left: 100%;">
                    <div class="timeline-date-marker" style="border-color: #ff6b6b;"></div>
                    <div class="timeline-date-text"><?php echo $actual_end->format('d/m/Y'); ?></div>
                </div>
            <?php endif; ?>

            <!-- Marcadores de hitos en la barra -->
            <?php foreach ($milestones as $index => $milestone): ?>
                <?php
                $milestone_date = new DateTime($milestone->date);
                $days_from_start = $start->diff($milestone_date)->days;
                $position = ($days_from_start / $total_days) * 100;
                $position = max(0, min(100, $position));
                ?>
                <div class="milestone-marker status-<?php echo esc_attr($milestone->status); ?>"
                    style="left: <?php echo $position; ?>%;"
                    onclick="openMilestoneModal(<?php echo $index; ?>)">
                    <div class="milestone-marker-dot"></div>
                </div>
            <?php endforeach; ?>
        </div>

        <h1 style="text-align:center"><?php echo esc_html($project->name); ?></h1><br>
        <p style="text-align:center"><?php echo esc_html($project->description); ?></p><br>

        <!-- Timeline vertical con hitos -->
        <div class="vertical-timeline">
            <?php foreach ($milestones as $index => $milestone): ?>
                <?php
                $date = new DateTime($milestone->date);
                $first_image = !empty($milestone->images) ? $milestone->images[0]->image_url : 'https://via.placeholder.com/200x200/cccccc/666666?text=Sin+Imagen';
                ?>
                <div class="milestone-card status-<?php echo esc_attr($milestone->status); ?>"
                    style="animation-delay: <?php echo $index * 0.15; ?>s;">
                    <div class="milestone-inner">

                        <div class="milestone-card-content">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div class="milestone-card-date"><?php echo $date->format('d/m/Y'); ?></div>
                                <h2 class="milestone-card-title">
                                    <span class="highlight"><?php echo esc_html(explode(' ', $milestone->title)[0]); ?></span>
                                    <?php echo esc_html(implode(' ', array_slice(explode(' ', $milestone->title), 1))); ?>
                                </h2>
                            </div>

                            <div class="milestone-card-description">
                                <?php echo nl2br(esc_html(substr($milestone->description, 0, 150))); ?>
                                <?php if (strlen($milestone->description) > 150): ?>...<?php endif; ?>
                            </div>
                        </div>

                        <div class="milestone-card-image">
                            <img src="<?php echo esc_url($first_image); ?>" alt="<?php echo esc_attr($milestone->title); ?>">
                            <button class="milestone-card-btn" onclick="openMilestoneModal(<?php echo $index; ?>)">
                                + Información
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($milestones) === 0): ?>
            <div style="text-align: center; padding: 80px 20px; color: #999;">
                <p>No hay hitos disponibles en este proyecto todavía</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal de hito -->
    <div id="milestoneModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeMilestoneModal()">&times;</span>

            <div class="modal-carousel" id="modal-carousel">
                <!-- Las imágenes se cargarán dinámicamente -->
            </div>

            <div class="modal-info">
                <div class="modal-date" id="modal-date"></div>
                <h2 class="modal-title" id="modal-title"></h2>
                <span class="modal-status" id="modal-status"></span>
                <div class="modal-description" id="modal-description"></div>
            </div>

            <div class="milestone-nav-arrows">
                <button class="milestone-nav-btn" id="prev-milestone-btn" onclick="navigateMilestone(-1)">
                    ← Hito Anterior
                </button>
                <button class="milestone-nav-btn" id="next-milestone-btn" onclick="navigateMilestone(1)">
                    Siguiente Hito →
                </button>
            </div>
        </div>
    </div>

    <script>
        const milestones = <?php echo json_encode($milestones); ?>;
        let currentMilestoneIndex = 0;
        let currentSlide = 0;

        function openMilestoneModal(index) {
            currentMilestoneIndex = index;
            currentSlide = 0;
            const milestone = milestones[index];

            document.getElementById('milestoneModal').classList.add('active');

            // Cargar datos
            const date = new Date(milestone.date);
            document.getElementById('modal-date').textContent = date.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            document.getElementById('modal-title').textContent = milestone.title;
            document.getElementById('modal-description').innerHTML = milestone.description.replace(/\n/g, '<br>');

            // Estado
            const statusElement = document.getElementById('modal-status');
            const statusLabels = {
                'pendiente': 'Pendiente',
                'en_proceso': 'En Proceso',
                'finalizado': 'Finalizado'
            };
            statusElement.textContent = statusLabels[milestone.status] || milestone.status;
            statusElement.className = 'modal-status status-' + milestone.status;

            // Cargar carrusel
            loadCarousel(milestone);

            // Actualizar botones de navegación
            updateNavButtons();

            // Prevenir scroll del body
            document.body.style.overflow = 'hidden';
        }

        function closeMilestoneModal() {
            document.getElementById('milestoneModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function loadCarousel(milestone) {
            const carousel = document.getElementById('modal-carousel');
            carousel.innerHTML = '';

            const images = milestone.images && milestone.images.length > 0 ?
                milestone.images :
                [{
                    image_url: 'https://via.placeholder.com/900x500/cccccc/666666?text=Sin+Imagenes'
                }];

            images.forEach((img, index) => {
                const slide = document.createElement('div');
                slide.className = 'carousel-slide' + (index === 0 ? ' active' : '');
                slide.innerHTML = `<img src="${img.image_url}" alt="Imagen ${index + 1}">`;
                carousel.appendChild(slide);
            });

            // Controles del carrusel
            if (images.length > 1) {
                const prevBtn = document.createElement('button');
                prevBtn.className = 'carousel-prev';
                prevBtn.innerHTML = '‹';
                prevBtn.onclick = () => changeSlide(-1);
                carousel.appendChild(prevBtn);

                const nextBtn = document.createElement('button');
                nextBtn.className = 'carousel-next';
                nextBtn.innerHTML = '›';
                nextBtn.onclick = () => changeSlide(1);
                carousel.appendChild(nextBtn);

                // Indicadores
                const indicators = document.createElement('div');
                indicators.className = 'carousel-indicators';
                images.forEach((_, index) => {
                    const indicator = document.createElement('div');
                    indicator.className = 'carousel-indicator' + (index === 0 ? ' active' : '');
                    indicator.onclick = () => goToSlide(index);
                    indicators.appendChild(indicator);
                });
                carousel.appendChild(indicators);
            }
        }

        function changeSlide(direction) {
            const slides = document.querySelectorAll('.carousel-slide');
            const indicators = document.querySelectorAll('.carousel-indicator');

            slides[currentSlide].classList.remove('active');
            if (indicators.length) indicators[currentSlide].classList.remove('active');

            currentSlide = (currentSlide + direction + slides.length) % slides.length;

            slides[currentSlide].classList.add('active');
            if (indicators.length) indicators[currentSlide].classList.add('active');
        }

        function goToSlide(index) {
            const slides = document.querySelectorAll('.carousel-slide');
            const indicators = document.querySelectorAll('.carousel-indicator');

            slides[currentSlide].classList.remove('active');
            if (indicators.length) indicators[currentSlide].classList.remove('active');

            currentSlide = index;

            slides[currentSlide].classList.add('active');
            if (indicators.length) indicators[currentSlide].classList.add('active');
        }

        function navigateMilestone(direction) {
            closeMilestoneModal();
            setTimeout(() => {
                const newIndex = currentMilestoneIndex + direction;
                if (newIndex >= 0 && newIndex < milestones.length) {
                    openMilestoneModal(newIndex);
                }
            }, 300);
        }

        function updateNavButtons() {
            document.getElementById('prev-milestone-btn').disabled = currentMilestoneIndex === 0;
            document.getElementById('next-milestone-btn').disabled = currentMilestoneIndex === milestones.length - 1;
        }

        // Cerrar modal con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMilestoneModal();
            }
        });

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
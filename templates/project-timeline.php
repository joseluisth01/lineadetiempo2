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
            max-width: 80%;
            margin: 0 auto;
            padding: 80px 40px;
        }

        .timeline-wrapper {
            position: relative;
            margin: 80px 0;
        }

        /* ===== NUEVA BARRA DE TIMELINE ===== */
        .timeline-bar-container {
            width: 100%;
            padding: 20px 0;
            background: #f2f2f2;
            position: relative;
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        /* Línea horizontal */
        .timeline-bar {
            position: absolute;
            left: 0;
            right: 0;
            top: 40px;
            height: 3px;
            background: #bfbfbf;
            z-index: 1;
        }

        /* Contenido scrollable en horizontal */
        .timeline-bar-inner {
            display: flex;
            width: 100%;
            justify-content: space-between;
            padding: 0 40px;
            position: relative;
            z-index: 2;
        }

        /* Cada hito superior */
        .timeline-top-item {
            text-align: center;
            flex: 1;
            position: relative;
        }

        /* Punto */
        .timeline-top-point {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: white;
            border: 3px solid #000;
            margin: 0 auto;
            margin-bottom: 6px;
        }

        /* Fecha */
        .timeline-top-date {
            font-size: 12px;
            color: #555;
            margin-top: 4px;
        }

        /* Flechas (opcionales) */
        .timeline-nav-arrow {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: black;
            color: white;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: absolute;
            top: 28px;
            z-index: 3;
        }

        .timeline-nav-left {
            left: 5px;
        }

        .timeline-nav-right {
            right: 5px;
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
            padding-left: calc(50% + 25px);
        }

        /* Alternar posición - Hitos pares a la IZQUIERDA */
        .milestone-card:nth-child(even) {
            justify-content: flex-end;
            padding-right: calc(50% + 25px);
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
            z-index: 999999;
        }

        .status-finalizado .milestone-inner {
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

        .milestone-card::after {
            content: '';
            position: absolute;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #FDC425;
            border: 3px solid black;
            z-index: 6;
        }

        /* Impares (derecha) → punto se coloca centrado respecto a la línea */
        .milestone-card:nth-child(odd)::after {
            left: 50%;
        }

        /* Pares (izquierda) */
        .milestone-card:nth-child(even)::after {
            left: 50%;
        }

        /* ===== CONECTOR: LÍNEA QUE SALE DEL PUNTO HACIA LA TARJETA ===== */
        .milestone-card::before {
            content: '';
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            /* longitud del conector */
            height: 2px;
            background: black;
            z-index: 4;
        }

        /* Impares → tarjeta a la DERECHA → línea a la izquierda */
        .milestone-card:nth-child(odd)::before {
            left: 50%;
        }

        /* Pares → tarjeta a la IZQUIERDA → línea a la derecha */
        .milestone-card:nth-child(even)::before {
            right: 50%;
        }


        .status-pendiente .milestone-inner {
            background-color: #EDEDED;
        }

        .status-en_proceso .milestone-inner {
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

        .milestone-card-image:not(:has(button:disabled)):hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
        }

        .milestone-card-image:has(button:disabled) {
            opacity: 0.7;
            cursor: default;
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

        .milestone-card:nth-child(even) .milestone-card-content .milestone-card-date {
            order: 2;
        }

        .milestone-card:nth-child(even) .milestone-card-content .milestone-card-date {
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
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            margin: 40px 0;
            border-radius: 40px;
        }

        /* NUEVAS REGLAS - Añadir después de .modal-content */
        .modal-content.status-en_proceso {
            background: #FDC425;
        }

        .modal-content.status-finalizado {
            background: #FFDE88;
        }

        /* ===== MODIFICACIÓN: NUEVA BARRA SUPERIOR CON FLECHAS DEL CARRUSEL ===== */
        .modal-top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 30px;
            gap: 20px;
        }

        .modal-top-left {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .modal-top-right {
            display: flex;
            gap: 10px;
            align-items: center;
            width: 46%;
            justify-content: space-between;
        }

        .modal-close {
            border: none;
            background: none;
            cursor: pointer;
        }

        .modal-close:hover {
            opacity: 0.8;
        }

        /* ===== FLECHAS DEL CARRUSEL EN LA BARRA SUPERIOR ===== */
        .carousel-nav-btn{
            border: none;
            background: none;
            cursor: pointer;
        }

        .carousel-nav-btn:hover:not(:disabled) {
            opacity: 0.8;
        }

        .carousel-nav-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }


        .modal-carousel {
            position: relative;
            width: 48%;
            height: 500px;
            overflow: hidden;
            padding: 30px;
            padding-top: 0px;
            border-radius: 20px;
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
            border-radius: 20px;
        }

        /* ===== OCULTAR FLECHAS ANTIGUAS DEL CARRUSEL ===== */
        .carousel-prev,
        .carousel-next {
            display: none !important;
        }

        .carousel-indicators {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
            display: none;
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
            padding: 30px;
            padding-top: 0px;
            width: 50%;
        }

        .modal-date {
            font-size: 14px;
            color: black;
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
            border-radius: 5px;
            background-color: black;
            color: #FDC425;
            border-radius: 10px;
        }



        .modal-description {
            font-size: 16px;
            line-height: 1.8;
            color: black;
        }

        .modal-description strong {
            font-weight: 700;
            color: #000;
        }

        /* ===== BOTONES DE NAVEGACIÓN ENTRE HITOS (ABAJO COMO ANTES) ===== */
        .milestone-nav-arrows {
            display: flex;
            justify-content: space-between;
            padding: 0px;
            bottom: 30px;
            position: absolute;
            width: 50%;
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
            border-radius: 10px;
        }

        .milestone-nav-btn:hover:not(:disabled) {
            opacity: 0.8;
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

            

            .modal-top-bar {
                flex-direction: column;
                align-items: stretch;
            }

            
            .milestone-nav-arrows {
                width: 100%;
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="navbar-brand">BeBuilt</div>
        <a href="<?php echo home_url('/timeline-mis-proyectos'); ?>" class="btn-back">← Mis Proyectos</a>
    </nav>

<div class="timeline-bar-container">

            <!-- Flechas laterales (opcionales) -->
            <div class="timeline-nav-arrow timeline-nav-left">‹</div>
            <div class="timeline-nav-arrow timeline-nav-right">›</div>

            <div class="timeline-bar"></div>

            <div class="timeline-bar-inner">
                <?php foreach ($milestones as $milestone): ?>
                    <?php $date = new DateTime($milestone->date); ?>
                    <div class="timeline-top-item">

                        <div class="timeline-top-point status-<?php echo esc_attr($milestone->status); ?>"></div>

                        <div class="timeline-top-date">
                            <?php echo $date->format('d/m/Y'); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <div class="container">
        <!-- Barra de Timeline horizontal (superior) -->
        


        <h1 style="text-align:center"><?php echo esc_html($project->name); ?></h1><br>
        <p style="text-align:center"><?php echo esc_html($project->description); ?></p><br>

        <!-- Timeline vertical con hitos -->
        <div class="vertical-timeline">
            <?php foreach ($milestones as $index => $milestone): ?>
                <?php
                $date = new DateTime($milestone->date);

                // Asegurar que siempre haya una imagen
                if (!empty($milestone->images) && isset($milestone->images[0]) && !empty($milestone->images[0]->image_url)) {
                    $first_image = $milestone->images[0]->image_url;
                } else {
                    $first_image = 'https://www.bebuilt.es/wp-content/uploads/2023/08/cropped-favicon.png';
                }
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
                            <?php if ($milestone->status !== 'pendiente'): ?>
                                <button class="milestone-card-btn" onclick="openMilestoneModal(<?php echo $index; ?>)">
                                    + Información
                                </button>
                            <?php else: ?>
                                <button class="milestone-card-btn" style="background: #666; cursor: not-allowed;" disabled>
                                    Pendiente
                                </button>
                            <?php endif; ?>
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

            <!-- ===== BARRA SUPERIOR CON FLECHAS DEL CARRUSEL ===== -->
            <div class="modal-top-bar">
                <div class="modal-top-left">
                    <div class="modal-date" id="modal-date"></div>
                    <span class="modal-status" id="modal-status"></span>
                </div>

                <div class="modal-top-right">
                    <!-- Flechas del carrusel de imágenes -->
                    <div style="display:flex; gap:10px; justify-content: space-between;">
                        <button class="carousel-nav-btn" id="carousel-prev-top" onclick="changeSlide(-1)" title="Imagen anterior">
                            <img src="https://www.bebuilt.es/wp-content/uploads/2025/12/Vector-18.svg" alt="">
                        </button>
                        <button class="carousel-nav-btn" id="carousel-next-top" onclick="changeSlide(1)" title="Imagen siguiente">
                            <img src="https://www.bebuilt.es/wp-content/uploads/2025/12/Vector-19.svg" alt="">
                        </button>
                    </div>

                    <!-- Botón cerrar -->
                    <button class="modal-close" onclick="closeMilestoneModal()"><img src="https://www.bebuilt.es/wp-content/uploads/2025/12/Vector-20.svg" alt=""></button>
                </div>
            </div>

            <div style="display: flex; gap: 15px; justify-content:space-between;">
                <div class="modal-info">
                    <h2 class="modal-title" id="modal-title"></h2>
                    <div class="modal-description" id="modal-description"></div>

                    <!-- Botones de navegación entre hitos (abajo como antes) -->
                    <div class="milestone-nav-arrows">
                        <button class="milestone-nav-btn" id="prev-milestone-btn" onclick="navigateMilestone(-1)">
                            < Anterior
                                </button>
                                <button class="milestone-nav-btn" id="next-milestone-btn" onclick="navigateMilestone(1)">
                                    Siguiente >
                                </button>
                    </div>
                </div>

                <div class="modal-carousel" id="modal-carousel">
                    <!-- Las imágenes se cargarán dinámicamente -->
                </div>
            </div>
        </div>
    </div>

    <script>
        const milestones = <?php echo json_encode($milestones); ?>;
        let currentMilestoneIndex = 0;
        let currentSlide = 0;
        let totalSlides = 0;

        function openMilestoneModal(index) {
            currentMilestoneIndex = index;
            currentSlide = 0;
            const milestone = milestones[index];

            const modal = document.getElementById('milestoneModal');
            const modalContent = modal.querySelector('.modal-content');

            modal.classList.add('active');

            // Añadir clase de estado al modal-content
            modalContent.className = 'modal-content status-' + milestone.status;

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

            // Actualizar botones de navegación entre hitos
            updateMilestoneNavButtons();

            // Actualizar botones del carrusel
            updateCarouselNavButtons();

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
                milestone.images : [{
                    image_url: 'https://via.placeholder.com/900x500/cccccc/666666?text=Sin+Imagenes'
                }];

            totalSlides = images.length;

            images.forEach((img, index) => {
                const slide = document.createElement('div');
                slide.className = 'carousel-slide' + (index === 0 ? ' active' : '');
                slide.innerHTML = `<img src="${img.image_url}" alt="Imagen ${index + 1}">`;
                carousel.appendChild(slide);
            });

            // Indicadores
            if (images.length > 1) {
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

            // Actualizar estado de los botones del carrusel
            updateCarouselNavButtons();
        }

        function goToSlide(index) {
            const slides = document.querySelectorAll('.carousel-slide');
            const indicators = document.querySelectorAll('.carousel-indicator');

            slides[currentSlide].classList.remove('active');
            if (indicators.length) indicators[currentSlide].classList.remove('active');

            currentSlide = index;

            slides[currentSlide].classList.add('active');
            if (indicators.length) indicators[currentSlide].classList.add('active');

            // Actualizar estado de los botones del carrusel
            updateCarouselNavButtons();
        }

        // ===== ACTUALIZAR BOTONES DEL CARRUSEL =====
        function updateCarouselNavButtons() {
            const prevBtn = document.getElementById('carousel-prev-top');
            const nextBtn = document.getElementById('carousel-next-top');

            // Si solo hay una imagen, ocultar los botones
            if (totalSlides <= 1) {
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
            } else {
                prevBtn.style.display = 'flex';
                nextBtn.style.display = 'flex';

                // Deshabilitar si estamos en los extremos (opcional)
                // Si prefieres que haga loop, quita estas líneas
                prevBtn.disabled = false;
                nextBtn.disabled = false;
            }
        }

        // ===== NAVEGACIÓN ENTRE HITOS (SALTA PENDIENTES) =====
        function navigateMilestone(direction) {
            closeMilestoneModal();
            setTimeout(() => {
                let newIndex = currentMilestoneIndex + direction;

                // Buscar el siguiente hito que NO sea pendiente
                while (newIndex >= 0 && newIndex < milestones.length) {
                    if (milestones[newIndex].status !== 'pendiente') {
                        openMilestoneModal(newIndex);
                        return;
                    }
                    newIndex += direction;
                }

                // Si no encuentra ninguno válido, no hace nada
            }, 300);
        }

        function updateMilestoneNavButtons() {
            // Buscar si hay un hito anterior válido (no pendiente)
            let hasPrevious = false;
            for (let i = currentMilestoneIndex - 1; i >= 0; i--) {
                if (milestones[i].status !== 'pendiente') {
                    hasPrevious = true;
                    break;
                }
            }

            // Buscar si hay un hito siguiente válido (no pendiente)
            let hasNext = false;
            for (let i = currentMilestoneIndex + 1; i < milestones.length; i++) {
                if (milestones[i].status !== 'pendiente') {
                    hasNext = true;
                    break;
                }
            }

            // Actualizar botones de navegación entre hitos
            document.getElementById('prev-milestone-btn').disabled = !hasPrevious;
            document.getElementById('next-milestone-btn').disabled = !hasNext;
        }

        // Cerrar modal con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMilestoneModal();
            }
            // Navegar por el carrusel con flechas del teclado
            if (document.getElementById('milestoneModal').classList.contains('active')) {
                if (e.key === 'ArrowLeft') {
                    changeSlide(-1);
                } else if (e.key === 'ArrowRight') {
                    changeSlide(1);
                }
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
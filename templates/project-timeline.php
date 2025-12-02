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

        /* ===== BARRA DE TIMELINE SUPERIOR ===== */
        .timeline-bar-container {
            width: 100%;
            padding: 0px;
            background: #f2f2f2;
            position: sticky;
            top: 0;
            z-index: 100;
            overflow-x: auto;
            overflow-y: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            scrollbar-width: thin;
            scrollbar-color: #bfbfbf #f2f2f2;
        }

        .timeline-bar-container::-webkit-scrollbar {
            height: 8px;
        }

        .timeline-bar-container::-webkit-scrollbar-track {
            background: #f2f2f2;
        }

        .timeline-bar-container::-webkit-scrollbar-thumb {
            background: #bfbfbf;
            border-radius: 4px;
        }

        .timeline-bar-container::-webkit-scrollbar-thumb:hover {
            background: #999;
        }

        /* Contenido scrollable en horizontal */
        .timeline-bar-inner {
            display: flex;
            gap: 0px;
            padding: 0 60px;
            position: relative;
            z-index: 2;
            min-width: max-content;
        }

        /* Línea horizontal - AHORA DENTRO DE timeline-bar-inner */
        .timeline-bar-inner::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            width: 100%;
            top: 36%;
            transform: translateY(-50%);
            height: 2px;
            background: black;
            z-index: 1;
            pointer-events: none;
        }

        /* Cada hito superior */
        .timeline-top-item {
            text-align: center;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 150px;
            height: 70px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 5px;
        }

        /* CAMBIO 2: Punto con background sólido para ocultar la línea */
        .timeline-top-point {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            border: 2px solid #000;
            margin: 0 auto;
            transition: all 0.3s ease;
            /* Añadir z-index superior y background */
            position: relative;
            z-index: 3;
            background: #f2f2f2;
        }

        /* Colores según estado - ahora con background visible */
        .timeline-top-item.status-pendiente .timeline-top-point {
            background: #EDEDED;
        }

        .timeline-top-item.status-en_proceso .timeline-top-point {
            background: #FDC425;
        }

        .timeline-top-item.status-finalizado .timeline-top-point {
            background: #FFDE88;
        }

        /* Fecha con colores según estado */
        .timeline-top-date {
            font-size: 13px;
            font-weight: 600;
            margin-top: 0px;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .timeline-top-item.status-pendiente {
            background: #EDEDED;
        }

        .timeline-top-item.status-en_proceso {
            background: #FDC425;
        }

        .timeline-top-item.status-finalizado {
            background: #FFDE88;
        }

        .timeline-top-item:hover .timeline-top-date {
            font-weight: 700;
        }

        .timeline-top-item.active .timeline-top-point {
            background-color: #000;
        }

        .timeline-top-item.active .timeline-top-date {
            font-weight: 700;
            font-size: 14px;
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
            text-align: justify;
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

        .modal-content.status-en_proceso {
            background: #FDC425;
        }

        .modal-content.status-finalizado {
            background: #FFDE88;
        }

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

        .carousel-nav-btn {
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

        .milestone-nav-arrows {
            display: flex;
            justify-content: space-between;
            padding: 0px;
            bottom: 30px;
            position: absolute;
            width: 50%;
        }

        .milestone-card.active .milestone-inner {
            border: 2px solid black;
        }

        .milestone-card.active::after {
            background: black !important;
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



            .timeline-top-item {
                min-width: 80px;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="navbar-brand">BeBuilt</div>
        <a href="<?php echo home_url('/timeline-mis-proyectos'); ?>" class="btn-back">← Mis Proyectos</a>
    </nav>

    <div class="timeline-bar-container" id="timelineBarContainer">
        <div class="timeline-bar"></div>
        <div class="timeline-bar-inner" id="timelineBarInner">
            <?php foreach ($milestones as $index => $milestone): ?>
                <?php
                $date = new DateTime($milestone->date);
                $status_class = 'status-' . esc_attr($milestone->status);
                ?>
                <div class="timeline-top-item <?php echo $status_class; ?>"
                    data-milestone-index="<?php echo $index; ?>"
                    onclick="scrollToMilestone(<?php echo $index; ?>)">
                    <div class="timeline-top-point"></div>
                    <div class="timeline-top-date">
                        <?php echo $date->format('d/m/Y'); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="container">
        <h1 style="text-align:center"><?php echo esc_html($project->name); ?></h1><br>
        <p style="text-align:center"><?php echo esc_html($project->description); ?></p><br>

        <div class="vertical-timeline" id="verticalTimeline">
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
                    id="milestone-<?php echo $index; ?>"
                    data-milestone-index="<?php echo $index; ?>"
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


<?php
$documents_class = Timeline_Documents::get_instance();
$documents = $documents_class->get_project_documents($project_id);

if (count($documents) > 0):
?>
<div style="padding: 50px; margin-top: 100px; background-image: url('https://www.bebuilt.es/wp-content/uploads/2025/12/Background.webp'); background-size: cover; background-position: center;">
    <div style="padding:50px; border-radius: 30px;background: rgba(0, 0, 0, 0.55);">
        <h2 style="text-align: center; font-size: 36px; font-weight: 700; margin-bottom: 15px; color:white">
            Descarga <span style="font-weight: 400;">cualquiera de los documentos</span> del proyecto
        </h2>
        
        <div style="display: flex; flex-wrap: wrap; gap: 40px; justify-content: center; margin-top:50px; max-width: 1200px; margin-left: auto; margin-right: auto;">
            <?php foreach ($documents as $doc): ?>
                <a href="<?php echo esc_url($doc->file_url); ?>" 
                   download 
                   target="_blank"
                   style="text-decoration: none; text-align: center; transition: transform 0.3s; width: 180px;"
                   onmouseover="this.style.transform='translateY(-5px)'"
                   onmouseout="this.style.transform='translateY(0)'">
                    <div style="width: 120px; height: 120px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                        <img src="https://www.bebuilt.es/wp-content/uploads/2025/12/Vector-21.svg" alt="Descargar" style="width: 90%;">
                    </div>
                    <div style="color: white; font-size: 17px; font-weight: 600; max-width: 180px; word-wrap: break-word; margin: 0 auto;">
                        <?php echo esc_html($doc->title); ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>


    <!-- Modal de hito -->
    <div id="milestoneModal" class="modal">
        <div class="modal-content">

            <div class="modal-top-bar">
                <div class="modal-top-left">
                    <div class="modal-date" id="modal-date"></div>
                    <span class="modal-status" id="modal-status"></span>
                </div>

                <div class="modal-top-right">
                    <div style="display:flex; gap:10px; justify-content: space-between;">
                        <button class="carousel-nav-btn" id="carousel-prev-top" onclick="changeSlide(-1)" title="Imagen anterior">
                            <img src="https://www.bebuilt.es/wp-content/uploads/2025/12/Vector-18.svg" alt="">
                        </button>
                        <button class="carousel-nav-btn" id="carousel-next-top" onclick="changeSlide(1)" title="Imagen siguiente">
                            <img src="https://www.bebuilt.es/wp-content/uploads/2025/12/Vector-19.svg" alt="">
                        </button>
                    </div>

                    <button class="modal-close" onclick="closeMilestoneModal()"><img src="https://www.bebuilt.es/wp-content/uploads/2025/12/Vector-20.svg" alt=""></button>
                </div>
            </div>

            <div style="display: flex; gap: 15px; justify-content:space-between;">
                <div class="modal-info">
                    <h2 class="modal-title" id="modal-title"></h2>
                    <div class="modal-description" id="modal-description"></div>

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
        let isScrolling = false;

        // ===== NUEVA FUNCIÓN: SCROLL AL HITO CORRESPONDIENTE =====
        function scrollToMilestone(index) {
            const milestoneCard = document.getElementById('milestone-' + index);
            if (milestoneCard) {
                // Desactivar el scroll sincronizado temporalmente
                isScrolling = true;

                // Activar el item en la barra superior
                const timelineItems = document.querySelectorAll('.timeline-top-item');
                timelineItems.forEach((item, idx) => {
                    if (idx === index) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });

                // Activar la card correspondiente
                const milestoneCards = document.querySelectorAll('.milestone-card');
                milestoneCards.forEach((card, idx) => {
                    if (idx === index) {
                        card.classList.add('active');
                    } else {
                        card.classList.remove('active');
                    }
                });

                milestoneCard.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                // Reactivar después de 1 segundo
                setTimeout(() => {
                    isScrolling = false;
                }, 1000);
            }
        }

        // ===== NUEVA FUNCIÓN: SINCRONIZACIÓN DEL SCROLL =====
        function setupScrollSync() {
            const verticalTimeline = document.getElementById('verticalTimeline');
            const timelineBarInner = document.getElementById('timelineBarInner');
            const milestoneCards = document.querySelectorAll('.milestone-card');

            if (!verticalTimeline || !timelineBarInner || milestoneCards.length === 0) {
                return;
            }

            let ticking = false;

            window.addEventListener('scroll', function() {
                if (isScrolling) return; // No sincronizar si estamos haciendo scroll programático

                if (!ticking) {
                    window.requestAnimationFrame(function() {
                        updateTimelinePosition();
                        ticking = false;
                    });
                    ticking = true;
                }
            });

            function updateTimelinePosition() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const windowHeight = window.innerHeight;
                const timelineStart = verticalTimeline.offsetTop;

                // Punto de referencia: 1/3 desde arriba de la pantalla
                const detectionPoint = scrollTop + (windowHeight / 3);

                let closestIndex = 0;
                let closestDistance = Infinity;

                // Si estamos antes del timeline, activar el primero
                if (scrollTop < timelineStart - 200) {
                    closestIndex = 0;
                } else {
                    // Buscar el hito cuyo centro esté más cerca del punto de detección
                    milestoneCards.forEach((card, index) => {
                        const cardTop = card.offsetTop;
                        const cardHeight = card.offsetHeight;
                        const cardCenter = cardTop + (cardHeight / 2);

                        // Calcular distancia al punto de detección
                        const distance = Math.abs(cardCenter - detectionPoint);

                        // Si este hito está más cerca que el anterior, actualizarlo
                        if (distance < closestDistance) {
                            closestDistance = distance;
                            closestIndex = index;
                        }
                    });
                }

                // Desplazar la barra horizontal para centrar el hito correspondiente
                const timelineItems = timelineBarInner.querySelectorAll('.timeline-top-item');
                if (timelineItems[closestIndex]) {
                    const itemLeft = timelineItems[closestIndex].offsetLeft;
                    const itemWidth = timelineItems[closestIndex].offsetWidth;
                    const containerWidth = timelineBarInner.parentElement.offsetWidth;

                    // Calcular el scroll para centrar el item
                    const scrollLeft = itemLeft - (containerWidth / 2) + (itemWidth / 2);

                    timelineBarInner.parentElement.scrollTo({
                        left: scrollLeft,
                        behavior: 'smooth'
                    });

                    // Destacar el item activo en la barra superior
                    timelineItems.forEach((item, idx) => {
                        if (idx === closestIndex) {
                            item.classList.add('active');
                        } else {
                            item.classList.remove('active');
                        }
                    });

                    // Destacar también el milestone-card activo
                    milestoneCards.forEach((card, idx) => {
                        if (idx === closestIndex) {
                            card.classList.add('active');
                        } else {
                            card.classList.remove('active');
                        }
                    });
                }
            }

        }

        function openMilestoneModal(index) {
            currentMilestoneIndex = index;
            currentSlide = 0;
            const milestone = milestones[index];

            const modal = document.getElementById('milestoneModal');
            const modalContent = modal.querySelector('.modal-content');

            modal.classList.add('active');

            modalContent.className = 'modal-content status-' + milestone.status;

            const date = new Date(milestone.date);
            document.getElementById('modal-date').textContent = date.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            document.getElementById('modal-title').textContent = milestone.title;
            document.getElementById('modal-description').innerHTML = milestone.description.replace(/\n/g, '<br>');

            const statusElement = document.getElementById('modal-status');
            const statusLabels = {
                'pendiente': 'Pendiente',
                'en_proceso': 'En Proceso',
                'finalizado': 'Finalizado'
            };
            statusElement.textContent = statusLabels[milestone.status] || milestone.status;
            statusElement.className = 'modal-status status-' + milestone.status;

            loadCarousel(milestone);
            updateMilestoneNavButtons();
            updateCarouselNavButtons();

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

            updateCarouselNavButtons();
        }

        function updateCarouselNavButtons() {
            const prevBtn = document.getElementById('carousel-prev-top');
            const nextBtn = document.getElementById('carousel-next-top');

            if (totalSlides <= 1) {
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
            } else {
                prevBtn.style.display = 'flex';
                nextBtn.style.display = 'flex';
                prevBtn.disabled = false;
                nextBtn.disabled = false;
            }
        }

        function navigateMilestone(direction) {
            closeMilestoneModal();
            setTimeout(() => {
                let newIndex = currentMilestoneIndex + direction;

                while (newIndex >= 0 && newIndex < milestones.length) {
                    if (milestones[newIndex].status !== 'pendiente') {
                        openMilestoneModal(newIndex);
                        return;
                    }
                    newIndex += direction;
                }
            }, 300);
        }

        function updateMilestoneNavButtons() {
            let hasPrevious = false;
            for (let i = currentMilestoneIndex - 1; i >= 0; i--) {
                if (milestones[i].status !== 'pendiente') {
                    hasPrevious = true;
                    break;
                }
            }

            let hasNext = false;
            for (let i = currentMilestoneIndex + 1; i < milestones.length; i++) {
                if (milestones[i].status !== 'pendiente') {
                    hasNext = true;
                    break;
                }
            }

            document.getElementById('prev-milestone-btn').disabled = !hasPrevious;
            document.getElementById('next-milestone-btn').disabled = !hasNext;
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMilestoneModal();
            }
            if (document.getElementById('milestoneModal').classList.contains('active')) {
                if (e.key === 'ArrowLeft') {
                    changeSlide(-1);
                } else if (e.key === 'ArrowRight') {
                    changeSlide(1);
                }
            }
        });

        window.onclick = function(event) {
            const modal = document.getElementById('milestoneModal');
            if (event.target == modal) {
                closeMilestoneModal();
            }
        }

        // Inicializar la sincronización del scroll cuando cargue la página
        document.addEventListener('DOMContentLoaded', function() {
            setupScrollSync();
        });
    </script>
</body>

</html>
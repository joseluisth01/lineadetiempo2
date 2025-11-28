<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Timeline</title>
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
            max-width: 1600px;
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
        
        .grid-2 {
            display: grid;
            grid-template-columns: 450px 1fr;
            gap: 40px;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 50px 40px;
        }
        
        .card h2 {
            font-size: 20px;
            font-weight: 300;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 40px;
            letter-spacing: 2px;
            text-transform: uppercase;
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
        
        .form-group input,
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
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-bottom-color: rgba(200, 150, 100, 0.6);
        }
        
        .form-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='rgba(255,255,255,0.4)' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right center;
            padding-right: 20px;
        }
        
        .form-group select option {
            background: #1a1a1a;
            color: #ffffff;
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
        
        .info-box {
            background: rgba(255, 255, 255, 0.03);
            padding: 20px;
            margin-top: 30px;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.4);
            border-left: 1px solid rgba(255, 255, 255, 0.1);
            letter-spacing: 0.5px;
            line-height: 1.6;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        th {
            padding: 20px 15px;
            text-align: left;
            font-weight: 300;
            color: rgba(255, 255, 255, 0.5);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        td {
            padding: 25px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 13px;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 300;
        }
        
        tbody tr {
            transition: background 0.3s;
        }
        
        tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }
        
        .badge {
            padding: 6px 14px;
            font-size: 9px;
            font-weight: 400;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            border: 1px solid;
        }
        
        .badge-red {
            background: rgba(255, 107, 107, 0.1);
            color: rgba(255, 107, 107, 0.9);
            border-color: rgba(255, 107, 107, 0.3);
        }
        
        .badge-blue {
            background: rgba(0, 123, 255, 0.1);
            color: rgba(0, 123, 255, 0.9);
            border-color: rgba(0, 123, 255, 0.3);
        }
        
        .badge-green {
            background: rgba(40, 167, 69, 0.1);
            color: rgba(40, 167, 69, 0.9);
            border-color: rgba(40, 167, 69, 0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: rgba(255, 255, 255, 0.3);
        }
        
        .empty-state p {
            font-size: 12px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .roles-info {
            background: rgba(255, 255, 255, 0.02);
            padding: 30px;
            margin-top: 40px;
            border-left: 1px solid rgba(200, 150, 100, 0.3);
        }
        
        .roles-info h3 {
            margin-top: 0;
            color: rgba(255, 255, 255, 0.8);
            font-size: 13px;
            margin-bottom: 20px;
            font-weight: 300;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        .roles-info ul {
            list-style: none;
            padding: 0;
        }
        
        .roles-info li {
            padding: 12px 0;
            color: rgba(255, 255, 255, 0.5);
            font-size: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-weight: 300;
            letter-spacing: 0.5px;
            line-height: 1.6;
        }

        #role-filter{
            background: black !important;
        }
        
        .roles-info li:last-child {
            border-bottom: none;
        }
        
        @media (max-width: 1200px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
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
        }
    </style>
        
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">BeBuilt</div>
        <div class="navbar-menu">
            <a href="<?php echo home_url('/timeline-dashboard'); ?>">Dashboard</a>
            <a href="<?php echo home_url('/timeline-proyectos'); ?>">Proyectos</a>
            <a href="<?php echo home_url('/timeline-usuarios'); ?>" class="active">Usuarios</a>
            <a href="<?php echo home_url('/timeline-perfil'); ?>">Perfil</a>
        </div>
        <div class="navbar-user">
            <div class="user-info">
                <div class="user-name"><?php echo esc_html($current_user->username); ?></div>
                <div class="user-role">
                    <?php echo $current_user->role === 'super_admin' ? 'Super Administrador' : 'Administrador'; ?>
                </div>
            </div>
            <a href="<?php echo admin_url('admin-post.php?action=timeline_logout'); ?>" class="btn-logout">
                Salir
            </a>
        </div>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <h1>Gestión de Usuarios</h1>
        </div>
        
        <?php if (isset($_GET['success']) && $_GET['success'] == 'created'): ?>
            <div class="alert alert-success">
                Usuario creado correctamente. Se ha enviado un email con las credenciales.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error']) && $_GET['error'] == 'failed'): ?>
            <div class="alert alert-error">
                Error al crear el usuario. Es posible que el nombre de usuario ya exista.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error']) && $_GET['error'] == 'permission'): ?>
            <div class="alert alert-error">
                No tienes permisos para crear ese tipo de usuario.
            </div>
        <?php endif; ?>
        
        <div class="grid-2">
            <!-- Formulario de crear usuario -->
            <div class="card">
                <h2>Crear Usuario</h2>
                
                <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="timeline_create_user">
                    <?php wp_nonce_field('timeline_create_user', 'timeline_create_user_nonce'); ?>
                    
                    <div class="form-group">
                        <label for="username">Usuario</label>
                        <input type="text" id="username" name="username" required>
                        <div class="help-text">Sin espacios, letras, números y guiones</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                        <div class="help-text">Se enviará la contraseña a este correo</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Rol</label>
                        <select id="role" name="role" required>
                            <option value="">Seleccionar...</option>
                            <?php if ($current_user->role === 'super_admin'): ?>
                                <option value="administrador">Administrador</option>
                            <?php endif; ?>
                            <option value="cliente">Cliente</option>
                        </select>
                        <div class="help-text">
                            <?php if ($current_user->role === 'super_admin'): ?>
                                Define los permisos del usuario
                            <?php else: ?>
                                Solo puedes crear clientes
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-primary">Crear Usuario</button>
                    
                    <div class="info-box">
                        La contraseña se generará automáticamente y se enviará por email al usuario.
                    </div>
                </form>
            </div>
            
            <!-- Lista de usuarios -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                    <h2 style="margin: 0;">Usuarios</h2>
                    <div class="filter-group">
                        <select id="role-filter" style="border: 1px solid rgba(255, 255, 255, 0.2); color: #fff; padding: 8px 30px 8px 15px; font-size: 11px; letter-spacing: 1.5px; text-transform: uppercase; cursor: pointer; appearance: none; background-image: url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 12 12\'%3E%3Cpath fill=\'rgba(255,255,255,0.4)\' d=\'M6 9L1 4h10z\'/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 10px center;">
                            <option value="all">Todos</option>
                            <option value="super_admin">Super Admin</option>
                            <option value="administrador">Administradores</option>
                            <option value="cliente">Clientes</option>
                        </select>
                    </div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Creación</th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body">
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                                <tr data-role="<?php echo esc_attr($user->role); ?>">
                                    <td><?php echo esc_html($user->username); ?></td>
                                    <td><?php echo esc_html($user->email); ?></td>
                                    <td>
                                        <?php
                                        switch($user->role) {
                                            case 'super_admin':
                                                echo '<span class="badge badge-red">Super Admin</span>';
                                                break;
                                            case 'administrador':
                                                echo '<span class="badge badge-blue">Administrador</span>';
                                                break;
                                            case 'cliente':
                                                echo '<span class="badge badge-green">Cliente</span>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($user->created_at)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <p>No hay usuarios</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <div class="roles-info">
                    <h3>Información</h3>
                    <ul>
                        <?php if ($current_user->role === 'super_admin'): ?>
                            <li>Super Admin — Acceso total al sistema y gestión de usuarios</li>
                            <li>Administrador — Gestión de proyectos y creación de clientes</li>
                            <li>Cliente — Visualización de proyectos asignados</li>
                        <?php else: ?>
                            <li>Administrador — Puedes crear y gestionar clientes</li>
                            <li>Cliente — Visualización de proyectos asignados</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Filtro de usuarios
        document.getElementById('role-filter').addEventListener('change', function() {
            const filterValue = this.value;
            const rows = document.querySelectorAll('#users-table-body tr[data-role]');
            
            rows.forEach(row => {
                if (filterValue === 'all') {
                    row.style.display = '';
                } else {
                    row.style.display = row.getAttribute('data-role') === filterValue ? '' : 'none';
                }
            });
        });
    </script>
</body>
</html>
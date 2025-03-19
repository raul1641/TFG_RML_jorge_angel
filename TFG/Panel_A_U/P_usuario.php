<?php

session_start();
require_once '../conf/conexion.php'; // Asegurar conexión

// Descargar PDF si está en sesión
if (isset($_SESSION['pdf_content'])) {
    $pdfContent = base64_decode($_SESSION['pdf_content']);
    $pdfName = $_SESSION['pdf_filename'];
    
    // Limpiar la sesión inmediatamente
    unset($_SESSION['pdf_content'], $_SESSION['pdf_filename']);

    // Forzar descarga vía JavaScript
    echo '<script>
        window.onload = function() 
        {
            var link = document.createElement("a");
            link.href = "data:application/pdf;base64,' . base64_encode($pdfContent) . '";
            link.download = "' . $pdfName . '";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };
    </script>';
}
?>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookLineRML - Panel de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="p_usuario.css">

    
    
    
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-book-reader me-2"></i>BookLineRML
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>Mi Cuenta
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#accountModal">
                            <i class="fas fa-user-edit me-2"></i>Editar Perfil</a>
                        </li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#passwordModal">
                            <i class="fas fa-key me-2"></i>Cambiar Contraseña</a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../PHP/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container mt-5 pt-4">
    <section id="buscar" class="mb-5">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title mb-4"><i class="fas fa-search me-2"></i>Buscar Libros</h2>
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchInput" placeholder="Buscar por título o autor...">
                            <button class="btn btn-primary" type="button" id="searchButton">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="availabilityFilter">
                            <option value="">Disponibilidad</option>
                            <option value="disponible">Disponible</option>
                            <option value="prestado">Prestado</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table table-hover" id="booksTable">
                        <thead class="table-light">
                            <tr>
                                <th>Título</th>
                                <th>Autor</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                        
                        <?php include '../PHP/libros.php'; ?>
                        
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <section id="prestamos" class="mb-5">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title mb-4"><i class="fas fa-book me-2"></i>Mis Préstamos</h2>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Libro</th>
                                <th>Fecha Préstamo</th>
                                <th>Fecha Devolución</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody >
                    
                        <?php include '../PHP/mis_prestamos.php'; ?>
                        
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Modal Editar Perfil -->
<div class="modal fade" id="accountModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Editar Perfil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="profileForm">
                    <div class="text-center mb-4">
                        <div class="avatar-container mb-3">
                            <img src="https://via.placeholder.com/150" class="rounded-circle img-thumbnail" alt="Avatar">
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-camera me-1"></i>Cambiar foto
                        </button>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" class="form-control" id="fullName" value="Usuario Ejemplo">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="userEmail" value="usuario@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="userPhone">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveProfile()">
                    <i class="fas fa-save me-1"></i>Guardar cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cambiar Contraseña -->
<div class="modal fade" id="passwordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="passwordForm">
                    <div class="mb-3">
                        <label class="form-label">Contraseña actual</label>
                        <input type="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva contraseña</label>
                        <input type="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar nueva contraseña</label>
                        <input type="password" class="form-control" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Login/registroValidacion.js"></script>

<!-- Agregar el script de préstamos -->
<script src="../JS/prestamos.js"></script>

</body>
</html>

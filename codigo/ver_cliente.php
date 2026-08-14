<?php

include("conexion.php");

if(!isset($_GET["id"]) || !is_numeric($_GET["id"])){
    die("Cliente no especificado.");
}

$id = intval($_GET["id"]);

$stmt = $conn->prepare("
    SELECT *
    FROM Cliente
    WHERE c_codigo = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();
$cliente = $resultado->fetch_assoc();

if(!$cliente){
    die("El cliente no existe.");
}

$mensaje = "";

if(isset($_GET["creado"])){
    $mensaje = "El cliente fue registrado correctamente.";
}

if(isset($_GET["editado"])){
    $mensaje = "Los datos del cliente fueron modificados correctamente.";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Cliente | Sistema Judicial</title>

<link rel="stylesheet" href="css/ver_cliente.css">

</head>

<body>

<header class="header">

    <div class="logo">

        <div class="logo-icon">⚖</div>

        <div>
            <h1>PODER JUDICIAL</h1>
            <span>Sistema de Gestión Judicial</span>
        </div>

    </div>

    <div class="usuario-header">

        <span class="notificacion">🔔</span>
        <span class="separador"></span>
        <span class="reloj">◷</span>
        <span class="separador"></span>

        <div class="usuario-icono">●</div>

        <div class="usuario-datos">
            <strong>Operador del Sistema</strong>
            <span>Usuario</span>
        </div>

        <span class="flecha">⌄</span>

    </div>

</header>

<nav class="navbar">

    <div class="navbar-contenido">

        <a href="index.php" class="nav-item">
            <span class="nav-icon">⌂</span>
            <span>Inicio</span>
        </a>

        <a href="expedientes.php" class="nav-item">
            <span class="nav-icon">▣</span>
            <span>Expedientes</span>
        </a>

        <a href="clientes.php" class="nav-item activo">
            <span class="nav-icon">♙</span>
            <span>Clientes</span>
        </a>

        <a href="#" class="nav-item">
            <span class="nav-icon">♙</span>
            <span>Abogados</span>
        </a>

        <a href="#" class="nav-item">
            <span class="nav-icon">⌂</span>
            <span>Juzgados</span>
        </a>

        <a href="#" class="nav-item">
            <span class="nav-icon">▣</span>
            <span>Audiencias</span>
        </a>

        <a href="#" class="nav-item">
            <span class="nav-icon">□</span>
            <span>Calendario</span>
        </a>

        <a href="#" class="nav-item">
            <span class="nav-icon">▥</span>
            <span>Reportes</span>
        </a>

    </div>

</nav>

<main class="contenido">

    <div class="encabezado-pagina">

        <div>

            <h2>Cliente</h2>

            <p>
                Información completa del cliente
            </p>

        </div>

        <div class="acciones-superiores">

            <a
                href="editar_cliente.php?id=<?php echo $id; ?>"
                class="btn-editar"
            >
                Editar
            </a>

            <a
                href="clientes.php"
                class="btn-secundario"
            >
                ← Volver
            </a>

        </div>

    </div>

    <?php if($mensaje != ""){ ?>

        <div class="mensaje-exito">
            ✓ <?php echo htmlspecialchars($mensaje); ?>
        </div>

    <?php } ?>

    <section class="cliente-panel">

        <div class="cliente-cabecera">

            <div class="avatar-grande">

                <?php
                echo strtoupper(
                    substr($cliente["c_nombre"], 0, 1) .
                    substr($cliente["c_apellido"], 0, 1)
                );
                ?>

            </div>

            <div>

                <span class="etiqueta">
                    CLIENTE #<?php echo $cliente["c_codigo"]; ?>
                </span>

                <h3>
                    <?php
                    echo htmlspecialchars(
                        $cliente["c_nombre"] . " " .
                        $cliente["c_apellido"]
                    );
                    ?>
                </h3>

            </div>

        </div>

        <div class="separador-panel"></div>

        <div class="datos-grid">

            <div class="dato">

                <span>Nombre</span>

                <strong>
                    <?php echo htmlspecialchars($cliente["c_nombre"]); ?>
                </strong>

            </div>

            <div class="dato">

                <span>Apellido</span>

                <strong>
                    <?php echo htmlspecialchars($cliente["c_apellido"]); ?>
                </strong>

            </div>

            <div class="dato">

                <span>Calle</span>

                <strong>
                    <?php echo htmlspecialchars($cliente["c_calle"]); ?>
                </strong>

            </div>

            <div class="dato">

                <span>Número</span>

                <strong>
                    <?php echo htmlspecialchars($cliente["c_numero"]); ?>
                </strong>

            </div>

            <div class="dato">

                <span>Piso</span>

                <strong>
                    <?php
                    echo !empty($cliente["c_piso"])
                        ? htmlspecialchars($cliente["c_piso"])
                        : "Sin especificar";
                    ?>
                </strong>

            </div>

            <div class="dato">

                <span>Departamento</span>

                <strong>
                    <?php
                    echo !empty($cliente["c_depto"])
                        ? htmlspecialchars($cliente["c_depto"])
                        : "Sin especificar";
                    ?>
                </strong>

            </div>

        </div>

    </section>

</main>

</body>
</html>

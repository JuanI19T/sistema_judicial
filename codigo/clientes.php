<?php

include("conexion.php");

$busqueda = trim($_GET["busqueda"] ?? "");

if($busqueda != ""){

    $sql = "
        SELECT *
        FROM Cliente
        WHERE c_nombre LIKE ?
        OR c_apellido LIKE ?
        OR c_codigo = ?
        ORDER BY c_apellido ASC, c_nombre ASC
    ";

    $stmt = $conn->prepare($sql);

    $busquedaCodigo = is_numeric($busqueda) ? intval($busqueda) : 0;
    $valor = "%" . $busqueda . "%";

    $stmt->bind_param("ssi", $valor, $valor, $busquedaCodigo);

}else{

    $sql = "
        SELECT *
        FROM Cliente
        ORDER BY c_apellido ASC, c_nombre ASC
    ";

    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$resultado = $stmt->get_result();

$clientes = [];

while($fila = $resultado->fetch_assoc()){
    $clientes[] = $fila;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Clientes | Sistema Judicial</title>

<link rel="stylesheet" href="css/clientes.css">
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

        <div class="usuario-icono">
            ●
        </div>

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
            <h2>Clientes</h2>
            <p>Administración y consulta de clientes registrados</p>
        </div>

        <a href="crear_cliente.php" class="btn-principal">
            + Nuevo cliente
        </a>

    </div>

    <section class="panel-busqueda">

        <form method="GET" action="clientes.php">

            <div class="busqueda-input">

                <span>⌕</span>

                <input
                    type="text"
                    name="busqueda"
                    placeholder="Buscar por nombre, apellido o código..."
                    value="<?php echo htmlspecialchars($busqueda); ?>"
                >

            </div>

            <button type="submit" class="btn-buscar">
                Buscar
            </button>

            <?php if($busqueda != ""){ ?>

                <a href="clientes.php" class="btn-limpiar">
                    Limpiar
                </a>

            <?php } ?>

        </form>

    </section>

    <section class="panel-clientes">

        <div class="panel-titulo">

            <div>
                <h3>Listado de clientes</h3>
                <span><?php echo count($clientes); ?> cliente(s) encontrado(s)</span>
            </div>

        </div>

        <?php if(count($clientes) > 0){ ?>

            <div class="tabla-contenedor">

                <table>

                    <thead>

                        <tr>
                            <th>Código</th>
                            <th>Nombre y apellido</th>
                            <th>Domicilio</th>
                            <th>Acciones</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach($clientes as $cliente){ ?>

                        <tr>

                            <td>
                                <strong>
                                    <?php echo $cliente["c_codigo"]; ?>
                                </strong>
                            </td>

                            <td>

                                <div class="cliente-nombre">

                                    <div class="avatar">
                                        <?php
                                        echo strtoupper(
                                            substr($cliente["c_nombre"], 0, 1) .
                                            substr($cliente["c_apellido"], 0, 1)
                                        );
                                        ?>
                                    </div>

                                    <div>

                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $cliente["c_nombre"] . " " .
                                                $cliente["c_apellido"]
                                            );
                                            ?>
                                        </strong>

                                    </div>

                                </div>

                            </td>

                            <td>

                                <?php
                                echo htmlspecialchars($cliente["c_calle"]);
                                ?>

                                Nº

                                <?php
                                echo htmlspecialchars($cliente["c_numero"]);
                                ?>

                                <?php if(!empty($cliente["c_piso"])){ ?>
                                    - Piso <?php echo htmlspecialchars($cliente["c_piso"]); ?>
                                <?php } ?>

                                <?php if(!empty($cliente["c_depto"])){ ?>
                                    - Depto. <?php echo htmlspecialchars($cliente["c_depto"]); ?>
                                <?php } ?>

                            </td>

                            <td>

                                <div class="acciones">

                                    <a
                                        href="ver_cliente.php?id=<?php echo $cliente["c_codigo"]; ?>"
                                        class="btn-accion ver"
                                    >
                                        Ver
                                    </a>

                                    <a
                                        href="editar_cliente.php?id=<?php echo $cliente["c_codigo"]; ?>"
                                        class="btn-accion editar"
                                    >
                                        Editar
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        <?php }else{ ?>

            <div class="sin-resultados">

                <div class="sin-resultados-icono">
                    ◌
                </div>

                <h3>No se encontraron clientes</h3>

                <p>
                    No existen clientes que coincidan con la búsqueda.
                </p>

            </div>

        <?php } ?>

    </section>

</main>

</body>
</html>

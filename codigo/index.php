<?php

include("conexion.php");

// Comprobar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener cantidad de expedientes
$sql = "SELECT COUNT(*) AS total FROM Expediente";
$resultado = $conn->query($sql);
$totalExpedientes = $resultado->fetch_assoc()["total"];

// Obtener cantidad de clientes
$sql = "SELECT COUNT(*) AS total FROM Cliente";
$resultado = $conn->query($sql);
$totalClientes = $resultado->fetch_assoc()["total"];

// Obtener cantidad de abogados
$sql = "SELECT COUNT(*) AS total FROM Abogado";
$resultado = $conn->query($sql);
$totalAbogados = $resultado->fetch_assoc()["total"];

// Obtener cantidad de juzgados
$sql = "SELECT COUNT(*) AS total FROM Juzgado";
$resultado = $conn->query($sql);
$totalJuzgados = $resultado->fetch_assoc()["total"];

// Obtener cantidad de audiencias pendientes
$sql = "
    SELECT COUNT(*) AS total
    FROM Audiencia
    WHERE au_estado = 'Pendiente'
";
$resultado = $conn->query($sql);
$totalAudiencias = $resultado->fetch_assoc()["total"];

// Variables del buscador
$busqueda = trim($_GET["busqueda"] ?? "");
$fuero = trim($_GET["fuero"] ?? "");
$abogado = trim($_GET["abogado"] ?? "");
$juzgado = trim($_GET["juzgado"] ?? "");

// Obtener fueros para el filtro
$sql = "
    SELECT DISTINCT fuero
    FROM Juzgado
    ORDER BY fuero ASC
";

$resultadoFueros = $conn->query($sql);

$fueros = [];

while ($fila = $resultadoFueros->fetch_assoc()) {
    $fueros[] = $fila["fuero"];
}

// Obtener abogados para el filtro
$sql = "
    SELECT
        a_codigo,
        a_nombre,
        a_apellido
    FROM Abogado
    ORDER BY a_apellido ASC, a_nombre ASC
";

$resultadoAbogados = $conn->query($sql);

$abogados = [];

while ($fila = $resultadoAbogados->fetch_assoc()) {
    $abogados[] = $fila;
}

// Obtener juzgados para el filtro
$sql = "
    SELECT
        j_codigo,
        j_nombre
    FROM Juzgado
    ORDER BY j_nombre ASC
";

$resultadoJuzgados = $conn->query($sql);

$juzgados = [];

while ($fila = $resultadoJuzgados->fetch_assoc()) {
    $juzgados[] = $fila;
}

// Construir búsqueda de expedientes
$expedientesBusqueda = [];

if (
    $busqueda !== "" ||
    $fuero !== "" ||
    $abogado !== "" ||
    $juzgado !== ""
) {

    $sql = "
        SELECT DISTINCT
            e.e_codigo,
            e.e_caratula,
            e.ultima_modificacion,

            c.c_nombre,
            c.c_apellido,

            j.j_codigo,
            j.j_nombre,
            j.fuero

        FROM Expediente e

        INNER JOIN Cliente c
            ON e.c_codigo = c.c_codigo

        INNER JOIN Juzgado j
            ON e.j_codigo = j.j_codigo

        LEFT JOIN Abogado_Expediente ae
            ON e.e_codigo = ae.e_codigo

        LEFT JOIN Abogado a
            ON ae.a_codigo = a.a_codigo

        WHERE 1=1
    ";

    $parametros = [];
    $tipos = "";

    // Buscar por expediente, carátula, cliente, abogado o juzgado
    if ($busqueda !== "") {

        $sql .= "
            AND (
                CAST(e.e_codigo AS CHAR) LIKE ?
                OR e.e_caratula LIKE ?
                OR c.c_nombre LIKE ?
                OR c.c_apellido LIKE ?
                OR j.j_nombre LIKE ?
                OR a.a_nombre LIKE ?
                OR a.a_apellido LIKE ?
            )
        ";

        $valorBusqueda = "%" . $busqueda . "%";

        $parametros[] = $valorBusqueda;
        $parametros[] = $valorBusqueda;
        $parametros[] = $valorBusqueda;
        $parametros[] = $valorBusqueda;
        $parametros[] = $valorBusqueda;
        $parametros[] = $valorBusqueda;
        $parametros[] = $valorBusqueda;

        $tipos .= "sssssss";
    }

    // Filtrar por fuero
    if ($fuero !== "") {

        $sql .= " AND j.fuero = ?";

        $parametros[] = $fuero;
        $tipos .= "s";
    }

    // Filtrar por abogado
    if ($abogado !== "") {

        $sql .= " AND a.a_codigo = ?";

        $parametros[] = intval($abogado);
        $tipos .= "i";
    }

    // Filtrar por juzgado
    if ($juzgado !== "") {

        $sql .= " AND j.j_codigo = ?";

        $parametros[] = intval($juzgado);
        $tipos .= "i";
    }

    $sql .= "
        ORDER BY e.ultima_modificacion DESC
        LIMIT 20
    ";

    $stmtBusqueda = $conn->prepare($sql);

    if ($stmtBusqueda) {

        if (!empty($parametros)) {
            $stmtBusqueda->bind_param($tipos, ...$parametros);
        }

        $stmtBusqueda->execute();

        $resultadoBusqueda = $stmtBusqueda->get_result();

        while ($fila = $resultadoBusqueda->fetch_assoc()) {
            $expedientesBusqueda[] = $fila;
        }

        $stmtBusqueda->close();
    }
}

// Obtener expedientes recientes
$sql = "
    SELECT
        e.e_codigo,
        e.e_caratula,
        e.ultima_modificacion,

        c.c_nombre,
        c.c_apellido,

        j.j_nombre,

        (
            SELECT CONCAT(a.a_nombre, ' ', a.a_apellido)
            FROM Abogado_Expediente ae
            INNER JOIN Abogado a
                ON ae.a_codigo = a.a_codigo
            WHERE ae.e_codigo = e.e_codigo
            ORDER BY ae.fecha_asignacion DESC
            LIMIT 1
        ) AS abogado

    FROM Expediente e

    INNER JOIN Cliente c
        ON e.c_codigo = c.c_codigo

    INNER JOIN Juzgado j
        ON e.j_codigo = j.j_codigo

    ORDER BY e.ultima_modificacion DESC

    LIMIT 5
";

$resultadoRecientes = $conn->query($sql);

$expedientesRecientes = [];

while ($fila = $resultadoRecientes->fetch_assoc()) {
    $expedientesRecientes[] = $fila;
}

// Obtener próximas audiencias
$sql = "
    SELECT
        au.au_codigo,
        au.au_fecha_hora,
        au.au_estado,
        au.au_tipo,

        e.e_codigo,
        e.e_caratula,

        j.j_nombre

    FROM Audiencia au

    INNER JOIN Expediente e
        ON au.e_codigo = e.e_codigo

    INNER JOIN Juzgado j
        ON e.j_codigo = j.j_codigo

    WHERE au.au_estado = 'Pendiente'

    ORDER BY au.au_fecha_hora ASC

    LIMIT 5
";

$resultadoAudiencias = $conn->query($sql);

$audiencias = [];

while ($fila = $resultadoAudiencias->fetch_assoc()) {
    $audiencias[] = $fila;
}

// Función para mostrar fecha
function formatearFecha($fecha)
{
    if (!$fecha) {
        return "-";
    }

    return date("d/m/Y H:i", strtotime($fecha));
}

// Función para mostrar fecha corta
function formatearFechaCorta($fecha)
{
    if (!$fecha) {
        return "-";
    }

    return date("d/m", strtotime($fecha));
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sistema de Gestión Judicial</title>

    <link rel="stylesheet" href="style_index.css">

</head>

<body>

<div class="sistema">

    <!-- Menú lateral -->

    <aside class="sidebar">

        <div class="logo">

            <div class="logo-icon">
                ⚖
            </div>

            <div class="logo-text">

                <strong>PODER JUDICIAL</strong>

                <span>Sistema de Gestión Judicial</span>

            </div>

        </div>

        <nav class="menu">

            <a href="index.php" class="menu-item activo">

                <span class="icono">⌂</span>

                <span>Inicio</span>

            </a>

            <a href="expedientes.php" class="menu-item">

                <span class="icono">▣</span>

                <span>Expedientes</span>

            </a>

            <a href="clientes.php" class="menu-item">

                <span class="icono">♙</span>

                <span>Clientes</span>

            </a>

            <a href="abogados.php" class="menu-item">

                <span class="icono">♙</span>

                <span>Abogados</span>

            </a>

            <a href="juzgados.php" class="menu-item">

                <span class="icono">▤</span>

                <span>Juzgados</span>

            </a>

            <a href="audiencias.php" class="menu-item">

                <span class="icono">▣</span>

                <span>Audiencias</span>

            </a>

            <a href="calendario.php" class="menu-item">

                <span class="icono">□</span>

                <span>Calendario</span>

            </a>

            <a href="reportes.php" class="menu-item">

                <span class="icono">▥</span>

                <span>Reportes</span>

            </a>

        </nav>

        <div class="menu-separador"></div>

        <div class="menu-titulo">
            CONFIGURACIÓN
        </div>

        <nav class="menu">

            <a href="usuarios.php" class="menu-item">

                <span class="icono">♙</span>

                <span>Usuarios</span>

            </a>

            <a href="perfil.php" class="menu-item">

                <span class="icono">♙</span>

                <span>Perfil</span>

            </a>

        </nav>

        <div class="sidebar-abajo">

            <a href="#" class="cerrar-sesion">

                <span>↪</span>

                <span>Cerrar sesión</span>

            </a>

        </div>

    </aside>

    <!-- Contenido principal -->

    <main class="contenido-principal">

        <!-- Barra superior -->

        <header class="topbar">

            <div class="topbar-izquierda">

                <button class="boton-menu" type="button">
                    ☰
                </button>

                <span class="pagina-actual">
                    Inicio
                </span>

            </div>

            <div class="topbar-derecha">

                <button class="icono-topbar" type="button">
                    ♧
                    <span class="notificacion">3</span>
                </button>

                <button class="icono-topbar" type="button">
                    ◷
                </button>

                <div class="usuario">

                    <div class="avatar">
                        MG
                    </div>

                    <div class="datos-usuario">

                        <strong>Operador del Sistema</strong>

                        <span>Abogado</span>

                    </div>

                    <span class="flecha">
                       ⌄
                    </span>

                </div>

            </div>

        </header>

        <div class="contenido">

            <!-- Encabezado -->

            <section class="bienvenida">

                <h1>
                    Bienvenido al Sistema de Gestión Judicial
                </h1>

                <p>
                    Panel general de gestión de expedientes y casos
                </p>

            </section>

            <!-- Tarjetas estadísticas -->

            <section class="estadisticas">

                <a href="expedientes.php" class="estadistica">

                    <div class="estadistica-icono">
                        ▰
                    </div>

                    <div class="estadistica-datos">

                        <strong>
                            <?php echo $totalExpedientes; ?>
                        </strong>

                        <span>
                            Expedientes
                        </span>

                    </div>

                    <div class="estadistica-abajo">
                        Ver todos
                    </div>

                </a>

                <a href="clientes.php" class="estadistica">

                    <div class="estadistica-icono">
                        ♙
                    </div>

                    <div class="estadistica-datos">

                        <strong>
                            <?php echo $totalClientes; ?>
                        </strong>

                        <span>
                            Clientes
                        </span>

                    </div>

                    <div class="estadistica-abajo">
                        Ver todos
                    </div>

                </a>

                <a href="abogados.php" class="estadistica">

                    <div class="estadistica-icono">
                        ♙
                    </div>

                    <div class="estadistica-datos">

                        <strong>
                            <?php echo $totalAbogados; ?>
                        </strong>

                        <span>
                            Abogados
                        </span>

                    </div>

                    <div class="estadistica-abajo">
                        Ver todos
                    </div>

                </a>

                <a href="juzgados.php" class="estadistica">

                    <div class="estadistica-icono">
                        ▤
                    </div>

                    <div class="estadistica-datos">

                        <strong>
                            <?php echo $totalJuzgados; ?>
                        </strong>

                        <span>
                            Juzgados
                        </span>

                    </div>

                    <div class="estadistica-abajo">
                        Ver todos
                    </div>

                </a>

                <a href="audiencias.php" class="estadistica">

                    <div class="estadistica-icono">
                        ▣
                    </div>

                    <div class="estadistica-datos">

                        <strong>
                            <?php echo $totalAudiencias; ?>
                        </strong>

                        <span>
                            Audiencias pendientes
                        </span>

                    </div>

                    <div class="estadistica-abajo">
                        Ver calendario
                    </div>

                </a>

            </section>

            <!-- Buscador -->

            <section class="panel-busqueda">

                <div class="cabecera-busqueda">

                    <div>

                        <h2>
                            Buscar expedientes y casos
                        </h2>

                        <p>
                            Encontrá rápidamente un expediente, cliente, abogado o juzgado.
                        </p>

                    </div>

                    <button
                        type="button"
                        class="boton-filtros"
                        onclick="mostrarFiltros()"
                    >
                        ☷
                        Búsqueda avanzada
                    </button>

                </div>

                <form method="GET" action="index.php">

                    <div class="busqueda-principal">

                        <input
                            type="text"
                            name="busqueda"
                            value="<?php echo htmlspecialchars($busqueda); ?>"
                            placeholder="Buscar por número de expediente, carátula, cliente, abogado o juzgado..."
                        >

                        <button type="submit" class="boton-buscar">
                            🔍
                            Buscar
                        </button>

                    </div>

                    <div
                        class="filtros"
                        id="filtros"
                    >

                        <div class="campo">

                            <label for="fuero">
                                Fuero
                            </label>

                            <select name="fuero" id="fuero">

                                <option value="">
                                    Todos
                                </option>

                                <?php foreach ($fueros as $fueroItem): ?>

                                    <option
                                        value="<?php echo htmlspecialchars($fueroItem); ?>"
                                        <?php echo ($fuero === $fueroItem) ? "selected" : ""; ?>
                                    >

                                        <?php echo htmlspecialchars($fueroItem); ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="campo">

                            <label for="abogado">
                                Abogado
                            </label>

                            <select name="abogado" id="abogado">

                                <option value="">
                                    Todos
                                </option>

                                <?php foreach ($abogados as $abogadoItem): ?>

                                    <option
                                        value="<?php echo $abogadoItem["a_codigo"]; ?>"
                                        <?php echo ($abogado == $abogadoItem["a_codigo"]) ? "selected" : ""; ?>
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $abogadoItem["a_apellido"] . ", " . $abogadoItem["a_nombre"]
                                        );
                                        ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="campo">

                            <label for="juzgado">
                                Juzgado
                            </label>

                            <select name="juzgado" id="juzgado">

                                <option value="">
                                    Todos
                                </option>

                                <?php foreach ($juzgados as $juzgadoItem): ?>

                                    <option
                                        value="<?php echo $juzgadoItem["j_codigo"]; ?>"
                                        <?php echo ($juzgado == $juzgadoItem["j_codigo"]) ? "selected" : ""; ?>
                                    >

                                        <?php echo htmlspecialchars($juzgadoItem["j_nombre"]); ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <a href="index.php" class="limpiar-filtros">
                            ↻
                            Limpiar filtros
                        </a>

                    </div>

                </form>

            </section>

            <!-- Resultados de búsqueda -->

            <?php if (
                $busqueda !== "" ||
                $fuero !== "" ||
                $abogado !== "" ||
                $juzgado !== ""
            ): ?>

                <section class="panel-resultados">

                    <div class="panel-titulo">

                        <div>

                            <h2>
                                Resultados de búsqueda
                            </h2>

                            <span>
                                <?php echo count($expedientesBusqueda); ?> resultado(s)
                            </span>

                        </div>

                    </div>

                    <?php if (count($expedientesBusqueda) > 0): ?>

                        <div class="tabla-resultados">

                            <div class="tabla-cabecera">

                                <span>
                                    N.º EXPEDIENTE
                                </span>

                                <span>
                                    CARÁTULA
                                </span>

                                <span>
                                    CLIENTE
                                </span>

                                <span>
                                    JUZGADO
                                </span>

                                <span>
                                    ACCIÓN
                                </span>

                            </div>

                            <?php foreach ($expedientesBusqueda as $expediente): ?>

                                <div class="tabla-fila">

                                    <strong>
                                        <?php echo $expediente["e_codigo"]; ?>
                                    </strong>

                                    <span>
                                        <?php echo htmlspecialchars($expediente["e_caratula"]); ?>
                                    </span>

                                    <span>
                                        <?php
                                        echo htmlspecialchars(
                                            $expediente["c_nombre"] . " " . $expediente["c_apellido"]
                                        );
                                        ?>
                                    </span>

                                    <span>
                                        <?php echo htmlspecialchars($expediente["j_nombre"]); ?>
                                    </span>

                                    <a
                                        href="ver_expediente.php?id=<?php echo $expediente["e_codigo"]; ?>"
                                        class="ver-expediente"
                                    >
                                        Ver
                                    </a>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php else: ?>

                        <div class="sin-resultados">

                            <span class="sin-resultados-icono">
                                ⌕
                            </span>

                            <strong>
                                No se encontraron expedientes
                            </strong>

                            <p>
                                Probá modificando los términos o filtros de búsqueda.
                            </p>

                        </div>

                    <?php endif; ?>

                </section>

            <?php endif; ?>

            <!-- Dos columnas -->

            <section class="paneles-inferiores">

                <!-- Expedientes recientes -->

                <div class="panel">

                    <div class="panel-cabecera">

                        <div>

                            <h2>
                                Expedientes recientes
                            </h2>

                            <p>
                                Últimos expedientes modificados
                            </p>

                        </div>

                        <a href="expedientes.php">
                            Ver todos los expedientes
                        </a>

                    </div>

                    <div class="tabla">

                        <div class="tabla-head">

                            <span>
                                N.º EXPEDIENTE
                            </span>

                            <span>
                                CARÁTULA
                            </span>

                            <span>
                                CLIENTE
                            </span>

                            <span>
                                ABOGADO
                            </span>

                            <span>
                                JUZGADO
                            </span>

                            <span>
                                ACTUALIZACIÓN
                            </span>

                        </div>

                        <?php if (count($expedientesRecientes) > 0): ?>

                            <?php foreach ($expedientesRecientes as $expediente): ?>

                                <a
                                    href="ver_expediente.php?id=<?php echo $expediente["e_codigo"]; ?>"
                                    class="tabla-row"
                                >

                                    <strong>
                                        <?php echo $expediente["e_codigo"]; ?>
                                    </strong>

                                    <span>
                                        <?php echo htmlspecialchars($expediente["e_caratula"]); ?>
                                    </span>

                                    <span>
                                        <?php
                                        echo htmlspecialchars(
                                            $expediente["c_nombre"] . " " . $expediente["c_apellido"]
                                        );
                                        ?>
                                    </span>

                                    <span>
                                        <?php
                                        echo htmlspecialchars(
                                            $expediente["abogado"] ?? "Sin asignar"
                                        );
                                        ?>
                                    </span>

                                    <span>
                                        <?php echo htmlspecialchars($expediente["j_nombre"]); ?>
                                    </span>

                                    <span>
                                        <?php echo formatearFecha($expediente["ultima_modificacion"]); ?>
                                    </span>

                                </a>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <div class="sin-datos">
                                No hay expedientes registrados.
                            </div>

                        <?php endif; ?>

                    </div>

                </div>

                <!-- Próximas audiencias -->

                <div class="panel panel-audiencias">

                    <div class="panel-cabecera">

                        <div>

                            <h2>
                                Próximas audiencias
                            </h2>

                            <p>
                                Audiencias pendientes
                            </p>

                        </div>

                        <a href="calendario.php">
                            Ver calendario
                        </a>

                    </div>

                    <div class="lista-audiencias">

                        <?php if (count($audiencias) > 0): ?>

                            <?php foreach ($audiencias as $audiencia): ?>

                                <a
                                    href="ver_expediente.php?id=<?php echo $audiencia["e_codigo"]; ?>"
                                    class="audiencia"
                                >

                                    <div class="fecha-audiencia">

                                        <strong>
                                            <?php echo date("d", strtotime($audiencia["au_fecha_hora"])); ?>
                                        </strong>

                                        <span>
                                            <?php echo strtoupper(date("M", strtotime($audiencia["au_fecha_hora"]))); ?>
                                        </span>

                                    </div>

                                    <div class="hora-audiencia">

                                        <?php echo date("H:i", strtotime($audiencia["au_fecha_hora"])); ?>

                                    </div>

                                    <div class="datos-audiencia">

                                        <strong>
                                            Audiencia <?php echo htmlspecialchars($audiencia["au_tipo"]); ?>
                                        </strong>

                                        <span>
                                            Exp. <?php echo $audiencia["e_codigo"]; ?>
                                        </span>

                                        <small>
                                            <?php echo htmlspecialchars($audiencia["j_nombre"]); ?>
                                        </small>

                                    </div>

                                    <span class="estado-pendiente">
                                        Pendiente
                                    </span>

                                </a>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <div class="sin-datos">
                                No hay audiencias pendientes.
                            </div>

                        <?php endif; ?>

                    </div>

                    <a href="audiencias.php" class="ver-audiencias">
                        Ver todas las audiencias →
                    </a>

                </div>

            </section>

            <!-- Acciones rápidas -->

            <section class="panel acciones-panel">

                <div class="panel-cabecera">

                    <div>

                        <h2>
                            Acciones rápidas
                        </h2>

                        <p>
                            Funciones de uso cotidiano
                        </p>

                    </div>

                </div>

                <div class="acciones">

                    <a href="nuevo_expediente.php" class="accion">

                        <div class="accion-icono">
                            ▰+
                        </div>

                        <div>

                            <strong>
                                Nuevo expediente
                            </strong>

                            <span>
                                Registrar un nuevo caso
                            </span>

                        </div>

                    </a>

                    <a href="nuevo_cliente.php" class="accion">

                        <div class="accion-icono">
                            ♙+
                        </div>

                        <div>

                            <strong>
                                Nuevo cliente
                            </strong>

                            <span>
                                Registrar un nuevo cliente
                            </span>

                        </div>

                    </a>

                    <a href="nuevo_abogado.php" class="accion">

                        <div class="accion-icono">
                            ♙+
                        </div>

                        <div>

                            <strong>
                                Nuevo abogado
                            </strong>

                            <span>
                                Registrar un abogado
                            </span>

                        </div>

                    </a>

                    <a href="nueva_audiencia.php" class="accion">

                        <div class="accion-icono">
                            ▣+
                        </div>

                        <div>

                            <strong>
                                Programar audiencia
                            </strong>

                            <span>
                                Registrar una nueva audiencia
                            </span>

                        </div>

                    </a>

                    <a href="juzgados.php" class="accion">

                        <div class="accion-icono">
                            ▤
                        </div>

                        <div>

                            <strong>
                                Consultar juzgados
                            </strong>

                            <span>
                                Ver juzgados registrados
                            </span>

                        </div>

                    </a>

                </div>

            </section>

        </div>

    </main>

</div>

<script>

function mostrarFiltros() {

    const filtros = document.getElementById("filtros");

    filtros.classList.toggle("visible");

}

</script>

</body>

</html>
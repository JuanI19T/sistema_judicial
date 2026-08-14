<?php
include("conexion.php");

// Comprobar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Variables del buscador
$busqueda = trim($_GET["busqueda"] ?? "");
$fuero = trim($_GET["fuero"] ?? "");
$abogado = trim($_GET["abogado"] ?? "");
$juzgado = trim($_GET["juzgado"] ?? "");

// Obtener fueros disponibles
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

// Obtener abogados disponibles
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

// Obtener juzgados disponibles
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

// Construir consulta de expedientes
$sql = "
    SELECT DISTINCT
        e.e_codigo,
        e.e_caratula,
        e.ultima_modificacion,
        c.c_nombre,
        c.c_apellido,
        j.j_nombre,
        j.fuero,
        (
            SELECT CONCAT(a.a_nombre, ' ', a.a_apellido)
            FROM Abogado_Expediente ae2
            INNER JOIN Abogado a
                ON ae2.a_codigo = a.a_codigo
            WHERE ae2.e_codigo = e.e_codigo
            ORDER BY ae2.fecha_asignacion DESC
            LIMIT 1
        ) AS abogado
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

// Buscar por número, carátula, cliente, abogado o juzgado
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

// Ordenar por última modificación
$sql .= "
    ORDER BY e.ultima_modificacion DESC
";

$stmt = $conn->prepare($sql);

$expedientes = [];

if ($stmt) {
    if (!empty($parametros)) {
        $stmt->bind_param($tipos, ...$parametros);
    }

    $stmt->execute();

    $resultado = $stmt->get_result();

    while ($fila = $resultado->fetch_assoc()) {
        $expedientes[] = $fila;
    }

    $stmt->close();
}

// Cantidad total de expedientes
$totalExpedientes = count($expedientes);

// Función para mostrar fecha
function formatearFecha($fecha)
{
    if (!$fecha) {
        return "-";
    }

    return date("d/m/Y H:i", strtotime($fecha));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expedientes - Sistema de Gestión Judicial</title>
    <link rel="stylesheet" href="css/expedientes.css">
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
            <a href="index.php" class="menu-item">
                <span class="icono">⌂</span>
                <span>Inicio</span>
            </a>

            <a href="expedientes.php" class="menu-item activo">
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
                    Expedientes
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

            <!-- Encabezado de la sección -->
            <section class="encabezado-pagina">
                <div>
                    <h1>
                        Expedientes
                    </h1>

                    <p>
                        Administración y seguimiento de los casos judiciales.
                    </p>
                </div>

                <a href="crear_expediente.php" class="boton-nuevo">
                    + Nuevo expediente
                </a>
            </section>

            <!-- Panel de búsqueda -->
            <section class="panel-busqueda">
                <div class="cabecera-busqueda">
                    <div>
                        <h2>
                            Buscar expediente
                        </h2>

                        <p>
                            Buscá por número, carátula, cliente, abogado o juzgado.
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

                <form method="GET" action="expedientes.php">

                    <div class="busqueda-principal">
                        <input
                            type="text"
                            name="busqueda"
                            value="<?php echo htmlspecialchars($busqueda); ?>"
                            placeholder="Número de expediente, carátula, cliente, abogado o juzgado..."
                        >

                        <button type="submit" class="boton-buscar">
                            🔍
                            Buscar
                        </button>
                    </div>

                    <!-- Filtros adicionales -->
                    <div
                        class="filtros <?php echo ($fuero !== "" || $abogado !== "" || $juzgado !== "") ? "visible" : ""; ?>"
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

                        <a href="expedientes.php" class="limpiar-filtros">
                            ↻
                            Limpiar filtros
                        </a>
                    </div>
                </form>
            </section>

            <!-- Resumen de resultados -->
            <section class="resumen-resultados">
                <div>
                    <strong>
                        <?php echo $totalExpedientes; ?>
                    </strong>

                    <span>
                        <?php echo ($totalExpedientes == 1) ? "expediente encontrado" : "expedientes encontrados"; ?>
                    </span>
                </div>

                <a href="crear_expediente.php">
                    Crear expediente
                </a>
            </section>

            <!-- Listado de expedientes -->
            <section class="panel-listado">

                <div class="panel-cabecera">
                    <div>
                        <h2>
                            Expedientes registrados
                        </h2>

                        <p>
                            Listado de expedientes ordenados por última modificación.
                        </p>
                    </div>
                </div>

                <?php if ($totalExpedientes > 0): ?>

                    <div class="tabla-expedientes">

                        <!-- Cabecera -->
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

                            <span>
                                ACCIONES
                            </span>
                        </div>

                        <!-- Filas -->
                        <?php foreach ($expedientes as $expediente): ?>

                            <div class="tabla-row">

                                <strong class="numero-expediente">
                                    <?php echo htmlspecialchars($expediente["e_codigo"]); ?>
                                </strong>

                                <span class="caratula">
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

                                <span class="fecha">
                                    <?php echo formatearFecha($expediente["ultima_modificacion"]); ?>
                                </span>

                                <div class="acciones-expediente">

                                    <a
                                        href="ver_expediente.php?id=<?php echo $expediente["e_codigo"]; ?>"
                                        class="accion-ver"
                                        title="Ver expediente"
                                    >
                                        Ver
                                    </a>

                                    <a
                                        href="editar_expediente.php?id=<?php echo $expediente["e_codigo"]; ?>"
                                        class="accion-editar"
                                        title="Editar expediente"
                                    >
                                        Editar
                                    </a>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <!-- Mensaje cuando no hay resultados -->
                    <div class="sin-resultados">

                        <div class="icono-sin-resultados">
                            ▱
                        </div>

                        <strong>
                            No se encontraron expedientes
                        </strong>

                        <?php if ($busqueda !== "" || $fuero !== "" || $abogado !== "" || $juzgado !== ""): ?>

                            <p>
                                Probá modificando los términos o filtros de búsqueda.
                            </p>

                            <a href="expedientes.php" class="boton-limpiar">
                                Limpiar búsqueda
                            </a>

                        <?php else: ?>

                            <p>
                                Todavía no hay expedientes registrados en el sistema.
                            </p>

                            <a href="crear_expediente.php" class="boton-nuevo-secundario">
                                + Crear primer expediente
                            </a>

                        <?php endif; ?>

                    </div>

                <?php endif; ?>

            </section>

            <!-- Acciones rápidas -->
            <section class="panel acciones-panel">

                <div class="panel-cabecera">
                    <div>
                        <h2>
                            Acciones rápidas
                        </h2>

                        <p>
                            Funciones de uso cotidiano para la gestión de expedientes.
                        </p>
                    </div>
                </div>

                <div class="acciones">

                    <a href="crear_expediente.php" class="accion">
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

                    <a href="clientes.php" class="accion">
                        <div class="accion-icono">
                            ♙
                        </div>

                        <div>
                            <strong>
                                Consultar clientes
                            </strong>

                            <span>
                                Buscar información de clientes
                            </span>
                        </div>
                    </a>

                    <a href="abogados.php" class="accion">
                        <div class="accion-icono">
                            ♙
                        </div>

                        <div>
                            <strong>
                                Consultar abogados
                            </strong>

                            <span>
                                Ver abogados registrados
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
                                Ver juzgados disponibles
                            </span>
                        </div>
                    </a>

                    <a href="audiencias.php" class="accion">
                        <div class="accion-icono">
                            ▣
                        </div>

                        <div>
                            <strong>
                                Audiencias
                            </strong>

                            <span>
                                Consultar audiencias relacionadas
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
<?php

include("conexion.php");

$busqueda = trim($_GET["busqueda"] ?? "");
$fuero = trim($_GET["fuero"] ?? "");

$sql = "
SELECT
    e.e_codigo,
    e.e_caratula,
    e.ultima_modificacion,
    c.c_codigo,
    c.c_nombre,
    c.c_apellido,
    j.j_codigo,
    j.j_nombre,
    j.fuero,
    COUNT(DISTINCT ae.a_codigo) AS cantidad_abogados
FROM Expediente e
INNER JOIN Cliente c
    ON e.c_codigo = c.c_codigo
INNER JOIN Juzgado j
    ON e.j_codigo = j.j_codigo
LEFT JOIN Abogado_Expediente ae
    ON e.e_codigo = ae.e_codigo
WHERE 1=1
";

$parametros = [];
$tipos = "";

if($busqueda != ""){
    $sql .= "
    AND (
        e.e_codigo LIKE ?
        OR e.e_caratula LIKE ?
        OR c.c_nombre LIKE ?
        OR c.c_apellido LIKE ?
        OR j.j_nombre LIKE ?
    )
    ";

    $valor = "%" . $busqueda . "%";

    $parametros[] = $valor;
    $parametros[] = $valor;
    $parametros[] = $valor;
    $parametros[] = $valor;
    $parametros[] = $valor;

    $tipos .= "sssss";
}

if($fuero != ""){
    $sql .= " AND j.fuero = ?";
    $parametros[] = $fuero;
    $tipos .= "s";
}

$sql .= "
GROUP BY
    e.e_codigo,
    e.e_caratula,
    e.ultima_modificacion,
    c.c_codigo,
    c.c_nombre,
    c.c_apellido,
    j.j_codigo,
    j.j_nombre,
    j.fuero
ORDER BY e.ultima_modificacion DESC
";

$stmt = $conn->prepare($sql);

if(!$stmt){
    die("Error al preparar la consulta.");
}

if(!empty($parametros)){
    $stmt->bind_param($tipos, ...$parametros);
}

$stmt->execute();

$resultado = $stmt->get_result();

$expedientes = [];

while($fila = $resultado->fetch_assoc()){
    $expedientes[] = $fila;
}

$stmt->close();

$fueros = [];

$sqlFueros = "
SELECT DISTINCT fuero
FROM Juzgado
ORDER BY fuero ASC
";

$resultadoFueros = $conn->query($sqlFueros);

while($fila = $resultadoFueros->fetch_assoc()){
    $fueros[] = $fila["fuero"];
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Expedientes | Sistema Judicial</title>

<link rel="stylesheet" href="css/expedientes.css">
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

    <a href="index.php" class="btn-inicio">
        Inicio
    </a>

</header>

<nav class="navbar">

    <a href="index.php">Inicio</a>

    <a href="expedientes.php" class="activo">
        Expedientes
    </a>

    <a href="#">
        Clientes
    </a>

    <a href="#">
        Abogados
    </a>

    <a href="#">
        Juzgados
    </a>

    <a href="#">
        Audiencias
    </a>

    <a href="#">
        Reportes
    </a>

</nav>

<main class="contenedor">

    <div class="encabezado">

        <div>
            <h2>Expedientes</h2>

            <p>
                Administración y seguimiento de los casos judiciales.
            </p>
        </div>

        <a href="crear_expediente.php" class="btn-principal">
            + Nuevo expediente
        </a>

    </div>

    <section class="panel-busqueda">

        <form method="GET" action="expedientes.php">

            <div class="campo-busqueda">

                <label for="busqueda">
                    Buscar expediente
                </label>

                <input
                    type="text"
                    id="busqueda"
                    name="busqueda"
                    value="<?php echo htmlspecialchars($busqueda); ?>"
                    placeholder="Número, carátula, cliente o juzgado..."
                >

            </div>

            <div class="campo">

                <label for="fuero">
                    Fuero
                </label>

                <select name="fuero" id="fuero">

                    <option value="">
                        Todos los fueros
                    </option>

                    <?php foreach($fueros as $f): ?>

                        <option
                            value="<?php echo htmlspecialchars($f); ?>"
                            <?php echo $fuero == $f ? "selected" : ""; ?>
                        >
                            <?php echo htmlspecialchars($f); ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <button type="submit" class="btn-buscar">
                Buscar
            </button>

            <a href="expedientes.php" class="btn-limpiar">
                Limpiar
            </a>

        </form>

    </section>

    <section class="barra-resultados">

        <div>
            <strong>
                <?php echo count($expedientes); ?>
            </strong>

            expedientes encontrados
        </div>

        <a href="crear_expediente.php" class="accion-rapida">
            Crear expediente
        </a>

    </section>

    <section class="lista-expedientes">

        <?php if(count($expedientes) == 0): ?>

            <div class="sin-resultados">

                <div class="icono-vacio">
                    📁
                </div>

                <h3>No se encontraron expedientes</h3>

                <p>
                    Probá modificar los filtros de búsqueda.
                </p>

            </div>

        <?php else: ?>

            <?php foreach($expedientes as $expediente): ?>

                <article class="tarjeta-expediente">

                    <div class="numero-expediente">

                        <span>EXPEDIENTE</span>

                        <strong>
                            <?php echo $expediente["e_codigo"]; ?>/2026
                        </strong>

                    </div>

                    <div class="datos-expediente">

                        <h3>
                            <?php echo htmlspecialchars($expediente["e_caratula"]); ?>
                        </h3>

                        <div class="datos-grid">

                            <div>
                                <span class="etiqueta">
                                    Cliente
                                </span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $expediente["c_nombre"] . " " .
                                        $expediente["c_apellido"]
                                    );
                                    ?>
                                </strong>
                            </div>

                            <div>
                                <span class="etiqueta">
                                    Juzgado
                                </span>

                                <strong>
                                    <?php echo htmlspecialchars($expediente["j_nombre"]); ?>
                                </strong>
                            </div>

                            <div>
                                <span class="etiqueta">
                                    Fuero
                                </span>

                                <strong>
                                    <?php echo htmlspecialchars($expediente["fuero"]); ?>
                                </strong>
                            </div>

                            <div>
                                <span class="etiqueta">
                                    Abogados
                                </span>

                                <strong>
                                    <?php echo $expediente["cantidad_abogados"]; ?>
                                </strong>
                            </div>

                        </div>

                        <div class="ultima-modificacion">

                            Última modificación:
                            <?php
                            echo date(
                                "d/m/Y H:i",
                                strtotime($expediente["ultima_modificacion"])
                            );
                            ?>

                        </div>

                    </div>

                    <div class="acciones">

                        <a
                            href="ver_expediente.php?id=<?php echo $expediente["e_codigo"]; ?>"
                            class="btn-ver"
                        >
                            Ver
                        </a>

                        <a
                            href="editar_expediente.php?id=<?php echo $expediente["e_codigo"]; ?>"
                            class="btn-editar"
                        >
                            Editar
                        </a>

                        <a
                            href="eliminar_expediente.php?id=<?php echo $expediente["e_codigo"]; ?>"
                            class="btn-eliminar"
                            onclick="return confirm('¿Está seguro de eliminar este expediente?');"
                        >
                            Eliminar
                        </a>

                    </div>

                </article>

            <?php endforeach; ?>

        <?php endif; ?>

    </section>

</main>

<footer class="footer">

    Sistema de Gestión Judicial

</footer>

</body>
</html>
<?php

include("conexion.php");

$id = intval($_GET["id"] ?? 0);

if($id <= 0){
    die("Expediente inválido.");
}

$sql = "
SELECT
    e.e_codigo,
    e.e_caratula,
    e.ultima_modificacion,
    c.c_codigo,
    c.c_nombre,
    c.c_apellido,
    c.c_calle,
    c.c_numero,
    c.c_piso,
    c.c_depto,
    j.j_codigo,
    j.j_nombre,
    j.j_nombre_juez,
    j.j_apellido_juez,
    j.j_calle,
    j.j_numero,
    j.j_piso,
    j.j_depto,
    j.fuero
FROM Expediente e
INNER JOIN Cliente c
    ON e.c_codigo = c.c_codigo
INNER JOIN Juzgado j
    ON e.j_codigo = j.j_codigo
WHERE e.e_codigo = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$expediente = $stmt->get_result()->fetch_assoc();

$stmt->close();

if(!$expediente){
    die("El expediente no existe.");
}

$sql = "
SELECT
    a.a_codigo,
    a.a_nombre,
    a.a_apellido,
    a.a_matricula,
    ae.fecha_asignacion
FROM Abogado_Expediente ae
INNER JOIN Abogado a
    ON ae.a_codigo = a.a_codigo
WHERE ae.e_codigo = ?
ORDER BY a.a_apellido ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$resultadoAbogados = $stmt->get_result();

$abogados = [];

while($fila = $resultadoAbogados->fetch_assoc()){
    $abogados[] = $fila;
}

$stmt->close();

$creado = isset($_GET["creado"]);

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
Expediente <?php echo $expediente["e_codigo"]; ?>
</title>

<link rel="stylesheet" href="css/ver_expediente.css">

</head>

<body>

<header class="header">

    <div class="logo">

        <div class="logo-icon">
            ⚖
        </div>

        <div>
            <h1>PODER JUDICIAL</h1>
            <span>Sistema de Gestión Judicial</span>
        </div>

    </div>

    <a href="expedientes.php" class="btn-volver">
        ← Expedientes
    </a>

</header>

<main class="contenedor">

    <?php if($creado): ?>

        <div class="mensaje-exito">
            El expediente fue creado correctamente.
        </div>

    <?php endif; ?>

    <div class="encabezado">

        <div>

            <span class="numero">
                EXPEDIENTE #<?php echo $expediente["e_codigo"]; ?>/2026
            </span>

            <h2>
                <?php echo htmlspecialchars($expediente["e_caratula"]); ?>
            </h2>

        </div>

        <div class="acciones">

            <a
                href="editar_expediente.php?id=<?php echo $id; ?>"
                class="btn-editar"
            >
                Editar
            </a>

            <a
                href="eliminar_expediente.php?id=<?php echo $id; ?>"
                class="btn-eliminar"
                onclick="return confirm('¿Eliminar este expediente?');"
            >
                Eliminar
            </a>

        </div>

    </div>

    <section class="panel">

        <h3>Información general</h3>

        <div class="grid">

            <div class="dato">

                <span>Cliente</span>

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $expediente["c_nombre"] . " " .
                        $expediente["c_apellido"]
                    );
                    ?>
                </strong>

                <small>
                    Código:
                    <?php echo $expediente["c_codigo"]; ?>
                </small>

            </div>

            <div class="dato">

                <span>Juzgado</span>

                <strong>
                    <?php echo htmlspecialchars($expediente["j_nombre"]); ?>
                </strong>

                <small>
                    Código:
                    <?php echo $expediente["j_codigo"]; ?>
                </small>

            </div>

            <div class="dato">

                <span>Fuero</span>

                <strong>
                    <?php echo htmlspecialchars($expediente["fuero"]); ?>
                </strong>

            </div>

            <div class="dato">

                <span>Última modificación</span>

                <strong>
                    <?php
                    echo date(
                        "d/m/Y H:i",
                        strtotime($expediente["ultima_modificacion"])
                    );
                    ?>
                </strong>

            </div>

        </div>

    </section>

    <div class="columnas">

        <section class="panel">

            <h3>Cliente</h3>

            <div class="persona">

                <div class="avatar">
                    <?php echo strtoupper(substr($expediente["c_nombre"],0,1)); ?>
                </div>

                <div>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $expediente["c_nombre"] . " " .
                            $expediente["c_apellido"]
                        );
                        ?>
                    </strong>

                    <span>
                        Código:
                        <?php echo $expediente["c_codigo"]; ?>
                    </span>

                    <span>
                        <?php
                        echo htmlspecialchars(
                            $expediente["c_calle"] . " " .
                            $expediente["c_numero"]
                        );
                        ?>
                    </span>

                </div>

            </div>

        </section>

        <section class="panel">

            <h3>Juzgado</h3>

            <div class="juzgado">

                <strong>
                    <?php echo htmlspecialchars($expediente["j_nombre"]); ?>
                </strong>

                <span>
                    Juez:
                    <?php
                    echo htmlspecialchars(
                        $expediente["j_nombre_juez"] . " " .
                        $expediente["j_apellido_juez"]
                    );
                    ?>
                </span>

                <span>
                    Fuero:
                    <?php echo htmlspecialchars($expediente["fuero"]); ?>
                </span>

            </div>

        </section>

    </div>

    <section class="panel">

        <div class="panel-titulo">

            <div>

                <h3>Abogados asignados</h3>

                <span>
                    <?php echo count($abogados); ?>
                    abogado(s) asociado(s)
                </span>

            </div>

        </div>

        <div class="abogados">

            <?php if(count($abogados) == 0): ?>

                <p>
                    No hay abogados asignados.
                </p>

            <?php else: ?>

                <?php foreach($abogados as $abogado): ?>

                    <div class="abogado">

                        <div class="avatar">
                            <?php echo strtoupper(substr($abogado["a_nombre"],0,1)); ?>
                        </div>

                        <div>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $abogado["a_nombre"] . " " .
                                    $abogado["a_apellido"]
                                );
                                ?>
                            </strong>

                            <span>
                                Matrícula:
                                <?php echo $abogado["a_matricula"]; ?>
                            </span>

                            <small>
                                Asignado:
                                <?php
                                echo date(
                                    "d/m/Y H:i",
                                    strtotime($abogado["fecha_asignacion"])
                                );
                                ?>
                            </small>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </section>

</main>

</body>
</html>
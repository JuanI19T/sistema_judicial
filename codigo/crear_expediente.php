<?php

include("conexion.php");

$mensaje = "";
$tipoMensaje = "";

$caratula = trim($_POST["caratula"] ?? "");
$c_codigo = intval($_POST["c_codigo"] ?? 0);
$fuero = trim($_POST["fuero"] ?? "");
$j_codigo = intval($_POST["j_codigo"] ?? 0);

$abogadosSeleccionados = $_POST["abogados"] ?? [];

if(!is_array($abogadosSeleccionados)){
    $abogadosSeleccionados = [];
}

$clienteSeleccionado = null;
$juzgadoSorteado = null;

$clientes = [];
$abogados = [];
$fueros = [];

$sql = "
SELECT
    c_codigo,
    c_nombre,
    c_apellido
FROM Cliente
ORDER BY c_apellido ASC, c_nombre ASC
";

$resultado = $conn->query($sql);

while($fila = $resultado->fetch_assoc()){
    $clientes[] = $fila;
}

$sql = "
SELECT
    a_codigo,
    a_nombre,
    a_apellido,
    a_matricula
FROM Abogado
ORDER BY a_apellido ASC, a_nombre ASC
";

$resultado = $conn->query($sql);

while($fila = $resultado->fetch_assoc()){
    $abogados[] = $fila;
}

$sql = "
SELECT DISTINCT fuero
FROM Juzgado
ORDER BY fuero ASC
";

$resultado = $conn->query($sql);

while($fila = $resultado->fetch_assoc()){
    $fueros[] = $fila["fuero"];
}

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["sortear"])){

    if($fuero == ""){
        $mensaje = "Debe seleccionar un fuero antes de sortear el juzgado.";
        $tipoMensaje = "error";
    }else{

        $sql = "
        SELECT
            j.j_codigo,
            j.j_nombre,
            j.j_apellido_juez,
            j.j_nombre_juez,
            j.fuero,
            COUNT(e.e_codigo) AS cantidad_expedientes
        FROM Juzgado j
        LEFT JOIN Expediente e
            ON j.j_codigo = e.j_codigo
        WHERE j.fuero = ?
        GROUP BY
            j.j_codigo,
            j.j_nombre,
            j.j_apellido_juez,
            j.j_nombre_juez,
            j.fuero
        ORDER BY cantidad_expedientes ASC, j.j_codigo ASC
        LIMIT 1
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $fuero);
        $stmt->execute();

        $resultado = $stmt->get_result();

        $juzgadoSorteado = $resultado->fetch_assoc();

        $stmt->close();

        if($juzgadoSorteado){

            $j_codigo = $juzgadoSorteado["j_codigo"];

            $mensaje = "Juzgado sorteado correctamente.";
            $tipoMensaje = "exito";

        }else{

            $mensaje = "No existen juzgados disponibles para el fuero seleccionado.";
            $tipoMensaje = "error";
        }
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["crear"])){

    $caratula = trim($_POST["caratula"] ?? "");
    $c_codigo = intval($_POST["c_codigo"] ?? 0);
    $fuero = trim($_POST["fuero"] ?? "");
    $j_codigo = intval($_POST["j_codigo"] ?? 0);
    $abogadosSeleccionados = $_POST["abogados"] ?? [];

    if(!is_array($abogadosSeleccionados)){
        $abogadosSeleccionados = [];
    }

    if($caratula == "" || $c_codigo <= 0 || $fuero == ""){
        $mensaje = "Complete todos los campos obligatorios.";
        $tipoMensaje = "error";
    }elseif(count($abogadosSeleccionados) == 0){
        $mensaje = "Debe seleccionar al menos un abogado.";
        $tipoMensaje = "error";
    }elseif(count($abogadosSeleccionados) > 2){
        $mensaje = "Un expediente puede tener como máximo dos abogados.";
        $tipoMensaje = "error";
    }else{

        // Volvemos a sortear en el servidor para evitar manipulaciones.
        $sql = "
        SELECT
            j.j_codigo
        FROM Juzgado j
        LEFT JOIN Expediente e
            ON j.j_codigo = e.j_codigo
        WHERE j.fuero = ?
        GROUP BY j.j_codigo
        ORDER BY COUNT(e.e_codigo) ASC, j.j_codigo ASC
        LIMIT 1
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $fuero);
        $stmt->execute();

        $resultado = $stmt->get_result();

        $juzgado = $resultado->fetch_assoc();

        $stmt->close();

        if(!$juzgado){

            $mensaje = "No se encontró un juzgado disponible.";
            $tipoMensaje = "error";

        }else{

            $j_codigo = intval($juzgado["j_codigo"]);

            $conn->begin_transaction();

            try{

                // Crear expediente.
                $sql = "
                INSERT INTO Expediente
                (c_codigo,j_codigo,e_caratula)
                VALUES(?,?,?)
                ";

                $stmt = $conn->prepare($sql);

                $stmt->bind_param(
                    "iis",
                    $c_codigo,
                    $j_codigo,
                    $caratula
                );

                $stmt->execute();

                $idExpediente = $conn->insert_id;

                $stmt->close();

                // Asociar abogados.
                foreach($abogadosSeleccionados as $idAbogado){

                    $idAbogado = intval($idAbogado);

                    $sql = "
                    INSERT INTO Abogado_Expediente
                    (a_codigo,e_codigo,fecha_asignacion)
                    VALUES(?,?,NOW())
                    ";

                    $stmt = $conn->prepare($sql);

                    $stmt->bind_param(
                        "ii",
                        $idAbogado,
                        $idExpediente
                    );

                    $stmt->execute();

                    $stmt->close();
                }

                $conn->commit();

                header(
                    "Location: ver_expediente.php?id=" .
                    $idExpediente .
                    "&creado=1"
                );

                exit();

            }catch(Exception $e){

                $conn->rollback();

                $mensaje = "No se pudo crear el expediente: " . $e->getMessage();
                $tipoMensaje = "error";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Nuevo expediente | Sistema Judicial</title>

<link rel="stylesheet" href="css/crear_expediente.css">

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

    <a href="expedientes.php" class="btn-volver">
        ← Expedientes
    </a>

</header>

<main class="contenedor">

    <div class="titulo">

        <h2>Nuevo expediente</h2>

        <p>
            Registrar un nuevo caso judicial.
        </p>

    </div>

    <?php if($mensaje != ""): ?>

        <div class="mensaje <?php echo $tipoMensaje; ?>">

            <?php echo htmlspecialchars($mensaje); ?>

        </div>

    <?php endif; ?>

    <form method="POST">

        <section class="panel">

            <h3>Información del expediente</h3>

            <div class="campo">

                <label for="caratula">
                    Carátula *
                </label>

                <input
                    type="text"
                    id="caratula"
                    name="caratula"
                    maxlength="255"
                    value="<?php echo htmlspecialchars($caratula); ?>"
                    placeholder="Ej.: Pérez c/ González s/ daños y perjuicios"
                    required
                >

            </div>

            <div class="fila">

                <div class="campo">

                    <label for="c_codigo">
                        Cliente *
                    </label>

                    <select
                        name="c_codigo"
                        id="c_codigo"
                        required
                    >

                        <option value="">
                            Seleccionar cliente
                        </option>

                        <?php foreach($clientes as $cliente): ?>

                            <option
                                value="<?php echo $cliente["c_codigo"]; ?>"
                                <?php echo $c_codigo == $cliente["c_codigo"] ? "selected" : ""; ?>
                            >

                                <?php
                                echo htmlspecialchars(
                                    $cliente["c_apellido"] . ", " .
                                    $cliente["c_nombre"]
                                );
                                ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="campo">

                    <label for="fuero">
                        Fuero *
                    </label>

                    <select
                        name="fuero"
                        id="fuero"
                        required
                    >

                        <option value="">
                            Seleccionar fuero
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

            </div>

        </section>

        <section class="panel">

            <h3>Abogados asignados</h3>

            <p class="ayuda">
                Se pueden asignar uno o dos abogados como defensores del expediente.
            </p>

            <div class="abogados">

                <?php foreach($abogados as $abogado): ?>

                    <label class="abogado">

                        <input
                            type="checkbox"
                            name="abogados[]"
                            value="<?php echo $abogado["a_codigo"]; ?>"
                            <?php
                            echo in_array(
                                $abogado["a_codigo"],
                                $abogadosSeleccionados
                            ) ? "checked" : "";
                            ?>
                        >

                        <span>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $abogado["a_nombre"] . " " .
                                    $abogado["a_apellido"]
                                );
                                ?>
                            </strong>

                            <small>
                                Matrícula:
                                <?php echo $abogado["a_matricula"]; ?>
                            </small>

                        </span>

                    </label>

                <?php endforeach; ?>

            </div>

        </section>

        <section class="panel">

            <h3>Juzgado</h3>

            <p class="ayuda">
                El juzgado no se selecciona manualmente.
                El sistema lo sortea según el fuero y la cantidad de expedientes.
            </p>

            <div class="sorteo">

                <div class="juzgado-resultante">

                    <?php if($juzgadoSorteado): ?>

                        <span>Juzgado asignado</span>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $juzgadoSorteado["j_nombre"]
                            );
                            ?>
                        </strong>

                        <small>
                            Juez:
                            <?php
                            echo htmlspecialchars(
                                $juzgadoSorteado["j_nombre_juez"] .
                                " " .
                                $juzgadoSorteado["j_apellido_juez"]
                            );
                            ?>
                        </small>

                    <?php else: ?>

                        <span>
                            Juzgado aún no sorteado
                        </span>

                        <strong>
                            -
                        </strong>

                    <?php endif; ?>

                </div>

                <button
                    type="submit"
                    name="sortear"
                    class="btn-sortear"
                >
                    Sortear Juzgado
                </button>

            </div>

        </section>

        <input
            type="hidden"
            name="j_codigo"
            value="<?php echo $j_codigo; ?>"
        >

        <div class="acciones">

            <a
                href="expedientes.php"
                class="btn-cancelar"
            >
                Cancelar
            </a>

            <button
                type="submit"
                name="crear"
                class="btn-crear"
                <?php echo $j_codigo <= 0 ? "disabled" : ""; ?>
            >
                Crear expediente
            </button>

        </div>

    </form>

</main>

</body>
</html>
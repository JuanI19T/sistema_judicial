<?php

include("conexion.php");

$id = intval($_GET["id"] ?? $_POST["id"] ?? 0);

if($id <= 0){
    die("Expediente inválido.");
}

$mensaje = "";
$tipoMensaje = "";

$caratula = "";
$c_codigo = 0;
$abogadosSeleccionados = [];

$sql = "
SELECT
    e_caratula,
    c_codigo
FROM Expediente
WHERE e_codigo = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$expediente = $stmt->get_result()->fetch_assoc();

$stmt->close();

if(!$expediente){
    die("El expediente no existe.");
}

$caratula = $expediente["e_caratula"];
$c_codigo = $expediente["c_codigo"];

$sql = "
SELECT a_codigo
FROM Abogado_Expediente
WHERE e_codigo = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();

while($fila = $resultado->fetch_assoc()){
    $abogadosSeleccionados[] = $fila["a_codigo"];
}

$stmt->close();

$clientes = [];
$abogados = [];

$sql = "
SELECT
    c_codigo,
    c_nombre,
    c_apellido
FROM Cliente
ORDER BY c_apellido ASC
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
ORDER BY a_apellido ASC
";

$resultado = $conn->query($sql);

while($fila = $resultado->fetch_assoc()){
    $abogados[] = $fila;
}

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["guardar"])){

    $caratula = trim($_POST["caratula"] ?? "");
    $c_codigo = intval($_POST["c_codigo"] ?? 0);
    $abogadosSeleccionados = $_POST["abogados"] ?? [];

    if(!is_array($abogadosSeleccionados)){
        $abogadosSeleccionados = [];
    }

    if($caratula == "" || $c_codigo <= 0){

        $mensaje = "Complete los campos obligatorios.";
        $tipoMensaje = "error";

    }elseif(count($abogadosSeleccionados) == 0){

        $mensaje = "Debe existir al menos un abogado.";
        $tipoMensaje = "error";

    }elseif(count($abogadosSeleccionados) > 2){

        $mensaje = "Un expediente puede tener como máximo dos abogados.";
        $tipoMensaje = "error";

    }else{

        $conn->begin_transaction();

        try{

            // Actualizar datos principales.
            $sql = "
            UPDATE Expediente
            SET
                c_codigo = ?,
                e_caratula = ?,
                ultima_modificacion = NOW()
            WHERE e_codigo = ?
            ";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "isi",
                $c_codigo,
                $caratula,
                $id
            );

            $stmt->execute();

            $stmt->close();

            // Eliminar las relaciones anteriores.
            $sql = "
            DELETE FROM Abogado_Expediente
            WHERE e_codigo = ?
            ";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            // Crear nuevamente las relaciones.
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
                    $id
                );

                $stmt->execute();
                $stmt->close();
            }

            $conn->commit();

            header(
                "Location: ver_expediente.php?id=" .
                $id .
                "&actualizado=1"
            );

            exit();

        }catch(Exception $e){

            $conn->rollback();

            $mensaje = "No se pudo actualizar el expediente.";
            $tipoMensaje = "error";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Editar expediente</title>

<link rel="stylesheet" href="css/editar_expediente.css">

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

    <a href="ver_expediente.php?id=<?php echo $id; ?>" class="volver">
        ← Cancelar
    </a>

</header>

<main class="contenedor">

    <div class="titulo">

        <span>
            Expediente #<?php echo $id; ?>/2026
        </span>

        <h2>Editar expediente</h2>

    </div>

    <?php if($mensaje != ""): ?>

        <div class="mensaje <?php echo $tipoMensaje; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <input
            type="hidden"
            name="id"
            value="<?php echo $id; ?>"
        >

        <section class="panel">

            <h3>Datos principales</h3>

            <div class="campo">

                <label>
                    Carátula
                </label>

                <input
                    type="text"
                    name="caratula"
                    maxlength="255"
                    value="<?php echo htmlspecialchars($caratula); ?>"
                    required
                >

            </div>

            <div class="campo">

                <label>
                    Cliente
                </label>

                <select name="c_codigo" required>

                    <?php foreach($clientes as $cliente): ?>

                        <option
                            value="<?php echo $cliente["c_codigo"]; ?>"
                            <?php echo
                            $c_codigo == $cliente["c_codigo"]
                            ? "selected"
                            : "";
                            ?>
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

        </section>

        <section class="panel">

            <h3>Abogados</h3>

            <p>
                Seleccione uno o dos abogados.
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
                            )
                            ? "checked"
                            : "";
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

        <div class="acciones">

            <a
                href="ver_expediente.php?id=<?php echo $id; ?>"
                class="cancelar"
            >
                Cancelar
            </a>

            <button
                type="submit"
                name="guardar"
                class="guardar"
            >
                Guardar cambios
            </button>

        </div>

    </form>

</main>

</body>
</html>

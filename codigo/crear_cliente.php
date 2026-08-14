<?php

include("conexion.php");

$mensaje = "";
$tipoMensaje = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $nombre = trim($_POST["nombre"] ?? "");
    $apellido = trim($_POST["apellido"] ?? "");
    $calle = trim($_POST["calle"] ?? "");
    $numero = trim($_POST["numero"] ?? "");
    $piso = trim($_POST["piso"] ?? "");
    $depto = trim($_POST["depto"] ?? "");

    if(
        empty($nombre) ||
        empty($apellido) ||
        empty($calle) ||
        empty($numero)
    ){

        $mensaje = "Complete todos los campos obligatorios.";
        $tipoMensaje = "error";

    }elseif(!is_numeric($numero)){

        $mensaje = "El número de calle debe ser numérico.";
        $tipoMensaje = "error";

    }else{

        $pisoValor = $piso !== "" ? intval($piso) : null;
        $deptoValor = $depto !== "" ? $depto : null;

        $sql = "
            INSERT INTO Cliente
            (
                c_nombre,
                c_apellido,
                c_calle,
                c_numero,
                c_piso,
                c_depto
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        $stmt = $conn->prepare($sql);

        $numero = intval($numero);

        $stmt->bind_param(
            "sssiss",
            $nombre,
            $apellido,
            $calle,
            $numero,
            $pisoValor,
            $deptoValor
        );

        if($stmt->execute()){

            $idNuevo = $conn->insert_id;

            header(
                "Location: ver_cliente.php?id=" . $idNuevo . "&creado=1"
            );

            exit();

        }else{

            $mensaje = "No se pudo registrar el cliente.";
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

<title>Nuevo cliente | Sistema Judicial</title>

<link rel="stylesheet" href="css/crear_cliente.css">

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

            <h2>Nuevo cliente</h2>

            <p>
                Registrar un nuevo cliente en el sistema
            </p>

        </div>

        <a href="clientes.php" class="btn-secundario">
            ← Volver
        </a>

    </div>

    <section class="formulario-panel">

        <div class="formulario-titulo">

            <h3>Datos del cliente</h3>

            <p>
                Los campos marcados con <strong>*</strong> son obligatorios.
            </p>

        </div>

        <?php if($mensaje != ""){ ?>

            <div class="mensaje <?php echo $tipoMensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>

        <?php } ?>

        <form method="POST">

            <div class="form-grid">

                <div class="campo">

                    <label>
                        Nombre *
                    </label>

                    <input
                        type="text"
                        name="nombre"
                        required
                        maxlength="50"
                        value="<?php echo htmlspecialchars($_POST["nombre"] ?? ""); ?>"
                    >

                </div>

                <div class="campo">

                    <label>
                        Apellido *
                    </label>

                    <input
                        type="text"
                        name="apellido"
                        required
                        maxlength="50"
                        value="<?php echo htmlspecialchars($_POST["apellido"] ?? ""); ?>"
                    >

                </div>

                <div class="campo campo-completo">

                    <label>
                        Calle *
                    </label>

                    <input
                        type="text"
                        name="calle"
                        required
                        maxlength="100"
                        value="<?php echo htmlspecialchars($_POST["calle"] ?? ""); ?>"
                    >

                </div>

                <div class="campo">

                    <label>
                        Número *
                    </label>

                    <input
                        type="number"
                        name="numero"
                        required
                        min="1"
                        value="<?php echo htmlspecialchars($_POST["numero"] ?? ""); ?>"
                    >

                </div>

                <div class="campo">

                    <label>
                        Piso
                    </label>

                    <input
                        type="number"
                        name="piso"
                        min="0"
                        value="<?php echo htmlspecialchars($_POST["piso"] ?? ""); ?>"
                    >

                </div>

                <div class="campo">

                    <label>
                        Departamento
                    </label>

                    <input
                        type="text"
                        name="depto"
                        maxlength="10"
                        value="<?php echo htmlspecialchars($_POST["depto"] ?? ""); ?>"
                    >

                </div>

            </div>

            <div class="acciones-formulario">

                <a
                    href="clientes.php"
                    class="btn-cancelar"
                >
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="btn-guardar"
                >
                    Guardar cliente
                </button>

            </div>

        </form>

    </section>

</main>

</body>
</html>
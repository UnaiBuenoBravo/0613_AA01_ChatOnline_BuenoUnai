<?php
session_start();
if (!isset($_SESSION['Usuario'])) {
    header('Location: ./login.php');
    exit();
}

include_once '../includes/conexion.php';

$idUser = (int)$_SESSION['IdUser'];

// Errores, datos escritos y avisos que deja script/valPerfil.php.
// Se borran al leerlos para que no vuelvan a salir al recargar.
$errores   = $_SESSION['errores_perfil'] ?? [];
$datosForm = $_SESSION['datos_perfil']   ?? [];
$ok        = $_SESSION['ok_perfil']      ?? [];
unset($_SESSION['errores_perfil'], $_SESSION['datos_perfil'], $_SESSION['ok_perfil']);

// Datos actuales del usuario
try {
    $st = $conn->prepare("SELECT Username, Nombre, Apellido, Email, FotoPerfil
                          FROM Tbl_Usuario WHERE IdUser = :id");
    $st->execute([':id' => $idUser]);
    $u = $st->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error perfil: ' . $e->getMessage());
    header('Location: ./home.php');
    exit();
}

if (!$u) {
    header('Location: ./login.php');
    exit();
}

// ---------------------------------------------------------------
// Funciones para pintar
// ---------------------------------------------------------------
if (!function_exists('e')) {
    function e($t) {
        return htmlspecialchars((string)$t, ENT_QUOTES, 'UTF-8');
    }
}

// <option> de un desplegable, marcando el actual
function opciones($lista, $actual) {
    $html = '';
    foreach ($lista as $valor => $texto) {
        $sel = ((string)$valor === (string)$actual) ? ' selected' : '';
        $html .= '<option value="' . e($valor) . '"' . $sel . '>' . e($texto) . '</option>';
    }
    return $html;
}

$foto = $u['FotoPerfil'] ? e($u['FotoPerfil']) : '../img/perfil.jpg';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <!-- Link material Symbols -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=home" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="../img/logo.png" type="image/x-icon">
    <title>Perfil</title>
</head>
<body class="pagina-ajustes">
    <header>
        <section id="logo_peque">
            <img src="../img/logo.png" alt="Logo">
        </section>
        <section id="username">
            <p><?php echo e($_SESSION['Usuario']); ?></p>
            <img src="<?php echo $foto; ?>" alt="Foto de perfil">
        </section>

        <section id="ajustes">
            <a href="./home.php" aria-label="Volver al chat"><span class="material-symbols-outlined">home</span></a>
            <a class="logout" href="../script/logout.php">Cerrar Sesión</a>
        </section>
    </header>
    <?php
    // Lo que se ve en cada campo: lo que escribió (si hubo error) o lo guardado en la BBDD
    $vUsername = $datosForm['username'] ?? $u['Username'];
    $vNombre   = $datosForm['nombre']   ?? $u['Nombre'];
    $vApellido = $datosForm['apellido'] ?? $u['Apellido'];
    $vEmail    = $datosForm['email']    ?? $u['Email'];
    ?>
    <main>
        <!-- ============ Configuración general ============ -->
        <section id="perfil">
            <form action="../script/valPerfil.php" method="post" id="formGeneral" novalidate>
                <input type="hidden" name="form" value="general">
                <h2>Configuración general</h2>
                <img id="fotoPerfil" src="<?php echo $foto; ?>" alt="Foto de perfil">

                <div class="campo">
                    <label for="inputUsername">Nombre de usuario</label>
                    <input type="text" id="inputUsername" name="username" value="<?php echo e($vUsername); ?>">
                    <p class="error"><?php echo e($errores['username'] ?? ''); ?></p>
                </div>
                <div class="campo">
                    <label for="inputName">Nombre</label>
                    <input type="text" id="inputName" name="nombre" value="<?php echo e($vNombre); ?>">
                    <p class="error"><?php echo e($errores['nombre'] ?? ''); ?></p>
                </div>
                <div class="campo">
                    <label for="inputApellido">Apellido</label>
                    <input type="text" id="inputApellido" name="apellido" value="<?php echo e($vApellido); ?>">
                    <p class="error"><?php echo e($errores['apellido'] ?? ''); ?></p>
                </div>
                <div class="campo">
                    <label for="inputEmail">Email</label>
                    <input type="email" id="inputEmail" name="email" value="<?php echo e($vEmail); ?>">
                    <p class="error"><?php echo e($errores['email'] ?? ''); ?></p>
                </div>

                <?php if (isset($errores['bd_general'])) { ?>
                    <p class="error"><?php echo e($errores['bd_general']); ?></p>
                <?php } ?>
                <?php if (isset($ok['general'])) { ?>
                    <p class="ok"><?php echo e($ok['general']); ?></p>
                <?php } ?>

                <input type="submit" value="Cambiar configuración">
            </form>
        </section>

        <!-- ============ Seguridad ============ -->
        <section id="seguridad">
            <form action="../script/valPerfil.php" method="post" id="formCon" novalidate>
                <input type="hidden" name="form" value="password">
                <h2>Configuración de seguridad</h2>

                <div class="campo">
                    <label for="oldCon">Escribe tu antigua contraseña</label>
                    <input type="password" id="oldCon" name="oldCon" placeholder="*******" autocomplete="current-password">
                    <p class="error"><?php echo e($errores['oldCon'] ?? ''); ?></p>
                </div>
                <div class="campo">
                    <label for="newCon">Escribe tu nueva contraseña</label>
                    <input type="password" id="newCon" name="newCon" placeholder="Nueva contraseña" autocomplete="new-password">
                    <p class="error"><?php echo e($errores['newCon'] ?? ''); ?></p>
                </div>
                <div class="campo">
                    <label for="repNewCon">Vuelve a escribir tu nueva contraseña</label>
                    <input type="password" id="repNewCon" name="repNewCon" placeholder="Nueva contraseña" autocomplete="new-password">
                    <p class="error"><?php echo e($errores['repNewCon'] ?? ''); ?></p>
                </div>

                <?php if (isset($errores['bd_password'])) { ?>
                    <p class="error"><?php echo e($errores['bd_password']); ?></p>
                <?php } ?>
                <?php if (isset($ok['password'])) { ?>
                    <p class="ok"><?php echo e($ok['password']); ?></p>
                <?php } ?>

                <input type="submit" value="Cambiar contraseña">
            </form>
        </section>

    </main>
</body>
</html>
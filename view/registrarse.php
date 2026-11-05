<?php
session_start();

// Si venimos de validaciones.php con errores, los recogemos de la sesión.
// Después los borramos para que no vuelvan a salir al recargar la página.
$errores = $_SESSION['errores'] ?? [];
$datos   = $_SESSION['datos']   ?? ['username' => '', 'nombre' => '', 'apellido' => '', 'email' => ''];
unset($_SESSION['errores'], $_SESSION['datos']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="../img/logo.png" type="image/x-icon">
    <title>Registrarse</title>
</head>
<body class="pagina-login">
    <header>
        <section id="logo_peque">
            <img src="../img/logo.png" alt="Logo">
        </section>
    </header>
    <main>
        <section id="login">
            <!-- novalidate: la validación la hacemos nosotros (JS y PHP) -->
            <form action="../script/validaciones.php" method="post" id="formRegistro" novalidate>
                <h2>Crear cuenta</h2>
                <p class="bienvenida">Únete a ChatOnline.</p>

                <div class="fila">
                    <div class="campo">
                        <label for="inputNombre">Nombre</label>
                        <input type="text" id="inputNombre" onblur="comprobarNombre()" name="name" placeholder="Tu nombre"
                               value="<?php echo htmlspecialchars($datos['nombre']); ?>"
                               class="<?php echo isset($errores['nombre']) ? 'invalido' : ''; ?>">
                        <p class="error" id="errorNombre"><?php echo htmlspecialchars($errores['nombre'] ?? ''); ?></p>
                    </div>
                    <div class="campo">
                        <label for="inputApellido">Apellido</label>
                        <input type="text" id="inputApellido" onblur="comprobarApellido()" name="apellido" placeholder="Tu apellido"
                               value="<?php echo htmlspecialchars($datos['apellido']); ?>"
                               class="<?php echo isset($errores['apellido']) ? 'invalido' : ''; ?>">
                        <p class="error" id="errorApellido"><?php echo htmlspecialchars($errores['apellido'] ?? ''); ?></p>
                    </div>
                </div>

                <div class="campo">
                    <label for="inputUsername">Nombre de usuario</label>
                    <input type="text" id="inputUsername" onblur="comprobarUsername()" name="username" placeholder="Tu nombre de usuario"
                           value="<?php echo htmlspecialchars($datos['username']); ?>"
                           class="<?php echo isset($errores['username']) ? 'invalido' : ''; ?>">
                    <p class="error" id="errorUsername"><?php echo htmlspecialchars($errores['username'] ?? ''); ?></p>
                </div>

                <div class="campo">
                    <label for="inputEmail">Email</label>
                    <input type="email" id="inputEmail" onblur="comprobarEmail()" name="email" placeholder="tucorreo@ejemplo.com"
                           value="<?php echo htmlspecialchars($datos['email']); ?>"
                           class="<?php echo isset($errores['email']) ? 'invalido' : ''; ?>">
                    <p class="error" id="errorEmail"><?php echo htmlspecialchars($errores['email'] ?? ''); ?></p>
                </div>

                <div class="campo">
                    <label for="inputPasswd">Contraseña</label>
                    <input type="password" id="inputPasswd" onblur="comprobarPasswd()" name="passwd" placeholder="*******"
                           class="<?php echo isset($errores['passwd']) ? 'invalido' : ''; ?>">
                    <p class="error" id="errorPasswd"><?php echo htmlspecialchars($errores['passwd'] ?? ''); ?></p>
                </div>

                <div class="campo">
                    <label for="inputRepCon">Repetir contraseña</label>
                    <input type="password" id="inputRepCon" onblur="comprobarRepCon()" name="repCon" placeholder="*******"
                           class="<?php echo isset($errores['repCon']) ? 'invalido' : ''; ?>">
                    <p class="error" id="errorRepCon"><?php echo htmlspecialchars($errores['repCon'] ?? ''); ?></p>
                </div>

                <input type="submit" name="registrar" onclick="return validarFormulario()" value="Crear cuenta">

                <p class="pie_form">¿Ya tienes cuenta? <a href="./login.php">Inicia sesión</a></p>
            </form>

            <?php if (isset($errores['general'])) { ?>
                <!-- Problema con la BBDD o la conexión -->
                <p class="mensaje-error"><?php echo htmlspecialchars($errores['general']); ?></p>
            <?php } ?>
        </section>
    </main>

    <script src="../js/validaciones.js"></script>
</body>
</html>
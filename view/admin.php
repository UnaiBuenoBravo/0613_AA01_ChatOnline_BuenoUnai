<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="../img/logo.png" type="image/x-icon">
    <title>Iniciar sesión</title>
</head>
<body class="pagina-login">
    <header>
        <section id="logo_peque">
            <img src="../img/logo.png" alt="Logo">
        </section>
    </header>
    <main>
        <section id="login">
            <form action="../script/vali_admin.php" method="POST" id="formLogin">
                <h2>Iniciar sesión desde ADMINISTRADOR</h2>
                <p class="bienvenida">Panel de gestión CHATONLINE</p>
                <div class="campo" id="campoNombre">
                    <label for="inputUsername">Nombre de usuario / Correo</label>
                    <input type="text" id="inputUsername" name="username" placeholder="Tu nombre de usuario">
                </div>
                <div class="campo" id="campoCon">
                    <label for="inputCon">Contraseña</label>
                    <input type="password" class="" id="inputCon" name="con" placeholder="*******">
                </div>

                <input type="submit" name="login" value="Entrar">

                <!-- <p class="pie_form">¿Aún no tienes cuenta? <a href="./registrarse.php">Regístrate</a></p> -->
            </form>
        </section>
    </main>
    <script src="../js/script.js"></script>
</body>
</html>
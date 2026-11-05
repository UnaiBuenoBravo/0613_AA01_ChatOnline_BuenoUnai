<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="../img/logo.png" type="image/x-icon">
    <title>ChatOnline | Chatea con quien quieras</title>

    <style>
        /* Solo para la portada: reutiliza las variables de style.css */
        
    </style>
</head>
<body class="pagina-login">
    <header>
        <section id="logo_peque">
            <img src="../img/logo.png" alt="Logo">
        </section>
    </header>
    <main>
        <!-- Presentación + botones -->
        <section id="login" class="portada">
            <h2>ChatOnline</h2>
            <p class="bienvenida">
                Mensajería sencilla para hablar con tus amigos y tu familia.
                Añade a tus contactos, abre una conversación privada o crea un grupo,
                y personaliza la aplicación a tu gusto.
            </p>

            <div class="acciones">
                <form action="./login.php" method="get" id="formInicio">
                    <input type="submit" value="Iniciar sesión">
                </form>
                <form action="./registrarse.php" method="get" id="formRegistro">
                    <input type="submit" value="Crear cuenta">
                </form>
            </div>
        </section>

        <!-- Funcionalidades -->
        <section id="funciones" class="bloque">
            <h2>Qué puedes hacer</h2>
            <div class="rejilla">
                <article class="funcion">
                    <h3>Chats privados y grupos</h3>
                    <p>Habla uno a uno con un amigo o crea un grupo con nombre y foto. En los grupos hay administradores y miembros.</p>
                </article>

                <article class="funcion">
                    <h3>Amistades</h3>
                    <p>Envía solicitudes de amistad y acéptalas o recházalas. Solo hablas con quien tú quieres.</p>
                </article>

                <article class="funcion">
                    <h3>Mensajes sin leer</h3>
                    <p>Cada chat recuerda hasta dónde has leído y puedes vaciar una conversación para ti sin afectar a los demás.</p>
                </article>

                <article class="funcion">
                    <h3>Apodos</h3>
                    <p>Ponle a tus contactos el apodo que quieras. Solo lo ves tú.</p>
                </article>

                <article class="funcion">
                    <h3>Bloqueo de usuarios</h3>
                    <p>Bloquea a quien no quieras que te escriba y desbloquéalo cuando lo decidas.</p>
                </article>

                <article class="funcion">
                    <h3>Estado de conexión</h3>
                    <p>Mira quién está conectado y cuándo fue la última vez que se conectó cada contacto.</p>
                </article>

                <article class="funcion">
                    <h3>Tu perfil</h3>
                    <p>Elige tu nombre de usuario, cambia tu foto y gestiona tus datos desde la página de ajustes.</p>
                </article>

                <article class="funcion">
                    <h3>Ajustes a tu medida</h3>
                    <p>Tema claro u oscuro, color de los mensajes (azul, verde, rojo o morado) y fuente (Arial, Verdana o Roboto).</p>
                </article>
            </div>
        </section>

        <!-- Cómo empezar -->
        <section id="empezar" class="bloque">
            <h2>Empieza en tres pasos</h2>
            <ol class="pasos">
                <li><strong>Crea tu cuenta</strong>Regístrate con tu nombre de usuario y tu correo.</li>
                <li><strong>Añade amigos</strong>Busca a tus contactos y envíales una solicitud.</li>
                <li><strong>Empieza a chatear</strong>Abre una conversación o crea un grupo y escribe tu primer mensaje.</li>
            </ol>
            <p class="pie_form" style="margin-top: 20px;">
                ¿Ya tienes cuenta? <a href="./login.php">Inicia sesión</a>
            </p>
        </section>
    </main>
</body>
</html>
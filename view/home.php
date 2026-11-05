<?php
// die('estoy en el home.php correcto');

session_start();
if (!isset($_SESSION['Usuario'])) {
    header('Location: ./login.php');
    exit();
}

include_once '../includes/conexion.php';
include_once './chat.php';

$user   = $_SESSION['Usuario'];
$idUser = (int)$_SESSION['IdUser'];
$filtro = trim($_GET['filtro'] ?? '');

// Mensaje (error/aviso) que deja script/amistad.php
$mensaje = $_SESSION['msg_home'] ?? '';
unset($_SESSION['msg_home']);

// Chat abierto (viene de ?chat=ID) y error que deja script/mensaje.php
$idChat       = (int)($_GET['chat'] ?? 0);
$errorChat    = $_SESSION['msg_chat'] ?? '';
unset($_SESSION['msg_chat']);
$chatOk       = false;
$mensajesChat = [];

$amigos     = [];
$peticiones = [];
$resultados = [];

try {
    if ($filtro !== '') {
        // ---------------------------------------------------------
        // BÚSQUEDA: usuarios por username / nombre / apellido
        // (sin mí y sin bloqueos en ninguna dirección)
        // ---------------------------------------------------------
        $sql = "SELECT u.IdUser, u.Username, u.Nombre, u.Apellido, u.FotoPerfil,
                       am.Estado, am.IdSolicitante
                FROM tbl_usuario u
                LEFT JOIN tbl_amigos am
                       ON am.IdUser1 = LEAST(u.IdUser, :id1)
                      AND am.IdUser2 = GREATEST(u.IdUser, :id2)
                WHERE u.IdUser <> :id3
                  AND (u.Username LIKE :f1 OR u.Nombre LIKE :f2 OR u.Apellido LIKE :f3)
                  AND NOT EXISTS (
                        SELECT 1 FROM tbl_bloqueo b
                        WHERE (b.IdBloqueador = :id4 AND b.IdBloqueado = u.IdUser)
                           OR (b.IdBloqueador = u.IdUser AND b.IdBloqueado = :id5)
                  )
                ORDER BY u.Username
                LIMIT 30";
        $stmt = $conn->prepare($sql);
        $like = '%' . $filtro . '%';
        $stmt->execute([
            ':id1' => $idUser, ':id2' => $idUser, ':id3' => $idUser,
            ':id4' => $idUser, ':id5' => $idUser,
            ':f1' => $like, ':f2' => $like, ':f3' => $like,
        ]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        // ---------------------------------------------------------
        // AMIGOS ACEPTADOS + apodo + último mensaje del chat privado
        // ---------------------------------------------------------
        $sql = "SELECT u.IdUser, u.Username, u.Nombre, u.Apellido, u.FotoPerfil,
                       ap.Apodo, c.IdChat,
                       (SELECT m.Mensaje
                        FROM tbl_mensaje m
                        WHERE m.IdChat = c.IdChat
                          AND m.IdMensaje > IFNULL(p.UltimoMensajeBorrado, 0)
                        ORDER BY m.IdMensaje DESC
                        LIMIT 1) AS UltimoMensaje,
                       (SELECT COUNT(*)
                        FROM tbl_mensaje m2
                        WHERE m2.IdChat = c.IdChat
                          AND m2.IdUser <> :id6
                          AND m2.IdMensaje > GREATEST(IFNULL(p.UltimoMensajeLeido, 0),
                                                      IFNULL(p.UltimoMensajeBorrado, 0))) AS NoLeidos
                FROM tbl_amigos am
                JOIN tbl_usuario u
                  ON u.IdUser = IF(am.IdUser1 = :id1, am.IdUser2, am.IdUser1)
                LEFT JOIN tbl_apodo ap
                  ON ap.IdAutor = :id2 AND ap.IdUser = u.IdUser
                LEFT JOIN tbl_chat c
                  ON c.ClavePrivado = CONCAT(am.IdUser1, '_', am.IdUser2)
                LEFT JOIN tbl_chatparticipante p
                  ON p.IdChat = c.IdChat AND p.IdUser = :id3
                WHERE (am.IdUser1 = :id4 OR am.IdUser2 = :id5)
                  AND am.Estado = 'aceptada'
                ORDER BY u.Username";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id1' => $idUser, ':id2' => $idUser, ':id3' => $idUser,
            ':id4' => $idUser, ':id5' => $idUser, ':id6' => $idUser,
        ]);
        $amigos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // ---------------------------------------------------------
        // PETICIONES pendientes (recibidas y enviadas)
        // ---------------------------------------------------------
        $sql = "SELECT u.IdUser, u.Username, u.Nombre, u.Apellido, u.FotoPerfil,
                       am.IdSolicitante
                FROM tbl_amigos am
                JOIN tbl_usuario u
                  ON u.IdUser = IF(am.IdUser1 = :id1, am.IdUser2, am.IdUser1)
                WHERE (am.IdUser1 = :id2 OR am.IdUser2 = :id3)
                  AND am.Estado = 'pendiente'
                ORDER BY am.FechaSolicitud DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id1' => $idUser, ':id2' => $idUser, ':id3' => $idUser]);
        $peticiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ---------------------------------------------------------
    // CHAT ABIERTO: validar amistad/bloqueo y cargar mensajes
    // (fuera del if/else de $filtro, dentro del try)
    // ---------------------------------------------------------
    if ($idChat > 0) {
        $r = comprobarChat($conn, $idUser, $idChat);
        $chatOk = $r['ok'];

        if (!$chatOk) {
            $errorChat = $r['error'];   // chat atenuado y sin mensajes
        } else {
            // Solo los mensajes posteriores al último que borré
            $sql = "SELECT m.IdMensaje, m.IdUser, m.Mensaje
                    FROM tbl_mensaje m
                    JOIN tbl_chatparticipante p
                      ON p.IdChat = m.IdChat AND p.IdUser = :id1
                    WHERE m.IdChat = :chat AND m.IdMensaje > p.UltimoMensajeBorrado
                    ORDER BY m.IdMensaje ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id1' => $idUser, ':chat' => $idChat]);
            $mensajesChat = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Marcar como leído hasta el último mensaje cargado
            if (count($mensajesChat) > 0) {
                $ultimoLeido = end($mensajesChat)['IdMensaje'];
                $stmt = $conn->prepare("UPDATE tbl_chatparticipante
                                        SET UltimoMensajeLeido = :ult
                                        WHERE IdChat = :chat2 AND IdUser = :id2");
                $stmt->execute([':ult' => $ultimoLeido, ':chat2' => $idChat, ':id2' => $idUser]);
            }
        }
    }

} catch (PDOException $e) {
    error_log('Error home: ' . $e->getMessage());
    header('Location: ./login.php?err=servidor');
    exit();
}

// ---------------------------------------------------------------
// Funciones para pintar
// ---------------------------------------------------------------
function e($t) {
    return htmlspecialchars((string)$t, ENT_QUOTES, 'UTF-8');
}

function fotoUsuario($foto) {
    return $foto ? e($foto) : '../img/perfil.jpg';
}

// Mini formulario POST para cada botón (agregar, aceptar, rechazar, cancelar)
function botonAmistad($accion, $idOtro, $texto, $filtro, $clase = '') {
    return '<form method="post" action="../script/amistad.php" class="form_amistad">
                <input type="hidden" name="accion" value="' . e($accion) . '">
                <input type="hidden" name="idOtro" value="' . (int)$idOtro . '">
                <input type="hidden" name="filtro" value="' . e($filtro) . '">
                <button type="submit" class="' . e($clase) . '">' . e($texto) . '</button>
            </form>';
}

// Tarjeta genérica: $acciones es HTML ya preparado (botones o texto)
function tarjetaUsuario($u, $subtitulo, $acciones) {
    echo '<div class="tarjeta">
            <img src="' . fotoUsuario($u['FotoPerfil']) . '" alt="Foto de ' . e($u['Username']) . '">
            <h2>' . e($u['Username']) . '</h2>
            <p>' . e($u['Nombre']) . ' ' . e($u['Apellido']) . '</p>
            <p>' . e($subtitulo) . '</p>
            <div class="acciones_amistad">' . $acciones . '</div>
          </div>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <!-- Link material Symbols -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=settings" />
    <link rel="shortcut icon" href="../img/logo.png" type="image/x-icon">
    <title>ChatOnline</title>
</head>
<body>
    <header>
        <section id="logo_peque">
            <img src="../img/logo.png" alt="Logo">
        </section>
        <section id="username">
            <p><?php echo e($user); ?></p>
            <img src="<?php echo fotoUsuario($_SESSION['FotoPerfil'] ?? null); ?>" alt="Foto de perfil">
        </section>
        <section id="ajustes">
            <a href="./perfil.php"><span class="material-symbols-outlined rueda-ajustes-per">settings</span></a>
        </section>
    </header>
    <main>
        <section id="contactos">
            <div id="Buscador">
                <form action="./home.php" method="get">
                    <label for="filtro">Buscar:</label>
                    <input type="text" name="filtro" id="filtro" placeholder="Buscar amigo/usuario" value="<?php echo e($filtro); ?>">
                    <button type="submit" class="btn_buscar">Buscar</button>
                    <!-- <?php if ($filtro !== '') { ?>
                        <a href="./home.php" class="btn_limpiar">Limpiar</a>
                    <?php } ?> -->
                </form>
            </div>

            <?php if ($mensaje !== '') { ?>
                <p class="error_buscador"><?php echo e($mensaje); ?></p>
            <?php } ?>

            <div id="chats_activos">
            <?php if ($filtro !== '') { ?>

                <h3 class="titulo_seccion">Resultados para "<?php echo e($filtro); ?>"</h3>
                <?php
                if (count($resultados) == 0) {
                    echo '<p class="vacio">No se ha encontrado ningún usuario.</p>';
                }
                foreach ($resultados as $r) {
                    $estado = $r['Estado'];
                    if ($estado === null || $estado === 'rechazada') {
                        $acc = botonAmistad('agregar', $r['IdUser'], 'Agregar', $filtro);
                    } elseif ($estado === 'aceptada') {
                        $acc = '<span class="etiqueta">Ya es amigo</span>';
                    } elseif ((int)$r['IdSolicitante'] === $idUser) {
                        $acc = '<span class="etiqueta">Pendiente</span>'
                             . botonAmistad('cancelar', $r['IdUser'], 'Cancelar', $filtro, 'secundario');
                    } else {
                        $acc = botonAmistad('aceptar', $r['IdUser'], 'Aceptar', $filtro)
                             . botonAmistad('rechazar', $r['IdUser'], 'Rechazar', $filtro, 'secundario');
                    }
                    tarjetaUsuario($r, '@' . $r['Username'], $acc);
                }
                ?>

            <?php } else { ?>

                <h3 class="titulo_seccion">Chats</h3>
                <?php
                if (count($amigos) == 0) {
                    echo '<p class="vacio">Aún no tienes amigos. Usa el buscador para añadir a alguien.</p>';
                }
                foreach ($amigos as $a) {
                    $nombreMostrado = $a['Apodo'] ? $a['Apodo'] : $a['Username'];
                    $ultimo = $a['UltimoMensaje'] !== null ? $a['UltimoMensaje'] : 'Sin mensajes todavía';
                    $enlace = $a['IdChat'] ? './home.php?chat=' . (int)$a['IdChat'] : '#';
                    // Burbuja de no leídos (no se pinta en el chat que tengo abierto)
                    $noLeidos = ((int)$a['IdChat'] !== $idChat) ? (int)$a['NoLeidos'] : 0;
                    echo '<div class="contacto">
                                <a href="' . e($enlace) . '" data-chat="' . (int)$a['IdChat'] . '">
                                    <div class="tarjeta">
                                        <img src="' . fotoUsuario($a['FotoPerfil']) . '" alt="Foto de ' . e($a['Username']) . '">
                                        <h2>' . e($nombreMostrado) . '</h2>
                                        <p>' . e($a['Nombre']) . ' ' . e($a['Apellido']) . '</p>
                                        <p class="ultimo_msg">' . e($ultimo) . '</p>
                                        <span class="burbuja">' . ($noLeidos > 0 ? $noLeidos : '') . '</span>
                                    </div>
                                </a>
                                <form method="post" action="../script/amistad.php" class="form_eliminar"
                                    onsubmit="return eliminar(this);">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="idOtro" value="' . (int)$a['IdUser'] . '">
                                    <button type="submit" class="btn_eliminar" title="Eliminar amigo" aria-label="Eliminar amigo">−</button>
                                </form>
                            </div>';
                }
                ?>

                <h3 class="titulo_seccion">Peticiones de amistad</h3>
                <?php
                if (count($peticiones) == 0) {
                    echo '<p class="vacio">No tienes peticiones pendientes.</p>';
                }
                foreach ($peticiones as $p) {
                    if ((int)$p['IdSolicitante'] === $idUser) {
                        // Enviada por mí
                        $acc = '<span class="etiqueta">Pendiente</span>'
                             . botonAmistad('cancelar', $p['IdUser'], 'Cancelar', '', 'secundario');
                        $sub = 'Solicitud enviada';
                    } else {
                        // Recibida
                        $acc = botonAmistad('aceptar', $p['IdUser'], 'Aceptar', '')
                             . botonAmistad('rechazar', $p['IdUser'], 'Rechazar', '', 'secundario');
                        $sub = 'Te ha enviado una solicitud';
                    }
                    tarjetaUsuario($p, $sub, $acc);
                }
                ?>

            <?php } ?>
            </div>
        </section>

        <section id="chat">
            <div class="tarjeta <?php echo $chatOk ? '' : 'chat_inactivo'; ?>">
                <?php $ultimoId = count($mensajesChat) ? (int)end($mensajesChat)['IdMensaje'] : 0; ?>
                <div id="contenido_chat" data-chat="<?php echo $chatOk ? $idChat : 0; ?>" data-ultimo="<?php echo $ultimoId; ?>">
                    <?php
                    if ($idChat === 0) {
                        echo '<p class="vacio"> Selecciona un chat para empezar a hablar.</p>';
                    }
                    if ($chatOk && count($mensajesChat) == 0) {
                        echo '<p class="vacio"> Aún no hay mensajes. ¡Escribe el primero!</p>';
                    }
                    foreach ($mensajesChat as $m) {
                        $clase = ((int)$m['IdUser'] === $idUser) ? 'mensaje_yo' : 'mensaje_amigo';
                        echo '<p class="' . $clase . '" data-id="' . (int)$m['IdMensaje'] . '">' . nl2br(e($m['Mensaje'])) . '</p>';
                    }
                    ?>
                    <span id="fin_chat"></span>
                </div>

                <?php if ($errorChat !== '') { ?>
                    <p class="error_buscador"><?php echo e($errorChat); ?></p>
                <?php } ?>

                <form action="../script/mensaje.php" method="post">
                    <input type="hidden" name="idChat" value="<?php echo $idChat; ?>">
                    <textarea rows="4" name="texto" id="texto" maxlength="2000"
                            placeholder="Escribe tu mensaje ..." <?php echo $chatOk ? '' : 'disabled'; ?>></textarea>
                    <button type="submit" <?php echo $chatOk ? '' : 'disabled'; ?>>Enviar</button>
                </form>
            </div>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/home.js"></script>
</body>
</html> 
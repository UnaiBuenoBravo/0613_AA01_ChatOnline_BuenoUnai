<?php
session_start();
include_once '../includes/conexion.php';

// Solo usuarios logueados y solo POST
if (!isset($_SESSION['IdUser']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/login.php');
    exit();
}

$yo     = (int)$_SESSION['IdUser'];
$accion = $_POST['accion'] ?? '';
$otro   = (int)($_POST['idOtro'] ?? 0);
$filtro = trim($_POST['filtro'] ?? '');

// Vuelta a home.php (conservando el filtro si lo había)
function volver($filtro) {
    $url = '../view/home.php';
    if ($filtro !== '') {
        $url .= '?filtro=' . urlencode($filtro);
    }
    header('Location: ' . $url);
    exit();
}

if ($otro <= 0 || $otro === $yo || !in_array($accion, ['agregar', 'aceptar', 'rechazar', 'cancelar', 'eliminar'])) {
    $_SESSION['msg_home'] = 'Acción no válida.';
    volver($filtro);
}

// Siempre guardamos el id menor en IdUser1 y el mayor en IdUser2
$u1 = min($yo, $otro);
$u2 = max($yo, $otro);

try {
    // ¿Existe el otro usuario?
    $st = $conn->prepare("SELECT IdUser FROM tbl_usuario WHERE IdUser = :id");
    $st->execute([':id' => $otro]);
    if (!$st->fetch()) {
        $_SESSION['msg_home'] = 'El usuario no existe.';
        volver($filtro);
    }

    // Relación actual entre los dos (si la hay)
    $st = $conn->prepare("SELECT Estado, IdSolicitante FROM tbl_amigos
                          WHERE IdUser1 = :u1 AND IdUser2 = :u2");
    $st->execute([':u1' => $u1, ':u2' => $u2]);
    $rel = $st->fetch(PDO::FETCH_ASSOC);

    switch ($accion) {

        // -----------------------------------------------------
        case 'agregar':
            // No se puede si hay bloqueo en cualquiera de los dos sentidos
            $st = $conn->prepare("SELECT 1 FROM tbl_bloqueo
                                  WHERE (IdBloqueador = :a AND IdBloqueado = :b)
                                     OR (IdBloqueador = :c AND IdBloqueado = :d)");
            $st->execute([':a' => $yo, ':b' => $otro, ':c' => $otro, ':d' => $yo]);
            if ($st->fetch()) {
                $_SESSION['msg_home'] = 'No puedes enviar la solicitud a este usuario.';
                break;
            }

            if (!$rel) {
                $st = $conn->prepare("INSERT INTO tbl_amigos (IdUser1, IdUser2, IdSolicitante, Estado)
                                      VALUES (:u1, :u2, :yo, 'pendiente')");
                $st->execute([':u1' => $u1, ':u2' => $u2, ':yo' => $yo]);

            } elseif ($rel['Estado'] === 'rechazada') {
                // Reutilizamos la fila (por el UNIQUE de la pareja)
                $st = $conn->prepare("UPDATE tbl_amigos
                                      SET Estado = 'pendiente', IdSolicitante = :yo,
                                          FechaSolicitud = NOW(), FechaRespuesta = NULL
                                      WHERE IdUser1 = :u1 AND IdUser2 = :u2");
                $st->execute([':yo' => $yo, ':u1' => $u1, ':u2' => $u2]);
            }
            // Si ya es pendiente o aceptada no hacemos nada
            break;

        // -----------------------------------------------------
        case 'cancelar':
            // Solo puedo cancelar una solicitud que envié yo
            $st = $conn->prepare("DELETE FROM tbl_amigos
                                  WHERE IdUser1 = :u1 AND IdUser2 = :u2
                                    AND Estado = 'pendiente' AND IdSolicitante = :yo");
            $st->execute([':u1' => $u1, ':u2' => $u2, ':yo' => $yo]);
            break;

        // -----------------------------------------------------
        case 'rechazar':
            // Solo puedo rechazar una solicitud que me enviaron a mí
            $st = $conn->prepare("UPDATE tbl_amigos
                                  SET Estado = 'rechazada', FechaRespuesta = NOW()
                                  WHERE IdUser1 = :u1 AND IdUser2 = :u2
                                    AND Estado = 'pendiente' AND IdSolicitante <> :yo");
            $st->execute([':u1' => $u1, ':u2' => $u2, ':yo' => $yo]);
            break;
        // -----------------------------------------------------
        case 'eliminar':
            // Solo se puede eliminar una amistad ya aceptada
            $st = $conn->prepare("DELETE FROM tbl_amigos
                                  WHERE IdUser1 = :u1 AND IdUser2 = :u2
                                    AND Estado = 'aceptada'");
            $st->execute([':u1' => $u1, ':u2' => $u2]);
            break;
        // -----------------------------------------------------
        case 'aceptar':
            if (!$rel || $rel['Estado'] !== 'pendiente' || (int)$rel['IdSolicitante'] === $yo) {
                $_SESSION['msg_home'] = 'No hay ninguna solicitud que aceptar.';
                break;
            }

            $conn->beginTransaction();
            try {
                $st = $conn->prepare("UPDATE tbl_amigos
                                      SET Estado = 'aceptada', FechaRespuesta = NOW()
                                      WHERE IdUser1 = :u1 AND IdUser2 = :u2");
                $st->execute([':u1' => $u1, ':u2' => $u2]);

                // Creamos el chat privado si no existe ya
                $clave = $u1 . '_' . $u2;
                $st = $conn->prepare("SELECT IdChat FROM tbl_chat WHERE ClavePrivado = :clave");
                $st->execute([':clave' => $clave]);
                $chat = $st->fetch(PDO::FETCH_ASSOC);

                if (!$chat) {
                    $st = $conn->prepare("INSERT INTO tbl_chat (Tipo, Nombre, ClavePrivado, IdCreador)
                                          VALUES ('privado', NULL, :clave, :yo)");
                    $st->execute([':clave' => $clave, ':yo' => $yo]);
                    $idChat = (int)$conn->lastInsertId();

                    $st = $conn->prepare("INSERT INTO tbl_chatparticipante (IdChat, IdUser, Rol)
                                          VALUES (:chat, :user, 'miembro')");
                    $st->execute([':chat' => $idChat, ':user' => $u1]);
                    $st->execute([':chat' => $idChat, ':user' => $u2]);
                }

                $conn->commit();
            } catch (PDOException $e) {
                $conn->rollBack();
                throw $e;
            }
            break;
    }

} catch (PDOException $e) {
    error_log('Error amistad: ' . $e->getMessage());
    $_SESSION['msg_home'] = 'Ha habido un problema con la base de datos. Inténtalo de nuevo más tarde.';
}

volver($filtro);

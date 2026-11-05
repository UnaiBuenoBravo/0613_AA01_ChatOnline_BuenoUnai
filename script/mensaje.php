<?php
session_start();
if (!isset($_SESSION['Usuario'])) {
    header('Location: ../view/login.php');
    exit();
}
include_once '../includes/conexion.php';
include_once '../view/chat.php';

$idUser = (int)$_SESSION['IdUser'];            // siempre de la sesión, nunca del formulario
$idChat = (int)($_POST['idChat'] ?? 0);
$texto  = trim($_POST['texto'] ?? '');

try {
    if ($texto === '') {
        $_SESSION['msg_chat'] = 'No puedes enviar un mensaje vacío';
    } elseif (mb_strlen($texto) > 2000) {
        $_SESSION['msg_chat'] = 'El mensaje es demasiado largo (máximo 2000 caracteres)';
    } else {
        // Se valida la amistad también al enviar, no solo al abrir el chat
        $r = comprobarChat($conn, $idUser, $idChat);
        if (!$r['ok']) {
            $_SESSION['msg_chat'] = $r['error'];
        } else {
            $stmt = $conn->prepare("INSERT INTO tbl_mensaje (IdChat, IdUser, Mensaje)
                                    VALUES (:chat, :user, :msg)");
            $stmt->execute([':chat' => $idChat, ':user' => $idUser, ':msg' => $texto]);
        }
    }
} catch (PDOException $e) {
    error_log('Error mensaje: ' . $e->getMessage());
    $_SESSION['msg_chat'] = 'No se ha podido enviar el mensaje';
}

header('Location: ../view/home.php?chat=' . $idChat . '#fin_chat');
exit();
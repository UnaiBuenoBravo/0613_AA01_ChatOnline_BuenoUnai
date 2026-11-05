<?php
// Devuelve en JSON los mensajes nuevos del chat abierto y los no leídos de cada chat.
// Lo llama js/notificaciones.js cada pocos segundos.
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['Usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'sesion']);
    exit();
}

include_once '../includes/conexion.php';
include_once '../view/chat.php';

$idUser = (int)$_SESSION['IdUser'];
$idChat = (int)($_GET['chat'] ?? 0);
$ultimo = (int)($_GET['ultimo'] ?? 0);

$salida = ['chats' => [], 'mensajes' => []];

try {
    // 1) Mensajes nuevos del chat que tengo abierto (y marcarlos como leídos)
    if ($idChat > 0) {
        $r = comprobarChat($conn, $idUser, $idChat);
        if ($r['ok']) {
            $sql = "SELECT m.IdMensaje, m.IdUser, m.Mensaje
                    FROM tbl_mensaje m
                    JOIN tbl_chatparticipante p
                      ON p.IdChat = m.IdChat AND p.IdUser = :id1
                    WHERE m.IdChat = :chat
                      AND m.IdMensaje > :ultimo
                      AND m.IdMensaje > p.UltimoMensajeBorrado
                    ORDER BY m.IdMensaje ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id1' => $idUser, ':chat' => $idChat, ':ultimo' => $ultimo]);
            $nuevos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($nuevos as $m) {
                $salida['mensajes'][] = [
                    'id'    => (int)$m['IdMensaje'],
                    'mio'   => ((int)$m['IdUser'] === $idUser),
                    'texto' => $m['Mensaje'],
                ];
            }

            if (count($nuevos) > 0) {
                $stmt = $conn->prepare("UPDATE tbl_chatparticipante
                                        SET UltimoMensajeLeido = GREATEST(UltimoMensajeLeido, :ult)
                                        WHERE IdChat = :chat2 AND IdUser = :id2");
                $stmt->execute([':ult' => end($nuevos)['IdMensaje'], ':chat2' => $idChat, ':id2' => $idUser]);
            }
        }
    }

    // 2) No leídos y último mensaje de cada uno de mis chats
    $sql = "SELECT p.IdChat,
                   (SELECT COUNT(*)
                    FROM tbl_mensaje m
                    WHERE m.IdChat = p.IdChat
                      AND m.IdUser <> :id1
                      AND m.IdMensaje > GREATEST(p.UltimoMensajeLeido, p.UltimoMensajeBorrado)) AS NoLeidos,
                   (SELECT m3.Mensaje
                    FROM tbl_mensaje m3
                    WHERE m3.IdChat = p.IdChat
                      AND m3.IdMensaje > p.UltimoMensajeBorrado
                    ORDER BY m3.IdMensaje DESC
                    LIMIT 1) AS UltimoMensaje
            FROM tbl_chatparticipante p
            WHERE p.IdUser = :id2";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id1' => $idUser, ':id2' => $idUser]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
        $salida['chats'][] = [
            'id'       => (int)$c['IdChat'],
            'noLeidos' => (int)$c['NoLeidos'],
            'ultimo'   => $c['UltimoMensaje'],
        ];
    }
} catch (PDOException $e) {
    error_log('Error nuevos: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'servidor']);
    exit();
}

echo json_encode($salida);
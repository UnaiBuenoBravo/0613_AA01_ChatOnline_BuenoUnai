t<?php
// ---------------------------------------------------------------
// Comprueba si $idUser puede usar el chat privado $idChat.
// Devuelve ['ok' => bool, 'error' => texto, 'otro' => username del otro]
// Orden de comprobaciones: 1) pertenece al chat  2) bloqueo  3) amistad
// ---------------------------------------------------------------
function comprobarChat($conn, $idUser, $idChat) {
    // 1) Soy participante de este chat privado? y quién es el otro?
    $sql = "SELECT u.IdUser, u.Username
            FROM tbl_chatparticipante mi
            JOIN tbl_chat c ON c.IdChat = mi.IdChat AND c.Tipo = 'privado'
            JOIN tbl_chatparticipante otro
              ON otro.IdChat = mi.IdChat AND otro.IdUser <> :id1
            JOIN tbl_usuario u ON u.IdUser = otro.IdUser
            WHERE mi.IdChat = :chat AND mi.IdUser = :id2";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id1' => $idUser, ':id2' => $idUser, ':chat' => $idChat]);
    $otro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$otro) {
        return ['ok' => false, 'error' => 'Este chat no existe', 'otro' => ''];
    }
    $idOtro = (int)$otro['IdUser'];

    // 2) Bloqueo en cualquier dirección
    $sql = "SELECT 1 FROM tbl_bloqueo
            WHERE (IdBloqueador = :a1 AND IdBloqueado = :b1)
               OR (IdBloqueador = :b2 AND IdBloqueado = :a2)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':a1' => $idUser, ':b1' => $idOtro, ':b2' => $idOtro, ':a2' => $idUser]);
    if ($stmt->fetchColumn()) {
        return ['ok' => false, 'error' => 'Este perfil no esta disponible', 'otro' => $otro['Username']];
    }

    // 3) Amistad aceptada (la pareja se guarda ordenada: menor, mayor)
    $sql = "SELECT 1 FROM tbl_amigos
            WHERE IdUser1 = LEAST(:a, :b)
              AND IdUser2 = GREATEST(:c, :d)
              AND Estado = 'aceptada'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':a' => $idUser, ':b' => $idOtro, ':c' => $idUser, ':d' => $idOtro]);
    if (!$stmt->fetchColumn()) {
        return ['ok' => false,
                'error' => 'Si no eres amigo de ' . $otro['Username'] . ' no puedes chatear con el',
                'otro' => $otro['Username']];
    }

    return ['ok' => true, 'error' => '', 'otro' => $otro['Username']];
}

// ---------------------------------------------------------------
// Crea (si no existe) el chat privado entre dos usuarios y sus
// dos participantes. Devuelve el IdChat. Llamar al ACEPTAR amistad.
// ---------------------------------------------------------------
function crearChatPrivado($conn, $idA, $idB) {
    $clave = min($idA, $idB) . '_' . max($idA, $idB);   // igual que en home.php

    $stmt = $conn->prepare("INSERT IGNORE INTO tbl_chat (Tipo, ClavePrivado) VALUES ('privado', :c1)");
    $stmt->execute([':c1' => $clave]);

    $stmt = $conn->prepare("SELECT IdChat FROM tbl_chat WHERE ClavePrivado = :c2");
    $stmt->execute([':c2' => $clave]);
    $idChat = (int)$stmt->fetchColumn();

    $stmt = $conn->prepare("INSERT IGNORE INTO tbl_chatparticipante (IdChat, IdUser)
                            VALUES (:chat1, :u1), (:chat2, :u2)");
    $stmt->execute([':chat1' => $idChat, ':u1' => $idA, ':chat2' => $idChat, ':u2' => $idB]);

    return $idChat;
}
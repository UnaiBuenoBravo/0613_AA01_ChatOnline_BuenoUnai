<?php

// echo password_hash('123', PASSWORD_DEFAULT);
session_start();
include_once '../includes/conexion.php';
 $upd = $conn->prepare(
            "UPDATE tbl_usuario
             SET Conectado = 0, UltimaConexion = NOW()
             WHERE IdUser = :id"
        );
    $upd->execute([':id' => $_SESSION['idUser']]);


session_unset();
session_destroy();


header('Location: ../view/index.php');

?>
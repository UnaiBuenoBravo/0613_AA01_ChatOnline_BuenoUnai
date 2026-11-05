<?php
session_start();
include_once '../includes/conexion.php';

// 1. Solo aceptar peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/login.php');
    exit();
}

// 2. Recoger datos
$name = trim($_POST['username'] ?? '');
$con  = $_POST['con'] ?? '';

// 3. Validar que no estén vacíos
if ($name === '' || $con === '') {
    header('Location: ../view/login.php?err=vacio');
    exit();
}

try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 4. Buscar por Username O Email con consulta preparada (evita SQL injection)
    $sql = "SELECT IdUser, Username, Nombre, Apellido, PasswordHash, Email, FotoPerfil
            FROM tbl_usuario
            WHERE Username = :user OR Email = :email
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':user'  => $name,
        ':email' => $name,
    ]);

    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    // 5. Comprobar que existe el usuario y que la contraseña coincide
    if ($u && password_verify($con, $u['PasswordHash'])) {

        // Evita fijación de sesión
        session_regenerate_id(true);

        // Guardar datos en sesión (nunca el hash)
        $_SESSION['IdUser']     = $u['IdUser'];
        $_SESSION['Usuario']   = $u['Username'];
        $_SESSION['Nombre']     = $u['Nombre'];
        $_SESSION['Apellido']   = $u['Apellido'];
        $_SESSION['Email']      = $u['Email'];
        $_SESSION['FotoPerfil'] = $u['FotoPerfil'];

        // Marcar como conectado y guardar la última conexión
        $upd = $conn->prepare(
            "UPDATE tbl_usuario
             SET Conectado = 1, UltimaConexion = NOW()
             WHERE IdUser = :id"
        );
        $upd->execute([':id' => $u['IdUser']]);

        header('Location: ../view/home.php');
        exit();
    }

    // Mensaje genérico: no revelar si falló el usuario o la contraseña
    header('Location: ../view/login.php?err=credenciales');
    exit();

} catch (PDOException $e) {
    // En producción: guarda el error en un log en vez de mostrarlo
    error_log('Error login: ' . $e->getMessage());
    header('Location: ../view/login.php?err=servidor');
    exit();
}
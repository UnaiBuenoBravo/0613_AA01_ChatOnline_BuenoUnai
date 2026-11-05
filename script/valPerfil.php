<?php
// script/valPerfil.php
// Valida y guarda los tres formularios de perfil.php (general, password, personalizar).
// Si algo falla deja errores y lo escrito en $_SESSION y vuelve a perfil.php.

session_start();

// >>> Ajusta esta ruta a donde tengas perfil.php (relativa a la carpeta script/)
const VOLVER = '../view/perfil.php';

if (!isset($_SESSION['Usuario'], $_SESSION['IdUser'])) {
    header('Location: ../view/login.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . VOLVER);
    exit();
}

include_once '../includes/conexion.php';

$idUser  = (int)$_SESSION['IdUser'];
$form    = $_POST['form'] ?? '';
$errores = [];
$datos   = [];
$ok      = [];

function limpiar($v) {
    return trim((string)$v);
}

try {
    switch ($form) {

        // ---------------------------------------------------------
        case 'general':
            $username = limpiar($_POST['username'] ?? '');
            $nombre   = limpiar($_POST['nombre']   ?? '');
            $apellido = limpiar($_POST['apellido'] ?? '');
            $email    = limpiar($_POST['email']    ?? '');
            $datos    = compact('username', 'nombre', 'apellido', 'email');

            if ($username === '') {
                $errores['username'] = 'El nombre de usuario es obligatorio.';
            } elseif (mb_strlen($username) < 3 || mb_strlen($username) > 50) {
                $errores['username'] = 'Debe tener entre 3 y 50 caracteres.';
            } elseif (!preg_match('/^[A-Za-z0-9_.]+$/', $username)) {
                $errores['username'] = 'Solo letras, números, punto y guion bajo.';
            }

            if ($nombre === '') {
                $errores['nombre'] = 'El nombre es obligatorio.';
            } elseif (mb_strlen($nombre) > 100 || !preg_match("/^\p{L}[\p{L} '\-]*$/u", $nombre)) {
                $errores['nombre'] = 'Nombre no válido (solo letras, máx. 100).';
            }

            if ($apellido === '') {
                $errores['apellido'] = 'El apellido es obligatorio.';
            } elseif (mb_strlen($apellido) > 100 || !preg_match("/^\p{L}[\p{L} '\-]*$/u", $apellido)) {
                $errores['apellido'] = 'Apellido no válido (solo letras, máx. 100).';
            }

            if ($email === '') {
                $errores['email'] = 'El email es obligatorio.';
            } elseif (mb_strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores['email'] = 'Email no válido.';
            }

            // Duplicados (ignorando al propio usuario)
            if (!isset($errores['username'])) {
                $st = $conn->prepare("SELECT 1 FROM Tbl_Usuario WHERE Username = :v AND IdUser <> :id");
                $st->execute([':v' => $username, ':id' => $idUser]);
                if ($st->fetch()) $errores['username'] = 'Ese nombre de usuario ya está en uso.';
            }
            if (!isset($errores['email'])) {
                $st = $conn->prepare("SELECT 1 FROM Tbl_Usuario WHERE Email = :v AND IdUser <> :id");
                $st->execute([':v' => $email, ':id' => $idUser]);
                if ($st->fetch()) $errores['email'] = 'Ese email ya está registrado.';
            }

            if (!$errores) {
                $st = $conn->prepare("UPDATE Tbl_Usuario
                                      SET Username = :u, Nombre = :n, Apellido = :a, Email = :e
                                      WHERE IdUser = :id");
                $st->execute([':u' => $username, ':n' => $nombre, ':a' => $apellido,
                              ':e' => $email, ':id' => $idUser]);
                $_SESSION['Usuario'] = $username;   // el header lo muestra
                $datos = [];                        // que perfil.php lea de la BBDD
                $ok['general'] = 'Configuración actualizada correctamente.';
            }
            break;

        // ---------------------------------------------------------
        case 'password':
            // Las contraseñas nunca se devuelven al formulario
            $old = (string)($_POST['oldCon']    ?? '');
            $new = (string)($_POST['newCon']    ?? '');
            $rep = (string)($_POST['repNewCon'] ?? '');

            $hash = null;
            if ($old === '') {
                $errores['oldCon'] = 'Escribe tu contraseña actual.';
            } else {
                $st = $conn->prepare("SELECT PasswordHash FROM Tbl_Usuario WHERE IdUser = :id");
                $st->execute([':id' => $idUser]);
                $hash = $st->fetchColumn();
                if (!$hash || !password_verify($old, $hash)) {
                    $errores['oldCon'] = 'La contraseña actual no es correcta.';
                }
            }

            if ($new === '') {
                $errores['newCon'] = 'Escribe la nueva contraseña.';
            } elseif (strlen($new) < 8 || strlen($new) > 72) {
                $errores['newCon'] = 'Debe tener entre 8 y 72 caracteres.';
            } elseif (!preg_match('/[A-Za-z]/', $new) || !preg_match('/\d/', $new)) {
                $errores['newCon'] = 'Debe incluir al menos una letra y un número.';
            } elseif ($new === $old) {
                $errores['newCon'] = 'La nueva contraseña debe ser distinta de la actual.';
            }

            if ($rep === '') {
                $errores['repNewCon'] = 'Repite la nueva contraseña.';
            } elseif ($new !== $rep) {
                $errores['repNewCon'] = 'Las contraseñas no coinciden.';
            }

            if (!$errores) {
                $st = $conn->prepare("UPDATE Tbl_Usuario SET PasswordHash = :h WHERE IdUser = :id");
                $st->execute([':h' => password_hash($new, PASSWORD_DEFAULT), ':id' => $idUser]);
                session_regenerate_id(true);
                $ok['password'] = 'Contraseña actualizada correctamente.';
            }
            break;

        // ---------------------------------------------------------
        case 'personalizar':
            $tema   = limpiar($_POST['tema']   ?? '');
            $color  = limpiar($_POST['color']  ?? '');
            $fuente = limpiar($_POST['fuente'] ?? '');
            $datos  = compact('tema', 'color', 'fuente');

            if (!in_array($tema, ['claro', 'oscuro'], true))                       $errores['tema']   = 'Tema no válido.';
            if (!in_array($color, ['azul', 'verde', 'rojo', 'morado'], true))      $errores['color']  = 'Color no válido.';
            if (!in_array($fuente, ['Arial', 'Verdana', 'Roboto'], true))          $errores['fuente'] = 'Fuente no válida.';

            if (!$errores) {
                // IdUser es UNIQUE en Tbl_Ajustes: crea la fila si no existe o la actualiza
                $st = $conn->prepare("INSERT INTO Tbl_Ajustes (IdUser, Tema, ColorChat, Fuente)
                                      VALUES (:id, :t, :c, :f)
                                      ON DUPLICATE KEY UPDATE Tema = VALUES(Tema),
                                                              ColorChat = VALUES(ColorChat),
                                                              Fuente = VALUES(Fuente)");
                $st->execute([':id' => $idUser, ':t' => $tema, ':c' => $color, ':f' => $fuente]);
                $datos = [];
                $ok['personalizar'] = 'Personalización guardada.';
            }
            break;

        default:
            // Formulario desconocido: no se hace nada
            break;
    }
} catch (PDOException $e) {
    error_log('Error valPerfil (' . $form . '): ' . $e->getMessage());
    $clave = ['general' => 'bd_general', 'password' => 'bd_password', 'personalizar' => 'bd_personalizar'][$form] ?? 'bd_general';
    $errores[$clave] = 'No se pudo guardar. Inténtalo de nuevo más tarde.';
}

if ($errores) {
    $_SESSION['errores_perfil'] = $errores;
    if ($datos) $_SESSION['datos_perfil'] = $datos;
}
if ($ok) {
    $_SESSION['ok_perfil'] = $ok;
}

header('Location: ' . VOLVER);
exit();

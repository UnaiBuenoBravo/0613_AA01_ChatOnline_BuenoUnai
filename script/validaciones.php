<?php

session_start();

// Este archivo recibe el formulario de registrarse.php (action="../script/validaciones.php").
// Nunca enseña nada por pantalla: siempre acaba redirigiendo.
//   - Todo OK  -> index.php
//   - Algún error -> vuelve a registrarse.php, que enseña los errores

$paginaRegistro = '../view/registrarse.php';
$paginaIndex    = '../view/index.php';

// Si alguien entra aquí sin pulsar el botón de registrarse, lo mandamos al formulario
if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['registrar'] )) {
    header('Location: ' . $paginaRegistro);
    exit;
}

require_once __DIR__ . '/../view/conexion.php';

$errores = [];
$datos   = [];

// Si no hay conexión con la BBDD, lo avisamos (el resto de campos se validan igual)
if ($conn == null) {
    $errores['general'] = "No se ha podido conectar con la base de datos. Inténtalo de nuevo más tarde.";
}

// Caracteres permitidos en username y contraseña:
// letras, números y   _  .  ,  -  #  ·  @
$caracteres = '/^[A-Za-z0-9_.,\-#\x{00B7}@]+$/u';

// Nombre y apellido: solo letras (con tildes, ñ...) y espacios
$soloLetras = '/^[\p{L} ]+$/u';





// ---------------------------------------------------------------
// Función para guardar nombres y apellidos:
//   "miguel angel"  ->  "MiguelAngel"
// ---------------------------------------------------------------
function formatearNombre($texto) {
    $palabras  = explode(" ", $texto);   // array: ["miguel", "angel"]
    $resultado = "";

    foreach ($palabras as $palabra) {
        if ($palabra != "") {            // salta los dobles espacios
            // MB_CASE_TITLE = primera letra en mayúscula y el resto en minúscula
            $resultado .= mb_convert_case($palabra, MB_CASE_TITLE, "UTF-8");
        }
    }
    return $resultado;
}


// ---------------------------------------------------------------
// 1. Recogemos lo que ha escrito el usuario (trim quita espacios sobrantes)
// ---------------------------------------------------------------
$username = trim($_POST['username'] ?? '');
$nombre   = trim($_POST['name'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email    = trim($_POST['email'] ?? '');
$passwd   = $_POST['passwd'] ?? '';
$repCon   = $_POST['repCon'] ?? '';

// Guardamos lo escrito para volver a ponerlo en el formulario (menos la contraseña)
$datos['username'] = $username;
$datos['nombre']   = $nombre;
$datos['apellido'] = $apellido;
$datos['email']    = $email;


// ---------------------------------------------------------------
// 2. Nombre
// ---------------------------------------------------------------
if ($nombre == "") {
    $errores['nombre'] = "El nombre no puede estar vacío.";
} elseif (mb_strlen($nombre) > 100) {
    $errores['nombre'] = "El nombre no puede tener más de 100 caracteres.";
} elseif (!preg_match($soloLetras, $nombre)) {
    $errores['nombre'] = "El nombre solo puede tener letras y espacios.";
}


// ---------------------------------------------------------------
// 3. Apellido (igual que el nombre)
// ---------------------------------------------------------------
if ($apellido == "") {
    $errores['apellido'] = "El apellido no puede estar vacío.";
} elseif (mb_strlen($apellido) > 100) {
    $errores['apellido'] = "El apellido no puede tener más de 100 caracteres.";
} elseif (!preg_match($soloLetras, $apellido)) {
    $errores['apellido'] = "El apellido solo puede tener letras y espacios.";
}


// ---------------------------------------------------------------
// 4. Username
// ---------------------------------------------------------------
if ($username == "") {
    $errores['username'] = "El username no puede estar vacío.";
} elseif (mb_strlen($username) > 30) {
    $errores['username'] = "El username no puede tener más de 30 caracteres.";
} elseif (!preg_match($caracteres, $username)) {
    $errores['username'] = "El username solo puede tener letras, números y los símbolos _ . , - # · @";
} elseif ($conn != null) {
    // El formato es correcto y hay conexión: miramos en la BBDD si ya existe
    try {
        $consulta = $conn->prepare("SELECT IdUser FROM Tbl_Usuario WHERE Username = :username");
        $consulta->execute([':username' => $username]);
        $existe = $consulta->fetch();   // false si no hay ninguna fila

        if ($existe != false) {
            // Ha devuelto una fila = ya está cogido
            $errores['username'] = "El username introducido ya existe";
        }
    } catch (PDOException $e) {
        error_log("Error al comprobar username: " . $e->getMessage());
        $errores['general'] = "Ha habido un problema con la base de datos. Inténtalo de nuevo más tarde.";
    }
}


// ---------------------------------------------------------------
// 5. Email
// ---------------------------------------------------------------
if ($email == "") {
    $errores['email'] = "El email no puede estar vacío.";
} elseif (mb_strlen($email) > 150) {
    $errores['email'] = "El email no puede tener más de 150 caracteres.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // filter_var es una función de PHP que ya sabe cómo es un email válido
    $errores['email'] = "El email no tiene un formato válido.";
} elseif ($conn != null) {
    // En la BBDD el email también es UNIQUE, lo comprobamos igual que el username
    try {
        $consulta = $conn->prepare("SELECT IdUser FROM Tbl_Usuario WHERE Email = :email");
        $consulta->execute([':email' => $email]);
        $existe = $consulta->fetch();

        if ($existe != false) {
            $errores['email'] = "El email introducido ya está registrado.";
        }
    } catch (PDOException $e) {
        error_log("Error al comprobar email: " . $e->getMessage());
        $errores['general'] = "Ha habido un problema con la base de datos. Inténtalo de nuevo más tarde.";
    }
}


// ---------------------------------------------------------------
// 6. Contraseña (mínimo 8, máximo 30, mismos caracteres que el username)
// ---------------------------------------------------------------
if ($passwd == "") {
    $errores['passwd'] = "La contraseña no puede estar vacía.";
} elseif (mb_strlen($passwd) < 8) {
    $errores['passwd'] = "La contraseña debe tener al menos 8 caracteres.";
} elseif (mb_strlen($passwd) > 30) {
    $errores['passwd'] = "La contraseña no puede tener más de 30 caracteres.";
} elseif (!preg_match($caracteres, $passwd)) {
    $errores['passwd'] = "La contraseña solo puede tener letras, números y los símbolos _ . , - # · @";
}

// Repetir contraseña: solo lo miramos si la primera está bien
if (!isset($errores['passwd']) && $passwd != $repCon) {
    $errores['repCon'] = "Las contraseñas no coinciden.";
}


// ---------------------------------------------------------------
// 7. Si no hay ningún error, guardamos en la BBDD y vamos a index.php
// ---------------------------------------------------------------
if (count($errores) == 0) {
    $nombreFinal   = formatearNombre($nombre);     // "Miguel Angel" -> "MiguelAngel"
    $apellidoFinal = formatearNombre($apellido);
    $hash          = password_hash($passwd, PASSWORD_DEFAULT);   // nunca guardes la contraseña en claro

    try {
        $insert = $conn->prepare(
            "INSERT INTO Tbl_Usuario (Username, Nombre, Apellido, PasswordHash, Email)
             VALUES (:username, :nombre, :apellido, :hash, :email)"
        );
        $insert->execute([
            ':username' => $username,
            ':nombre'   => $nombreFinal,
            ':apellido' => $apellidoFinal,
            ':hash'     => $hash,
            ':email'    => $email
        ]);

        // Creamos sus ajustes por defecto (tema claro, color azul, fuente Arial)
        $idNuevo = $conn->lastInsertId();
        $ajustes = $conn->prepare("INSERT INTO Tbl_Ajustes (IdUser) VALUES (:id)");
        $ajustes->execute([':id' => $idNuevo]);

        // TODO OK: nos vamos a index.php
        header('Location: ' . $paginaIndex);
        exit;

    } catch (PDOException $e) {
        // Por ejemplo: tabla que no existe, o dos personas con el mismo username a la vez
        error_log("Error al crear usuario: " . $e->getMessage());
        $errores['general'] = "No se ha podido crear tu cuenta por un problema con la base de datos. Inténtalo de nuevo más tarde.";
    }
}




// ---------------------------------------------------------------
// 8. Si hemos llegado hasta aquí, hay errores: volvemos a registrarse.php
//    (guardamos errores y datos en la sesión para que los pueda enseñar)
// ---------------------------------------------------------------
$_SESSION['errores'] = $errores;
$_SESSION['datos']   = $datos;
header('Location: ' . $paginaRegistro);
exit;
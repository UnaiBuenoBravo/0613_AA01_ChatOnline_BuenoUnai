<?php
// includes/ajustes.php
// Se incluye dentro del <head> de cada pagina. Carga los ajustes del usuario
// y aplica tema, color de mensajes y fuente sobre <html> (el CSS hace el resto).
// Deja disponibles: $ajTema, $ajColor, $ajFuente (los usa perfil.php).

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ajTema   = 'claro';
$ajColor  = 'azul';
$ajFuente = 'Arial';

if (isset($_SESSION['IdUser'])) {
    try {
        include_once __DIR__ . '/conexion.php';
        $stAj = $conn->prepare("SELECT Tema, ColorChat, Fuente FROM Tbl_Ajustes WHERE IdUser = :id");
        $stAj->execute([':id' => (int)$_SESSION['IdUser']]);
        $fila = $stAj->fetch(PDO::FETCH_ASSOC);

        // Solo se aceptan valores conocidos
        if ($fila) {
            if (in_array($fila['Tema'], ['claro', 'oscuro'], true))                        $ajTema   = $fila['Tema'];
            if (in_array($fila['ColorChat'], ['azul', 'verde', 'rojo', 'morado'], true))   $ajColor  = $fila['ColorChat'];
            if (in_array($fila['Fuente'], ['Arial', 'Verdana', 'Roboto'], true))           $ajFuente = $fila['Fuente'];
        }
    } catch (PDOException $e) {
        error_log('Error ajustes: ' . $e->getMessage());
    }
}
?>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<script>
// En el <head>, antes de pintar nada: evita el parpadeo de tema claro -> oscuro
(function () {
    var raiz = document.documentElement;
    raiz.setAttribute('data-tema',   <?php echo json_encode($ajTema); ?>);
    raiz.setAttribute('data-color',  <?php echo json_encode($ajColor); ?>);
    raiz.setAttribute('data-fuente', <?php echo json_encode($ajFuente); ?>);
})();
</script>

// Validación del registro en el navegador.
// Hace lo mismo que valRegistro.php (PHP sigue siendo el que manda).
// Lo de "el username ya existe" solo se puede comprobar en PHP (necesita la BBDD).

// Letras, números y  _ . , - # · @
const caracteres = /^[A-Za-z0-9_.,\-#\u00B7@]+$/;

// Solo letras (con tildes, ñ...) y espacios
const soloLetras = /^[\p{L} ]+$/u;

// Pone (o quita) el mensaje de error de un campo
function mostrarError(idInput, idError, mensaje) {
    document.getElementById(idError).textContent = mensaje;
    document.getElementById(idInput).classList.toggle('invalido', mensaje !== '');
}

// Cada función devuelve el mensaje de error, o "" si todo está bien
function validarNombre(texto, campo) {
    if (texto == "") return "El " + campo + " no puede estar vacío.";
    if (texto.length > 100) return "El " + campo + " no puede tener más de 100 caracteres.";
    if (!soloLetras.test(texto)) return "El " + campo + " solo puede tener letras y espacios.";
    return "";
}

function validarUsername(texto) {
    if (texto == "") return "El username no puede estar vacío.";
    if (texto.length > 30) return "El username no puede tener más de 30 caracteres.";
    if (!caracteres.test(texto)) return "El username solo puede tener letras, números y los símbolos _ . , - # · @";
    return "";
}

function validarEmail(texto) {
    if (texto == "") return "El email no puede estar vacío.";
    if (texto.length > 150) return "El email no puede tener más de 150 caracteres.";
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(texto)) return "El email no tiene un formato válido.";
    return "";
}

function validarPasswd(texto) {
    if (texto == "") return "La contraseña no puede estar vacía.";
    if (texto.length < 8) return "La contraseña debe tener al menos 8 caracteres.";
    if (texto.length > 30) return "La contraseña no puede tener más de 30 caracteres.";
    if (!caracteres.test(texto)) return "La contraseña solo puede tener letras, números y los símbolos _ . , - # · @";
    return "";
}

// ---------------------------------------------------------------
// Una función por campo. Se llaman con onblur (al salir del campo)
// y también desde validarFormulario (al pulsar el botón).
// Cada una enseña el error y lo devuelve ("" si todo está bien).
// ---------------------------------------------------------------
function comprobarNombre() {
    const texto = document.getElementById('inputNombre').value.trim();
    const error = validarNombre(texto, "nombre");
    mostrarError('inputNombre', 'errorNombre', error);
    return error;
}

function comprobarApellido() {
    const texto = document.getElementById('inputApellido').value.trim();
    const error = validarNombre(texto, "apellido");
    mostrarError('inputApellido', 'errorApellido', error);
    return error;
}

function comprobarUsername() {
    const texto = document.getElementById('inputUsername').value.trim();
    const error = validarUsername(texto);
    mostrarError('inputUsername', 'errorUsername', error);
    return error;
}

function comprobarEmail() {
    const texto = document.getElementById('inputEmail').value.trim();
    const error = validarEmail(texto);
    mostrarError('inputEmail', 'errorEmail', error);
    return error;
}

function comprobarPasswd() {
    const texto = document.getElementById('inputPasswd').value;
    const error = validarPasswd(texto);
    mostrarError('inputPasswd', 'errorPasswd', error);
    return error;
}

function comprobarRepCon() {
    const passwd = document.getElementById('inputPasswd').value;
    const repCon = document.getElementById('inputRepCon').value;

    // Repetir contraseña: solo si la primera está bien
    let error = "";
    if (validarPasswd(passwd) == "" && passwd != repCon) {
        error = "Las contraseñas no coinciden.";
    }
    mostrarError('inputRepCon', 'errorRepCon', error);
    return error;
}


// ---------------------------------------------------------------
// Se llama con onclick en el botón "Crear cuenta".
// return false = NO se envía el formulario
// return true  = se envía
// ---------------------------------------------------------------
function validarFormulario() {
    // Llamamos a todas (sin saltarnos ninguna) para que se vean todos los errores a la vez
    const errNombre   = comprobarNombre();
    const errApellido = comprobarApellido();
    const errUsername = comprobarUsername();
    const errEmail    = comprobarEmail();
    const errPasswd   = comprobarPasswd();
    const errRepCon   = comprobarRepCon();

    if (errNombre || errApellido || errUsername || errEmail || errPasswd || errRepCon) {
        return false;
    }
    return true;
}
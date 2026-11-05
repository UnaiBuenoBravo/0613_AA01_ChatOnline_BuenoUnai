// Actualiza en vivo (sin recargar) los mensajes del chat abierto
// y la burbuja morada de "mensajes sin leer" de la lista de chats.
(function () {
    const contenido = document.getElementById('contenido_chat');
    if (!contenido) return;

    const idChat = parseInt(contenido.dataset.chat || '0', 10);
    let ultimo   = parseInt(contenido.dataset.ultimo || '0', 10);
    const finChat = document.getElementById('fin_chat');

    // Baja el scroll al último mensaje
    function bajar() {
        contenido.scrollTop = contenido.scrollHeight;
    }

    // Añade un mensaje nuevo al chat abierto
    function pintarMensaje(m) {
        // Quitar el "Aún no hay mensajes" / "Selecciona un chat"
        contenido.querySelectorAll('.vacio').forEach(function (p) { p.remove(); });

        const p = document.createElement('p');
        p.className = m.mio ? 'mensaje_yo' : 'mensaje_amigo';
        p.dataset.id = m.id;

        // textContent evita inyectar HTML; los saltos de línea se hacen con <br>
        m.texto.split('\n').forEach(function (linea, i) {
            if (i > 0) p.appendChild(document.createElement('br'));
            p.appendChild(document.createTextNode(linea));
        });

        contenido.insertBefore(p, finChat);
    }

    // Actualiza burbujas y último mensaje de la lista lateral
    function pintarChats(chats) {
        chats.forEach(function (c) {
            const enlace = document.querySelector('#chats_activos a[data-chat="' + c.id + '"]');
            if (!enlace) return;

            const burbuja = enlace.querySelector('.burbuja');
            const resumen = enlace.querySelector('.ultimo_msg');

            // En el chat que tengo abierto nunca se muestra burbuja
            const n = (c.id === idChat) ? 0 : c.noLeidos;
            if (burbuja) burbuja.textContent = n > 0 ? n : '';
            if (resumen && c.ultimo !== null) resumen.textContent = c.ultimo;
        });
    }

    async function comprobar() {
        try {
            const url = '../script/nuevos.php?chat=' + idChat + '&ultimo=' + ultimo;
            const resp = await fetch(url, { cache: 'no-store' });

            if (resp.status === 401) {          // sesión caducada
                window.location.href = './login.php';
                return;
            }
            if (!resp.ok) return;

            const datos = await resp.json();

            if (datos.mensajes.length > 0) {
                // Solo bajar el scroll si ya estaba abajo del todo (o si el mensaje es mío)
                const alFinal = contenido.scrollHeight - contenido.scrollTop - contenido.clientHeight < 80;
                let hayMio = false;
                datos.mensajes.forEach(function (m) {
                    pintarMensaje(m);
                    ultimo = Math.max(ultimo, m.id);
                    if (m.mio) hayMio = true;
                });
                if (alFinal || hayMio) bajar();
            }

            pintarChats(datos.chats);
        } catch (e) {
            // Sin conexión o error puntual: se reintenta en la siguiente vuelta
        }
    }

    bajar();
    setInterval(comprobar, 3000);
})();
# Tbl_Amigos

| Campo | Tipo de dato | Longitud | Función |
|:---------|:-------------|:---------|:--------|
| IdAmigo PK | INT | — | Identificador de la relación |
| IdUser1 FK | INT | — | Usuario que envía la solicitud |
| IdUser2 FK | INT | — | Usuario que recibe la solicitud |
| Estado | ENUM | pendiente, accepted, refused | Estado de la solicitud de amistad |

La regla fundamental de las solicitudes, imaginemos que el usuario 1 envía una solicitud al usuario 2.

| IdAmigo | IdUser1 | IdUser2 | Estado |
|:--------|:--------|:--------|:-------|
| 1 | 1 | 2 | pendiente |

Si el usuario 2 no responde, la relación permanece pendiente. Si acepta, cambia a accepted. Si rechaza, cambia a refused.
Pero debemos garantizar estas reglas:
- El usuario no puede enviarse una solicitud a sí mismo.
- No puede haber dos relaciones para la misma pareja, independientemente del orden de los identificadores.
- Si el usuario 1 ya ha enviado una solicitud al usuario 2, el usuario 2 no puede crear otra solicitud hacia el usuario 1.
- Si la solicitud está pendiente, el receptor puede aceptarla o rechazarla.
- Si está aceptada, ambos usuarios son amigos y pueden acceder a su conversación.
- Si está rechazada, no se debe permitir crear otra solicitud en sentido inverso como forma de eludir el rechazo.
Una decisión importante: para mantener el proyecto sencillo, yo conservaría una única fila por pareja de usuarios y cambiaría su estado cuando corresponda, en lugar de crear una fila nueva para cada intento. Si en el futuro quisierais permitir volver a enviar solicitudes rechazadas, habría que definir esa regla expresamente.
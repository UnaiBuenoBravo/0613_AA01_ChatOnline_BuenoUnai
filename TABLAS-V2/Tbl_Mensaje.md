# Tbl_Mensaje

Almacena **todos los mensajes** enviados, tanto de chats privados como de grupos.

| Campo | Tipo de dato | Longitud | Función |
|:---|:---|:---|:---|
| IdMensaje **PK** | (BIG)INT UNSIGNED (AUTO_INCREMENT) | — | Identificador del mensaje; crece con el tiempo, por lo que también marca el orden |
| IdChat **FK**, NOT NULL | INT | — | Chat donde se envía → `Tbl_Chat` |
| IdUser **FK**, NOT NULL | INT | — | Autor del mensaje → `Tbl_Usuario` |
| Mensaje NOT NULL | TEXT | hasta 65.535 bytes | Contenido del mensaje |
| FechaEnvio NOT NULL | DATETIME | — | Fecha y hora de envío (automática) |

**Índice:** `idx_mensaje_chat_fecha (IdChat, FechaEnvio)` → acelera la carga del historial de un chat ordenado por fecha.

## Peculiaridades

- **BIGINT UNSIGNED** permite una cantidad enorme de mensajes sin quedarse sin números.
- **Validación automática** (trigger `trg_mensaje_bi`) antes de guardar cualquier mensaje:
  1. El autor debe ser **participante** del chat.
  2. En chats **privados**, además deben ser **amigos** (solicitud `aceptada`).
  3. En chats **privados**, **no** puede haber un **bloqueo** entre ambos.
- En **grupos** solo se comprueba que el autor pertenezca al grupo.
- Los ids de esta tabla son los que usan `UltimoMensajeLeido` y `UltimoMensajeBorrado` de `Tbl_ChatParticipante`.
- Si se elimina el chat, se borran sus mensajes. Si se elimina el usuario, se borran **sus** mensajes de todos los chats.

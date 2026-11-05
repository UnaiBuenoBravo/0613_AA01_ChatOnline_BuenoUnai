# Triggers BBDD

## 1. ¿Qué es un trigger?

Un **trigger** (disparador) es una **regla automática** de la base de datos. Se activa solo cuando alguien intenta **insertar** o **modificar** datos, y puede:

- **Dejar pasar** la operación si todo está bien.
- **Corregir** datos automáticamente.
- **Bloquear** la operación con un mensaje de error.
- **Hacer algo extra** (como crear datos relacionados).

Un símil podría ser un **portero de una discoteca**: cada vez que alguien intenta entrar (guardar datos), comprueba que cumple las normas.

**Nombres:** `trg_<tabla>_<momento>`: `bi` = *Before Insert* (antes de insertar), `bu` = *Before Update* (antes de modificar), `ai` = *After Insert* (después de insertar).

## 2. Resumen rápido

| Trigger | Tabla | Cuándo salta | Qué hace |
|:---|:---|:---|:---|
| `trg_usuario_ai (After Insert)` | Tbl_Usuario | Después de registrar un usuario | Crea sus ajustes por defecto |
| `trg_amigos_bi (Before Insert)` | Tbl_Amigos | Antes de crear una solicitud | Valida y ordena la pareja |
| `trg_amigos_bu (Before Update)` | Tbl_Amigos | Antes de modificar una solicitud | Revalida las reglas |
| `trg_bloqueo_bi (Before Insert)` | Tbl_Bloqueo | Antes de bloquear | Impide bloquearse a uno mismo |
| `trg_mensaje_bi (Before Insert)` | Tbl_Mensaje | Antes de enviar un mensaje | Comprueba permisos del remitente |

---

## 3. Triggers

### 3.1 `trg_usuario_ai` · Ajustes automáticos

**Cuándo:** justo **después** de registrar un usuario nuevo.

**Qué hace:** crea automáticamente su fila en `Tbl_Ajustes` con los valores por defecto (tema claro, color azul, fuente Arial).

**Ejemplo:** te registras como `maria`. Sin hacer nada más, ya tienes tus ajustes listos para personalizar.

**Para el programador:** nunca hagas `INSERT` en `Tbl_Ajustes`; solo `UPDATE`.

---

### 3.2 `trg_amigos_bi` · Reglas al enviar una solicitud de amistad

**Cuándo:** **antes** de guardar una solicitud nueva.

**Qué comprueba, en orden:**

| # | Regla | Si no se cumple |
|:--|:---|:---|
| 1 | No puedes enviarte una solicitud a ti mismo | ⛔ *"Un usuario no puede ser amigo de si mismo"* |
| 2 | Los dos ids se guardan **ordenados** (menor primero) | ✏️ Los intercambia automáticamente, sin error |
| 3 | El solicitante debe ser uno de los dos usuarios | ⛔ *"El solicitante debe ser uno de los dos usuarios"* |
| 4 | No debe haber bloqueo entre ambos (en ningún sentido) | ⛔ *"No se puede enviar solicitud: hay un bloqueo"* |

**Por qué se ordena la pareja:** para que "Ana y Luis" y "Luis y Ana" sean *la misma* relación. Sin esto podrían existir dos filas para los mismos amigos.

**Ejemplo:** el usuario 8 envía solicitud al usuario 3. Se guarda como `IdUser1 = 3`, `IdUser2 = 8`, `IdSolicitante = 8`. Así sabemos quién la envió.

---

### 3.3 `trg_amigos_bu` · Reglas al modificar una solicitud

**Cuándo:** **antes** de modificar una fila de `Tbl_Amigos` (aceptar, rechazar, reenviar...).

**Qué comprueba:**

| # | Regla | Si no se cumple |
|:--|:---|:---|
| 1 | La pareja sigue ordenada (`IdUser1 < IdUser2`) y el solicitante es uno de los dos | ⛔ *"Pareja de amigos no valida"* |
| 2 | Si el nuevo estado **no** es `rechazada`, no puede haber bloqueo | ⛔ *"No se puede continuar: hay un bloqueo"* |

**En sencillo:** aunque la solicitud ya exista, si entre medias uno bloquea al otro, **ya no se puede aceptar ni reenviar**. Lo único permitido con un bloqueo es dejarla en `rechazada`.

**Reenvío tras rechazo:** si alguien rechazó y luego quiere reintentarlo, se **modifica** la fila existente (porque la pareja es única) y este trigger vuelve a validar todo.

---

### 3.4 `trg_bloqueo_bi` · No bloquearse a uno mismo

**Cuándo:** **antes** de guardar un bloqueo.

**Qué hace:** si el bloqueador y el bloqueado son la misma persona, lo impide.

**Error:** ⛔ *"Un usuario no puede bloquearse a si mismo"*

---

### 3.5 `trg_mensaje_bi` · El portero de los mensajes

Es el más importante. Se activa **antes** de guardar cada mensaje y decide si se puede enviar.

**Paso 1: ¿Estás en el chat?**
Si no tienes fila en `Tbl_ChatParticipante` para ese chat, no puedes escribir.
⛔ *"El usuario no participa en este chat"*

**Paso 2: ¿Es privado o de grupo?**
- **Grupo:** ✅ ya puedes enviar. Solo hacía falta ser miembro.
- **Privado:** pasa a los pasos 3 y 4.

**Paso 3 (solo privados): ¿Sois amigos?**
Se identifica a la otra persona del chat y se busca una amistad con estado `aceptada`. Si no la hay (o no hay otro participante):
⛔ *"No sois amigos: no se puede enviar el mensaje"*

**Paso 4 (solo privados): ¿Hay bloqueo?**
Si alguno de los dos ha bloqueado al otro:
⛔ *"No se puede enviar el mensaje: hay un bloqueo"*

**Diagrama de decisión:**

```
Enviar mensaje
     │
     ▼
¿Participas en el chat? ── No ──► ⛔ Rechazado
     │ Sí
     ▼
¿Chat de grupo? ── Sí ──► ✅ Mensaje guardado
     │ No (privado)
     ▼
¿Sois amigos (aceptada)? ── No ──► ⛔ Rechazado
     │ Sí
     ▼
¿Hay bloqueo? ── Sí ──► ⛔ Rechazado
     │ No
     ▼
✅ Mensaje guardado
```

**Ejemplo real:** Ana y Luis son amigos y chatean. Luis bloquea a Ana. Los mensajes antiguos siguen existiendo, pero Ana (y Luis) **ya no pueden enviar** nuevos mensajes en ese chat privado. Si Luis la desbloquea, vuelven a poder escribir.

---

## 4. Cómo encajan todas las piezas

```
Registro ──► trg_usuario_ai ──► Ajustes creados
                    │
Solicitud de amistad ──► trg_amigos_bi ──► (aceptar/rechazar: trg_amigos_bu)
                    │
Bloquear ──► trg_bloqueo_bi
                    │
Chatear ──► trg_mensaje_bi (comprueba participación + amistad + bloqueo)
```

## 5. Chuleta de errores

| Mensaje de error | Qué significa | Cómo solucionarlo |
|:---|:---|:---|
| Un usuario no puede ser amigo de si mismo | Ids iguales en la solicitud | Usar dos usuarios distintos |
| El solicitante debe ser uno de los dos usuarios | `IdSolicitante` no coincide con la pareja | Poner como solicitante a uno de los dos |
| No se puede enviar solicitud: hay un bloqueo | Uno bloqueó al otro | Desbloquear primero |
| Pareja de amigos no valida | Pareja desordenada o solicitante incorrecto al modificar | Mantener `IdUser1 < IdUser2` |
| No se puede continuar: hay un bloqueo | Se intenta aceptar/reenviar con bloqueo | Desbloquear o dejarla como `rechazada` |
| Un usuario no puede bloquearse a si mismo | Bloqueador = bloqueado | Usar usuarios distintos |
| El usuario no participa en este chat | Falta la fila en `Tbl_ChatParticipante` | Añadir al usuario al chat |
| No sois amigos: no se puede enviar el mensaje | No hay amistad `aceptada` | Aceptar la solicitud de amistad |
| No se puede enviar el mensaje: hay un bloqueo | Bloqueo entre ambos | Desbloquear |

## 6. Cosas a tener en cuenta

- Los errores de estos triggers los **recibe PHP** como excepción: conviene capturarlos (`try/catch`) y mostrar un mensaje amable al usuario.
- Las comprobaciones de amistad y bloqueo **solo se aplican a chats privados**; los grupos no las usan.
- Los triggers **no crean** el chat privado ni sus participantes: eso lo hace la aplicación PHP (incluida la `ClavePrivado`).

### Parte de Codigo TRIGGERS:
-- =========================================
-- TRIGGERS
-- =========================================
DELIMITER $$

-- Ajustes por defecto al crear usuario
DROP TRIGGER IF EXISTS trg_usuario_ai$$
CREATE TRIGGER trg_usuario_ai AFTER INSERT ON Tbl_Usuario
FOR EACH ROW
BEGIN
    INSERT INTO Tbl_Ajustes (IdUser) VALUES (NEW.IdUser);
END$$

-- Amigos (INSERT): no a sí mismo, pareja ordenada, solicitante válido, sin bloqueo
DROP TRIGGER IF EXISTS trg_amigos_bi$$
CREATE TRIGGER trg_amigos_bi BEFORE INSERT ON Tbl_Amigos
FOR EACH ROW
BEGIN
    DECLARE tmp INT;

    IF NEW.IdUser1 = NEW.IdUser2 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Un usuario no puede ser amigo de si mismo';
    END IF;

    IF NEW.IdUser1 > NEW.IdUser2 THEN
        SET tmp = NEW.IdUser1;
        SET NEW.IdUser1 = NEW.IdUser2;
        SET NEW.IdUser2 = tmp;
    END IF;

    IF NEW.IdSolicitante NOT IN (NEW.IdUser1, NEW.IdUser2) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El solicitante debe ser uno de los dos usuarios';
    END IF;

    IF EXISTS (SELECT 1 FROM Tbl_Bloqueo
               WHERE (IdBloqueador = NEW.IdUser1 AND IdBloqueado = NEW.IdUser2)
                  OR (IdBloqueador = NEW.IdUser2 AND IdBloqueado = NEW.IdUser1)) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No se puede enviar solicitud: hay un bloqueo';
    END IF;
END$$

-- Amigos (UPDATE): mismas reglas (cubre el reenvío tras un rechazo)
DROP TRIGGER IF EXISTS trg_amigos_bu$$
CREATE TRIGGER trg_amigos_bu BEFORE UPDATE ON Tbl_Amigos
FOR EACH ROW
BEGIN
    IF NEW.IdUser1 >= NEW.IdUser2
       OR NEW.IdSolicitante NOT IN (NEW.IdUser1, NEW.IdUser2) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Pareja de amigos no valida';
    END IF;

    IF NEW.Estado <> 'rechazada'
       AND EXISTS (SELECT 1 FROM Tbl_Bloqueo
                   WHERE (IdBloqueador = NEW.IdUser1 AND IdBloqueado = NEW.IdUser2)
                      OR (IdBloqueador = NEW.IdUser2 AND IdBloqueado = NEW.IdUser1)) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No se puede continuar: hay un bloqueo';
    END IF;
END$$

-- Bloqueo: no a sí mismo
DROP TRIGGER IF EXISTS trg_bloqueo_bi$$
CREATE TRIGGER trg_bloqueo_bi BEFORE INSERT ON Tbl_Bloqueo
FOR EACH ROW
BEGIN
    IF NEW.IdBloqueador = NEW.IdBloqueado THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Un usuario no puede bloquearse a si mismo';
    END IF;
END$$

-- Mensajes: solo participantes; en privados, además amistad aceptada y sin bloqueo
DROP TRIGGER IF EXISTS trg_mensaje_bi$$
CREATE TRIGGER trg_mensaje_bi BEFORE INSERT ON Tbl_Mensaje
FOR EACH ROW
BEGIN
    DECLARE v_tipo VARCHAR(10);
    DECLARE v_otro INT;

    IF NOT EXISTS (SELECT 1 FROM Tbl_ChatParticipante
                   WHERE IdChat = NEW.IdChat AND IdUser = NEW.IdUser) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El usuario no participa en este chat';
    END IF;

    SET v_tipo = (SELECT Tipo FROM Tbl_Chat WHERE IdChat = NEW.IdChat);

    IF v_tipo = 'privado' THEN
        SET v_otro = (SELECT IdUser FROM Tbl_ChatParticipante
                      WHERE IdChat = NEW.IdChat AND IdUser <> NEW.IdUser LIMIT 1);

        IF v_otro IS NULL OR NOT EXISTS (
               SELECT 1 FROM Tbl_Amigos
               WHERE IdUser1 = LEAST(NEW.IdUser, v_otro)
                 AND IdUser2 = GREATEST(NEW.IdUser, v_otro)
                 AND Estado = 'aceptada') THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'No sois amigos: no se puede enviar el mensaje';
        END IF;

        IF EXISTS (SELECT 1 FROM Tbl_Bloqueo
                   WHERE (IdBloqueador = NEW.IdUser AND IdBloqueado = v_otro)
                      OR (IdBloqueador = v_otro AND IdBloqueado = NEW.IdUser)) THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'No se puede enviar el mensaje: hay un bloqueo';
        END IF;
    END IF;
END$$

DELIMITER ;

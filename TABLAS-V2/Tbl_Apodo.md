# Tbl_Apodo

Permite a un usuario ponerle un **apodo privado** a otro. Solo lo ve quien lo pone: la otra persona nunca se entera.

| Campo | Tipo de dato | Longitud | Función |
|:---|:---|:---|:---|
| IdApodo **PK** | INT (AUTO_INCREMENT) | — | Identificador del apodo |
| IdAutor **FK**, NOT NULL | INT | — | Usuario que pone el apodo → `Tbl_Usuario` |
| IdUser **FK**, NOT NULL | INT | — | Usuario que recibe el apodo → `Tbl_Usuario` |
| Apodo NOT NULL | VARCHAR | 50 | Texto del apodo |

**Restricción:** `UNIQUE (IdAutor, IdUser)` → cada usuario puede poner **un solo apodo** a cada persona.

## Peculiaridades

- **Es privado:** al mostrar un contacto, la aplicación debe buscar el apodo con `IdAutor = usuario que mira`. Los apodos de otros usuarios no deben mostrarse.
- Para cambiar un apodo se modifica la fila existente (no se crea otra, por la restricción única).
- No hay trigger que impida ponerse un apodo a uno mismo; si no lo quieres permitir, controla ese caso desde PHP.
- Si se elimina el autor o el apodado, el apodo se borra (cascada).

# Tbl_Mensaje

| Campo | Tipo de dato | Longitud | Función |
|:------------|:-------------|:---------|:------------------------------|
| IdMensaje PK | INT | — | Identificador del mensaje |
| IdChat FK   | INT | — | Chat al que pertenece |
| IdUser FK   | INT | —  | Usuario que lo envía  |
| Mensaje     | TEXT | — | Contenido del mensaje  |


## Ejemplo de Tbl_Mensaje

| IdMensaje | IdChat | IdUser | Mensaje | FechaEnvio |
|:----------|:-------|:-------|:--------|:-------------|
| *1*       | 1      | 1      | ¡Hola!      | DATETIME |
| *2*       | 1      | 2      | ¿Qué tal?   | DATETIME |
| *3*       | 1      | 1      | ¡Todo bien! | DATETIME |



Los mensajes se almacenan individualmente y en orden cronológico. Además añadimos un fecha envio para guardar dia y hora de los mensaje asi no dependemos solamente del IdChat. 

Además aqui añadiremos una validacion con el IdUsuario, para que la persona que envía los mensajes en dicho chat, sea quien dice ser, de forma que "if IdUsuario(envia) != IdUsuario (Chat) --> Este usuario no pertenece a ese chat."
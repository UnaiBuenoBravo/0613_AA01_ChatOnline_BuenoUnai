# Tbl_Chat

| Campo | Tipo de dato | Longitud | Función |
|:------------|:-------------|:---------|:-----------------------|
| IdChat PK | INT | — | Identificador del chat |
| IdAmigo FK | INT | — | Relación de amistad asociada al chat |

Cada pareja de amigos tendrá una conversación privada.

Por ejemplo, la relación de amistad IdAmigo = 1 puede tener el chat IdChat = 1.

Para la base de datos que hemos planteado el campo IdAmigo tampoco podrá repetir su valor.
De esa forma podremos evitar que una misma pareja tengo dos conversaciones privadas por error. 
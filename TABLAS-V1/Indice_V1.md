# ChatOnline

# Planteamiento de Flujo 1

## TABLAS

Tbl_Usuario - IdUser, Username, Nombre, Apellido, Contraseña, Email, FotoPerfil, Conectado <br>
Tbl_Amigos - IdAmigo, IdUser1, IdUser2, Estado(pendiente, accepted, refused) <br>
Tbl_Chat - IdChat, IdAmigo, Username1, Username2, MensajeUser1, MensajeUser2 <br>
Tbl_Apodo - IdApodo, IdChat, IdUser, Apodo <br>
Tbl_Ajustes - IdAjustes, IdUser, Temas, Colores_chat, Fuentes <br>

## LOGICA - Archivo
La `__Tbl_Usuario__`, tendra un `*login*` con los respectivos campos.
<hr>

### *Index* 
Será la página principal de cada **usuario**.
- Header que tendrá (LogoChat, **Username**, FotoPerfil, Ajustes [RedirectAjustes] ).

#### Buscador
Un buscador que filtrará entre usuarios amigos y desconocidos, dandole preferencia a los amigos. Como "dividiendo" el buscador. (dos section/div).

### Chats (Amigos)
Aqui veremos las chats ordenados de forma más reciente.

- Opción de poner al **usuario** el linea cuando inicie su session y offline cuando cierre la misma viendo si esta o no **conectado**. 
- Opcion de poner la opcion de ver el **usuario** escribiendo al hacer (onlick) en el input.

### Chat (Conversación)
Este va a ser el espacio donde exisitiran las interacciones.
<hr>

### Ajustes <br>
Perfil--> CambioUsername, Nombre, Apellido, Email, FotoPerfil<br>
Personalización --> Temas, Colores_chat, Teclado<br>
Seguridad --> CambioPasswd<br>
Idioma --> Seleccion de idioma<br>
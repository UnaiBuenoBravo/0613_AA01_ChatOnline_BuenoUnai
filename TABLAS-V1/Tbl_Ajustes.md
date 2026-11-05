# Tbl_Ajustes

| Campo | Tipo de dato | Longitud | Función |
|:---------------|:-------------|:---------|:--------------------|
| IdAjustes PK   | INT | — | Identificador de los ajustes |
| IdUser FK, UNIQUE | INT | — | Propietario de la configuración |
| Temas | VARCHAR | 30 | Tema visual |
| Colores_chat | VARCHAR | 50 | Personalización de los colores |
| Fuentes | VARCHAR | 50 | Tipografía seleccionada |

Cada usuario tendrá su configuración y con ello su apartado ajustes. Gracias a la FK de IdUser garantizamos que no existen simultáneas configuraciones para dicho usuario. 


# Tbl_Apodo

| Campo       | Tipo de dato | Longitud | Función |
|:------------|:-------------|:---------|:--------------------------------|
| IdApodo PK | INT  | — | Identificador del apodo |
| IdChat FK | INT  | — | Chat donde se utiliza |
| IdUser FK | INT | — | Usuario al que se asigna el apodo |
| Apodo | VARCHAR | 50 | Nombre personalizado |

A nivel de lógica este seria un ejemplo de como funcionaria el "Apodo" aplicando al chat segun usuarios. 

| IdApodo | IdChat | IdUser | Apodo      |
|:--------|:-------|:-------|:-----------|
| 1       | 1      | 2      | El máquina |
| 2       | 1      | 1      | Crack      |
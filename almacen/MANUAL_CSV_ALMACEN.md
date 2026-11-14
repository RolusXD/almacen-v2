# Manual CSV Almacen

## Objetivo

Este documento explica como llenar correctamente los archivos CSV para importar:

- equipos
- accesorios

## Archivos oficiales a usar

Usar siempre estas plantillas:

- [plantilla_importacion_equipos.csv](C:/Users/raul1/.lmstudio/conversations/almacen/assets/templates/plantilla_importacion_equipos.csv)
- [plantilla_importacion_accesorios.csv](C:/Users/raul1/.lmstudio/conversations/almacen/assets/templates/plantilla_importacion_accesorios.csv)

Ejemplos de apoyo:

- [ejemplo_importacion_equipos.csv](C:/Users/raul1/.lmstudio/conversations/almacen/assets/templates/ejemplo_importacion_equipos.csv)
- [ejemplo_importacion_accesorios.csv](C:/Users/raul1/.lmstudio/conversations/almacen/assets/templates/ejemplo_importacion_accesorios.csv)

No usar el archivo exportado del sistema como plantilla de importacion.

## Reglas generales

- El archivo debe ser `CSV`
- El separador debe ser `coma (,)`
- No cambiar el orden de las columnas
- No renombrar columnas
- No agregar columnas nuevas
- La primera fila debe quedar como encabezado
- Si un dato no aplica, dejar la celda vacia
- Guardar como `CSV UTF-8` si se trabaja en Excel
- Las fechas para importar deben escribirse como `AAAA-MM-DD`
- Aunque en pantalla el sistema muestre fechas como `dd/mm/aaaa`, en el CSV no se debe usar ese formato
- En Excel se recomienda poner primero la columna de fecha en formato `Texto` antes de escribirla
- Si se edita el archivo manualmente, evitar escribir comas dentro de una celda

## Importacion de equipos

### Columnas exactas

```text
Nombre, N° Serie/MAC, Código, Modelo, Marca, Fecha Entrada, Calidad, Observaciones, Precio, Proveedor
```

### Reglas

- `Nombre`: obligatorio
- `N° Serie/MAC`: obligatorio y no debe repetirse
- `Código`: no es libre, debe coincidir con un codigo ya existente en el catalogo de tipos de equipos
- `Modelo`: opcional
- `Marca`: opcional
- `Fecha Entrada`: formato `AAAA-MM-DD`
- Ejemplo correcto: `2026-04-23`
- Ejemplo incorrecto: `23/04/2026`
- `Calidad`: usar solo `NUEVO`, `BUENO`, `REGULAR` o `MALO`
- `Observaciones`: opcional
- `Precio`: usar punto decimal, ejemplo `350.00`
- `Proveedor`: texto libre segun el formato actual del sistema
- Si `Fecha Entrada` queda vacia, el sistema usa la fecha actual
- Si `Calidad` queda vacia, el sistema usa `BUENO`

### Ejemplo

```csv
Nombre,N° Serie/MAC,Código,Modelo,Marca,Fecha Entrada,Calidad,Observaciones,Precio,Proveedor
Router Mikrotik,RB-IMPORT-001,EQ-RB,RB750GR3,Mikrotik,2026-04-23,NUEVO,Equipo de ejemplo,350.00,DIGICORP
```

## Importacion de accesorios

### Columnas exactas

```text
Código, Descripción, Cantidad, Unidad de medida, Marca, Modelo, Serie/MAC, Fecha de ingreso, Calidad, Precio por unidad, Proveedor, Observaciones
```

### Reglas

- `Código`: recomendado
- `Código`: si ya existe un accesorio con el mismo `Código` y la misma `Unidad de medida`, el sistema suma la cantidad al registro existente
- `Descripción`: obligatoria
- `Cantidad`: obligatoria, numerica y entera
- `Unidad de medida`: obligatoria
- `Marca`: opcional
- `Modelo`: opcional
- `Serie/MAC`: opcional
- `Fecha de ingreso`: formato `AAAA-MM-DD`
- Ejemplo correcto: `2026-04-23`
- Ejemplo incorrecto: `23/04/2026`
- `Calidad`: usar solo `NUEVO`, `BUENO`, `REGULAR` o `MALO`
- `Precio por unidad`: usar punto decimal, ejemplo `25.50`
- `Proveedor`: debe coincidir con un proveedor existente en el sistema
- `Observaciones`: opcional
- Si `Fecha de ingreso` queda vacia, el sistema usa la fecha actual
- Si `Proveedor` no coincide exactamente con uno existente, no se asociara al proveedor

### Ejemplo

```csv
Código,Descripción,Cantidad,Unidad de medida,Marca,Modelo,Serie/MAC,Fecha de ingreso,Calidad,Precio por unidad,Proveedor,Observaciones
FADD,Adaptador de corriente,10,UNIDAD,Genérica,12V-2A,,2026-04-23,NUEVO,25.50,DIGICORP,Accesorio de ejemplo
```

## Errores comunes

- usar `;` en vez de `,`
- cambiar el nombre de las columnas
- cambiar el orden de las columnas
- guardar como Excel `.xlsx` en vez de CSV
- poner fechas como `23/04/2026` en vez de `2026-04-23`
- dejar que Excel cambie la fecha automaticamente a formato regional
- repetir numeros de serie en equipos
- usar codigos que no existen en catalogo
- pensar que el codigo en equipos puede inventarse libremente
- pensar que la importacion de accesorios siempre crea una fila nueva
- cargar cantidades con decimales en accesorios
- escribir mal el nombre del proveedor en accesorios

## Pasos recomendados

1. Abrir la plantilla correcta.
2. Si hay columnas de fecha, marcarlas como `Texto` en Excel.
3. Llenar los datos sin cambiar columnas.
4. Verificar que la fecha quede como `AAAA-MM-DD`.
5. Guardar como `CSV UTF-8 (delimitado por comas)`.
6. Ir al modulo correcto en el sistema.
7. Importar el archivo.
8. Revisar el mensaje final del sistema.

## Recomendacion operativa

Antes de cargar muchos registros:

1. probar primero con una sola fila
2. verificar que el sistema la acepte
3. luego cargar el archivo completo

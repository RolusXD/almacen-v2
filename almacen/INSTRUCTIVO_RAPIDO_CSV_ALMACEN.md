# Instructivo Rapido CSV Almacen

## Antes de importar

Usar solo estas plantillas:

- [plantilla_importacion_equipos.csv](C:/Users/raul1/.lmstudio/conversations/almacen/assets/templates/plantilla_importacion_equipos.csv)
- [plantilla_importacion_accesorios.csv](C:/Users/raul1/.lmstudio/conversations/almacen/assets/templates/plantilla_importacion_accesorios.csv)

No usar el archivo exportado del sistema como plantilla de importacion.

## Reglas que siempre deben cumplirse

- El archivo debe ser `CSV`
- El separador debe ser `coma (,)`
- No cambiar el orden de las columnas
- No renombrar columnas
- Guardar como `CSV UTF-8`
- Las fechas deben escribirse como `AAAA-MM-DD`

Ejemplo correcto:

```text
2026-04-23
```

Ejemplo incorrecto:

```text
23/04/2026
```

## Equipos

Columnas:

```text
Nombre, N° Serie/MAC, Código, Modelo, Marca, Fecha Entrada, Calidad, Observaciones, Precio, Proveedor
```

Puntos importantes:

- `Nombre`: obligatorio
- `N° Serie/MAC`: obligatorio y no debe repetirse
- `Código`: debe existir en el catalogo del sistema
- `Calidad`: usar solo `NUEVO`, `BUENO`, `REGULAR`, `MALO`
- `Precio`: usar punto decimal

Ejemplo:

```csv
Nombre,N° Serie/MAC,Código,Modelo,Marca,Fecha Entrada,Calidad,Observaciones,Precio,Proveedor
Router Mikrotik,RB-IMPORT-001,EQ-RB,RB750GR3,Mikrotik,2026-04-23,NUEVO,Equipo de ejemplo,350.00,DIGICORP
```

## Accesorios

Columnas:

```text
Código, Descripción, Cantidad, Unidad de medida, Marca, Modelo, Serie/MAC, Fecha de ingreso, Calidad, Precio por unidad, Proveedor, Observaciones
```

Puntos importantes:

- `Descripción`: obligatoria
- `Cantidad`: entera
- `Proveedor`: debe coincidir con uno existente
- Si se repite `Código + Unidad`, el sistema suma la cantidad

Ejemplo:

```csv
Código,Descripción,Cantidad,Unidad de medida,Marca,Modelo,Serie/MAC,Fecha de ingreso,Calidad,Precio por unidad,Proveedor,Observaciones
FADD,Adaptador de corriente,10,UNIDAD,Genérica,12V-2A,,2026-04-23,NUEVO,25.50,DIGICORP,Accesorio de ejemplo
```

## Errores comunes

- usar `;` en vez de `,`
- guardar en `.xlsx` en vez de `.csv`
- escribir fecha como `23/04/2026`
- inventar un codigo que no existe
- escribir mal el nombre del proveedor

## Recomendacion

Antes de cargar muchos datos:

1. probar primero con una sola fila
2. verificar que el sistema la acepte
3. luego cargar el archivo completo

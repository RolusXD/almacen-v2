# 📥 INSTALACIÓN DE FPDF - REPORTES PDF

**Fecha:** Marzo 2026  
**Problema:** Error al generar reportes PDF - FPDF no instalado

---

## ❌ ERROR ACTUAL

```
Warning: require_once(C:\xampp\htdocs\almacen_moderno5\controllers/../fpdf/fpdf.php): 
Failed to open stream: No such file or directory

Fatal error: Uncaught Error: Failed opening required 
'C:\xampp\htdocs\almacen_moderno5\controllers/../fpdf/fpdf.php'
```

**Causa:** La librería FPDF no está instalada en el servidor.

---

## ✅ SOLUCIÓN 1: INSTALAR FPDF (Recomendado)

### Opción A: Descarga Manual (Más Fácil)

**Paso 1: Descargar FPDF**
```
1. Ir a: http://www.fpdf.org/
2. Click en "Download" (Descargar)
3. Guardar archivo: fpdf186.zip (o versión más reciente)
```

**Paso 2: Extraer en el Proyecto**
```
1. Descomprimir fpdf186.zip
2. Copiar carpeta "fpdf" a:
   C:\xampp\htdocs\almacen_moderno\fpdf\
```

**Estructura final:**
```
almacen_moderno/
├── config/
├── controllers/
├── fpdf/              ← NUEVA CARPETA
│   ├── fpdf.php       ← Archivo principal
│   ├── font/
│   ├── makefont/
│   └── tutorial/
├── models/
└── views/
```

**Paso 3: Verificar Instalación**
```php
<?php
// Crear archivo de prueba: test_fpdf.php
require_once __DIR__ . '/fpdf/fpdf.php';

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(40, 10, 'FPDF Funciona!');
$pdf->Output();
echo "FPDF instalado correctamente!";
```

**Paso 4: Probar Reportes**
```
1. Acceder a: http://localhost/almacen_moderno
2. Ir a: Reportes
3. Click en: "Usuarios PDF"
4. Debería descargar PDF sin errores
```

---

### Opción B: Instalar con Composer (Avanzado)

**Si tienes Composer instalado:**

```bash
cd C:\xampp\htdocs\almacen_moderno

# Inicializar composer (si no existe composer.json)
composer init

# Instalar FPDF
composer require setasign/fpdf

# La ruta cambiará a:
# vendor/setasign/fpdf/fpdf.php
```

**Actualizar ReportesController.php:**
```php
// Cambiar línea 196:
require_once __DIR__ . '/../fpdf/fpdf.php';

// Por:
require_once __DIR__ . '/../vendor/autoload.php';
```

---

## ✅ SOLUCIÓN 2: TCPDF (Alternativa Moderna)

**Ventajas de TCPDF:**
- ✅ Más funciones que FPDF
- ✅ Soporte UTF-8 nativo
- ✅ Más fuentes disponibles
- ✅ Mejor soporte de caracteres especiales (ñ, tildes)

### Instalación de TCPDF

**Opción A: Descarga Manual**

```
1. Ir a: https://sourceforge.net/projects/tcpdf/
2. Descargar tcpdf-x.x.xx.zip
3. Extraer en: C:\xampp\htdocs\almacen_moderno\tcpdf\
```

**Opción B: Con Composer**

```bash
composer require tecnickcom/tcpdf
```

### Actualizar ReportesController.php para TCPDF

```php
// Cambiar línea 196:
require_once __DIR__ . '/../fpdf/fpdf.php';

// Por:
require_once __DIR__ . '/../tcpdf/tcpdf.php';

// Cambiar línea 201:
$pdf = new FPDF('L', 'mm', 'Letter');

// Por:
$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
```

---

## 🔧 CONFIGURACIÓN DE FUENTES

### FPDF - Fuentes Disponibles

**Fuentes por defecto:**
- Arial (Arial, Helvetica)
- Times (Times, Times New Roman)
- Courier (Courier, Courier New)
- Symbol
- ZapfDingbats

**Para usar otras fuentes:**
```php
// Agregar fuente personalizada
$pdf->AddFont('Roboto', '', 'roboto.php');
$pdf->SetFont('Roboto', '', 12);
```

### TCPDF - Fuentes Disponibles

**Fuentes incluidas:**
- helvetica (recomendada)
- times
- courier
- courierb
- courieri
- symbol
- zapfdingbats

**Fuentes adicionales:**
- dejavusans (soporta caracteres especiales)
- dejavusanscondensed
- freesans
- freemono

**Recomendación para español:**
```php
$pdf->SetFont('dejavusans', '', 12); // Soporta ñ, tildes
```

---

## 📝 ACTUALIZAR CONTROLADOR DE REPORTES

### ReportesController.php - Línea 196

**Verificar ruta correcta:**

```php
// Opción 1: FPDF en raíz del proyecto
require_once __DIR__ . '/../fpdf/fpdf.php';

// Opción 2: FPDF en vendor (Composer)
require_once __DIR__ . '/../vendor/autoload.php';

// Opción 3: TCPDF
require_once __DIR__ . '/../tcpdf/tcpdf.php';
```

### Agregar Verificación de Existencia

```php
// Antes de require_once, agregar:
$fpdfPath = __DIR__ . '/../fpdf/fpdf.php';
if (!file_exists($fpdfPath)) {
    $this->setFlash('error', 'Librería FPDF no instalada. Contacte al administrador.');
    $this->redirect('index.php?controller=dashboard');
    return;
}
require_once $fpdfPath;
```

---

## 🧪 PROBAR INSTALACIÓN

### Script de Prueba

**Crear archivo: test_fpdf.php**
```php
<?php
// test_fpdf.php - Prueba de FPDF

require_once __DIR__ . '/fpdf/fpdf.php';

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(40, 10, 'Hola Mundo!');
$pdf->Cell(40, 10, 'FPDF Funciona en TOI!');
$pdf->Output('I', 'prueba.pdf');

echo "✅ FPDF instalado correctamente!";
echo "<br><a href='index.php'>Volver al sistema</a>";
```

**Ejecutar:**
```
http://localhost/almacen_moderno/test_fpdf.php
```

**Resultado esperado:**
- ✅ PDF se muestra en el navegador
- ✅ Mensaje "FPDF instalado correctamente!"

---

## 🐛 SOLUCIÓN DE PROBLEMAS

### Problema 1: "Cannot modify header information"

**Causa:** Hay output antes de llamar a `$pdf->Output()`

**Solución:**
```php
// Asegurar que no haya output antes
ob_clean();
$pdf->Output('D', 'reporte.pdf');
exit;
```

### Problema 2: Caracteres especiales no se muestran (ñ, tildes)

**Causa:** FPDF no soporta UTF-8 por defecto

**Solución A: Usar TCPDF (recomendado)**
```php
$pdf = new TCPDF();
$pdf->SetFont('dejavusans', '', 12); // Soporta UTF-8
```

**Solución B: Convertir a ISO-8859-1**
```php
$pdf->Cell(0, 10, utf8_decode('Reporte de Usuarios'));
```

**Solución C: Usar fuente con UTF-8**
```php
// Generar fuente UTF-8 con makefont.php
// Incluido en carpeta fpdf/makefont/
```

### Problema 3: "Permission denied" al guardar PDF

**Causa:** Permisos incorrectos en carpeta temporal

**Solución:**
```bash
# Crear carpeta temporal
mkdir C:\xampp\htdocs\almacen_moderno\tmp
chmod 777 C:\xampp\htdocs\almacen_moderno\tmp

# Configurar en FPDF
$pdf->SetTempDir(__DIR__ . '/tmp');
```

---

## 📊 COMPARATIVA: FPDF vs TCPDF

| Característica | FPDF | TCPDF |
|----------------|------|-------|
| **Tamaño** | ~300 KB | ~2 MB |
| **UTF-8** | ❌ No (requiere conversión) | ✅ Sí (nativo) |
| **Fuentes** | 5 básicas | 20+ incluidas |
| **Imágenes** | JPG, PNG, GIF | JPG, PNG, GIF, SVG, WebP |
| **HTML** | ❌ No | ✅ Parcial |
| **QR Codes** | ❌ No | ✅ Sí |
| **Rendimiento** | ⚡ Muy rápido | 🐢 Más lento |
| **Facilidad** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ |

**Recomendación:** 
- **FPDF:** Reportes simples, rápido, poco espacio
- **TCPDF:** Reportes complejos, caracteres especiales, HTML

---

## 🚀 PASOS RÁPIDOS (5 Minutos)

```bash
# 1. Descargar FPDF
# Ir a: http://www.fpdf.org/es/download.php

# 2. Extraer en proyecto
# Copiar carpeta "fpdf" a: C:\xampp\htdocs\almacen_moderno\fpdf\

# 3. Verificar estructura
# C:\xampp\htdocs\almacen_moderno\fpdf\fpdf.php ← Debe existir

# 4. Probar
# http://localhost/almacen_moderno/test_fpdf.php

# 5. Probar reportes
# Ir a: Reportes → Usuarios PDF
```

---

## 📁 ESTRUCTURA FINAL RECOMENDADA

```
almacen_moderno/
├── config/
├── controllers/
│   └── ReportesController.php
├── fpdf/                  ← Librería FPDF
│   ├── fpdf.php           ← Archivo principal
│   ├── font/              ← Fuentes
│   ├── makefont/          ← Generador de fuentes
│   └── tutorial/          ← Ejemplos
├── tmp/                   ← Archivos temporales (opcional)
├── models/
├── views/
│   └── reportes/
├── test_fpdf.php          ← Script de prueba (eliminar después)
└── index.php
```

---

## ✅ VERIFICACIÓN FINAL

**Checklist:**
- [ ] FPDF descargado e instalado
- [ ] Archivo fpdf.php existe en: `almacen_moderno/fpdf/fpdf.php`
- [ ] Script de prueba funciona
- [ ] Reporte de Usuarios PDF genera correctamente
- [ ] Reporte de Equipos PDF genera correctamente
- [ ] Reporte de Accesorios PDF genera correctamente
- [ ] Caracteres especiales (ñ, tildes) se muestran correctamente

---

**Documentación oficial:**
- FPDF: http://www.fpdf.org/
- TCPDF: https://tcpdf.org/

**Estado:** ✅ Listo para instalar  
**Tiempo estimado:** 5-10 minutos

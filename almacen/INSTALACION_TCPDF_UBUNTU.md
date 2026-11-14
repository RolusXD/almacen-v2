# 📥 INSTALACIÓN DE TCPDF - UBUNTU 22.04
## Sistema de Almacén TOI - Producción

**Versión:** 1.0  
**Fecha:** Marzo 2026  
**SO:** Ubuntu Server 22.04 LTS

---

## 🎯 POR QUÉ TCPDF EN UBUNTU

**Ventajas para Ubuntu Server:**
- ✅ Mejor soporte UTF-8 (caracteres especiales ñ, tildes)
- ✅ Más estable en entornos Linux
- ✅ Mejor integración con Apache/Nginx
- ✅ Soporte técnico activo
- ✅ Compatible con PHP 8.0+

**FPDF vs TCPDF en Ubuntu:**
| Característica | FPDF | TCPDF |
|----------------|------|-------|
| UTF-8 | ❌ Requiere conversión | ✅ Nativo |
| Fuentes | 5 básicas | 20+ incluidas |
| Estabilidad Linux | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| Mantenimiento | ❌ Abandonado | ✅ Activo |
| Caracteres ñ tildes | ❌ Problemas | ✅ Sin problemas |

---

## 🚀 INSTALACIÓN EN UBUNTU 22.04

### Método 1: Composer (RECOMENDADO)

#### Paso 1: Verificar Prerrequisitos

```bash
# Verificar PHP instalado
php -v
# Debe mostrar PHP 8.0 o superior

# Verificar extensiones requeridas
php -m | grep -E "pdo|mbstring"
# Debe mostrar: pdo_mysql, mbstring
```

#### Paso 2: Instalar Composer (si no existe)

```bash
# Descargar instalador
cd /tmp
curl -sS https://getcomposer.org/installer | php

# Mover a directorio global
sudo mv composer.phar /usr/local/bin/composer

# Verificar instalación
composer --version
```

#### Paso 3: Navegar al Directorio del Proyecto

```bash
cd /var/www/html/almacen_moderno
```

#### Paso 4: Inicializar Composer

```bash
# Crear composer.json
composer init --name="toi/almacen" --type="project"
```

**Respuestas interactivas:**
```
Description []: Sistema de Almacén TOI
Author [Tu Nombre <tu@email.com>]: TOI <admin@toi.bo>
Minimum stability []: stable
Package type [project]: project
Define your dependencies? [no]: no
```

#### Paso 5: Instalar TCPDF

```bash
composer require tecnickcom/tcpdf
```

**Salida esperada:**
```
Using version ^6.6 for tecnickcom/tcpdf
./composer.json has been updated
Running composer update tecnickcom/tcpdf
Loading composer repositories with package information
Updating dependencies
Lock file operations: 1 install, 0 updates, 0 removals
  - Locking tecnickcom/tcpdf (6.6.5)
Writing lock file
Installing dependencies from lock file
Package operations: 1 install, 0 updates, 0 removals
  - Downloading tecnickcom/tcpdf (6.6.5)
  - Installing tecnickcom/tcpdf (6.6.5): Extracting archive
Generating autoload files
```

#### Paso 6: Verificar Instalación

```bash
# Verificar que TCPDF se instaló
ls -la vendor/tecnickcom/tcpdf/

# Verificar autoload
ls -la vendor/autoload.php
```

---

### Método 2: Descarga Manual (Alternativa)

```bash
# Navegar al directorio del proyecto
cd /var/www/html/almacen_moderno

# Descargar TCPDF
wget https://github.com/tecnickcom/TCPDF/archive/refs/tags/6.6.5.tar.gz

# Extraer
tar -xzf 6.6.5.tar.gz

# Mover a carpeta tcpdf
mv TCPDF-6.6.5 tcpdf

# Eliminar archivo comprimido
rm 6.6.5.tar.gz

# Establecer permisos
chown -R www-data:www-data tcpdf/
chmod -R 755 tcpdf/
```

---

## 🔧 ACTUALIZAR CÓDIGO DE LA APLICACIÓN

### 1. Actualizar ReportesController.php

**Archivo:** `controllers/ReportesController.php`

**Línea 196 - Cambiar:**
```php
// ANTES (FPDF):
require_once __DIR__ . '/../fpdf/fpdf.php';
```

**POR:**
```php
// AHORA (TCPDF con Composer):
require_once __DIR__ . '/../vendor/autoload.php';
```

**O para instalación manual:**
```php
// AHORA (TCPDF manual):
require_once __DIR__ . '/../tcpdf/tcpdf.php';
```

### 2. Actualizar Creación del PDF

**Línea 201 - Cambiar:**
```php
// ANTES (FPDF):
$pdf = new FPDF('L', 'mm', 'Letter');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
```

**POR (TCPDF):**
```php
// AHORA (TCPDF):
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
```

### 3. Actualizar Métodos de Output

**Cambiar:**
```php
// ANTES (FPDF):
$pdf->Output('D', 'reporte_usuarios.pdf');
```

**POR:**
```php
// AHORA (TCPDF):
$pdf->Output('reporte_usuarios.pdf', 'D');
```

**TCPDF Output modes:**
- `'I'` - Enviar al navegador (inline)
- `'D'` - Descargar (download)
- `'F'` - Guardar en archivo
- `'S'` - Devolver como string

---

## 📝 ACTUALIZAR TODOS LOS MÉTODOS PDF

### Ejemplo Completo - usuariosPdf()

```php
public function usuariosPdf() {
    // Verificar sesión
    if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
        $this->setFlash('error', 'No tiene permisos');
        $this->redirect('index.php?controller=dashboard');
    }

    // Cargar TCPDF
    require_once __DIR__ . '/../vendor/autoload.php';

    // Obtener datos
    $usuarioModel = new Usuario();
    $usuarios = $usuarioModel->obtenerTodos(100, 0);

    // Crear PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Configurar documento
    $pdf->SetCreator('Sistema de Almacén TOI');
    $pdf->SetAuthor('TOI - Telecomunicaciones Oportunas Inteligentes');
    $pdf->SetTitle('Reporte de Usuarios');
    $pdf->SetSubject('Reporte');
    
    // Configurar márgenes
    $pdf->SetMargins(15, 20, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    // Agregar página
    $pdf->AddPage();
    
    // Encabezado
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 10, 'SISTEMA DE ALMACEN TOI', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Reporte de Usuarios', 0, 1, 'C');
    $pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Tabla
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(30, 58, 95);
    $pdf->SetTextColor(255);
    $pdf->Cell(15, 8, 'ID', 1, 0, 'C', true);
    $pdf->Cell(40, 8, 'Usuario', 1, 0, 'C', true);
    $pdf->Cell(70, 8, 'Nombre', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Carnet', 1, 0, 'C', true);
    $pdf->Cell(60, 8, 'Email', 1, 0, 'C', true);
    
    // Datos
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(0);
    $pdf->SetFillColor(245, 245, 245);
    
    $fill = false;
    foreach ($usuarios as $usuario) {
        $pdf->Cell(15, 7, $usuario['id_usuario'], 1, 0, 'C', $fill);
        $pdf->Cell(40, 7, $usuario['usuario'], 1, 0, 'L', $fill);
        $pdf->Cell(70, 7, $usuario['nombre_completo'], 1, 0, 'L', $fill);
        $pdf->Cell(30, 7, $usuario['carnet'], 1, 0, 'L', $fill);
        $pdf->Cell(60, 7, $usuario['email'], 1, 0, 'L', $fill);
        $pdf->Ln();
        $fill = !$fill;
    }
    
    // Footer
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetTextColor(128);
    $pdf->Cell(0, 10, 'Página ' . $pdf->getAliasNumPage() . '/' . $pdf->getAliasNbPages(), 0, 0, 'R');
    
    // Output
    $pdf->Output('reporte_usuarios_' . date('Ymd') . '.pdf', 'D');
    exit;
}
```

---

## 🔒 PERMISOS Y SEGURIDAD

### Establecer Permisos Correctos

```bash
# Directorio del proyecto
sudo chown -R www-data:www-data /var/www/html/almacen_moderno
sudo chmod -R 755 /var/www/html/almacen_moderno

# Directorio vendor (solo lectura)
sudo chmod -R 755 /var/www/html/almacen_moderno/vendor

# Archivos PHP
sudo find /var/www/html/almacen_moderno -name "*.php" -exec chmod 644 {} \;

# Directorio de logs (si existe)
sudo mkdir -p /var/www/html/almacen_moderno/logs
sudo chown www-data:www-data /var/www/html/almacen_moderno/logs
sudo chmod 755 /var/www/html/almacen_moderno/logs

# Archivos de configuración (más restrictivos)
sudo chmod 600 /var/www/html/almacen_moderno/config/database.php
sudo chmod 600 /var/www/html/almacen_moderno/config/config.php
```

### Configurar directorio temporal

```bash
# Crear directorio temporal para TCPDF
sudo mkdir -p /var/www/html/almacen_moderno/tmp
sudo chown www-data:www-data /var/www/html/almacen_moderno/tmp
sudo chmod 777 /var/www/html/almacen_moderno/tmp

# En ReportesController.php, agregar:
$pdf->SetTempDir(__DIR__ . '/../tmp');
```

---

## 🧪 PROBAR INSTALACIÓN

### Script de Prueba

**Crear archivo:** `test_tcpdf.php`

```bash
cd /var/www/html/almacen_moderno
sudo nano test_tcpdf.php
```

**Contenido:**
```php
<?php
/**
 * Prueba de TCPDF - Ubuntu 22.04
 * Eliminar después de probar
 */

require_once __DIR__ . '/vendor/autoload.php';

$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('Sistema de Almacén TOI');
$pdf->SetAuthor('TOI');
$pdf->SetTitle('Prueba TCPDF');

$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 20);
$pdf->Cell(0, 10, 'TCPDF Funciona en Ubuntu!', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, 'Caracteres especiales: á é í ó ú ñ', 0, 1, 'C');
$pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y H:i:s'), 0, 1, 'C');

$pdf->Output('prueba_tcpdf.pdf', 'I');
echo "✅ TCPDF instalado correctamente en Ubuntu 22.04!";
```

**Probar:**
```bash
# Desde navegador:
http://tu-servidor/almacen_moderno/test_tcpdf.php

# Debería mostrar PDF en el navegador
```

**Eliminar después de probar:**
```bash
sudo rm test_tcpdf.php
```

---

## 📊 ACTUALIZAR TODOS LOS REPORTES

### Lista de métodos a actualizar:

```php
// controllers/ReportesController.php

// 1. Equipos PDF
public function equiposPdf() {
    require_once __DIR__ . '/../vendor/autoload.php';
    // ... actualizar código FPDF → TCPDF
}

// 2. Accesorios PDF
public function accesoriosPdf() {
    require_once __DIR__ . '/../vendor/autoload.php';
    // ... actualizar código FPDF → TCPDF
}

// 3. Movimientos PDF
public function movimientosPdf() {
    require_once __DIR__ . '/../vendor/autoload.php';
    // ... actualizar código FPDF → TCPDF
}

// 4. Usuarios PDF
public function usuariosPdf() {
    require_once __DIR__ . '/../vendor/autoload.php';
    // ... actualizar código FPDF → TCPDF
}
```

---

## 🐛 SOLUCIÓN DE PROBLEMAS EN UBUNTU

### Problema 1: "Class 'TCPDF' not found"

**Causa:** Autoload no se está cargando

**Solución:**
```php
// Verificar ruta
var_dump(file_exists(__DIR__ . '/../vendor/autoload.php'));

// Regenerar autoload
cd /var/www/html/almacen_moderno
composer dump-autoload
```

### Problema 2: "Permission denied"

**Causa:** Permisos incorrectos en directorio temporal

**Solución:**
```bash
sudo mkdir -p /var/www/html/almacen_moderno/tmp
sudo chown www-data:www-data /var/www/html/almacen_moderno/tmp
sudo chmod 777 /var/www/html/almacen_moderno/tmp
```

### Problema 3: Caracteres especiales no se muestran

**Causa:** Fuente no compatible con UTF-8

**Solución:**
```php
// Usar fuentes que soporten UTF-8
$pdf->SetFont('dejavusans', '', 12);  // ✅ Soporta UTF-8
$pdf->SetFont('helvetica', '', 12);   // ✅ Soporta UTF-8
$pdf->SetFont('arialunicid0', '', 12); // ✅ Soporta UTF-8

// NO usar:
$pdf->SetFont('arial', '', 12); // ❌ No soporta UTF-8
```

### Problema 4: "Cannot modify header information"

**Causa:** Output antes de llamar a Output()

**Solución:**
```php
// Limpiar buffer de salida
ob_clean();
$pdf->Output('reporte.pdf', 'D');
exit; // IMPORTANTE: exit después de Output()
```

---

## ✅ CHECKLIST DE INSTALACIÓN

### Pre-Instalación
- [ ] Ubuntu 22.04 instalado y actualizado
- [ ] Apache/Nginx configurado
- [ ] PHP 8.0+ instalado
- [ ] Extensiones PDO y mbstring habilitadas
- [ ] Acceso SSH al servidor

### Instalación
- [ ] Composer instalado globalmente
- [ ] Navegar a directorio del proyecto
- [ ] composer.json creado
- [ ] TCPDF instalado con composer
- [ ] Directorio vendor existe
- [ ] Permisos establecidos correctamente

### Actualización de Código
- [ ] ReportesController.php actualizado
- [ ] Todos los métodos PDF actualizados
- [ ] require_once apunta a vendor/autoload.php
- [ ] FPDF cambiado por TCPDF
- [ ] Fonts actualizadas a helvetica/dejavusans

### Pruebas
- [ ] Script test_tcpdf.php funciona
- [ ] Caracteres especiales (ñ, tildes) se muestran
- [ ] Reporte de Usuarios PDF genera
- [ ] Reporte de Equipos PDF genera
- [ ] Reporte de Accesorios PDF genera
- [ ] Descarga de PDFs funciona
- [ ] No hay errores en logs de Apache

### Limpieza
- [ ] test_tcpdf.php eliminado
- [ ] Permisos de configuración verificados
- [ ] Logs revisados
- [ ] Backup realizado

---

## 📦 COMPOSER.JSON DE EJEMPLO

```json
{
    "name": "toi/almacen",
    "description": "Sistema de Almacén TOI",
    "type": "project",
    "authors": [
        {
            "name": "TOI",
            "email": "admin@toi.bo"
        }
    ],
    "require": {
        "tecnickcom/tcpdf": "^6.6"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    },
    "config": {
        "optimize-autoloader": true,
        "sort-packages": true
    }
}
```

---

## 🚀 COMANDO DE INSTALACIÓN RÁPIDA

**Copiar y pegar en Ubuntu:**

```bash
# Navegar al proyecto
cd /var/www/html/almacen_moderno

# Instalar Composer (si no existe)
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
fi

# Inicializar composer
composer init --name="toi/almacen" --type="project" --no-interaction

# Instalar TCPDF
composer require tecnickcom/tcpdf --no-interaction

# Establecer permisos
sudo chown -R www-data:www-data /var/www/html/almacen_moderno
sudo chmod -R 755 /var/www/html/almacen_moderno
sudo chmod 600 /var/www/html/almacen_moderno/config/database.php
sudo chmod 600 /var/www/html/almacen_moderno/config/config.php

# Crear directorio temporal
sudo mkdir -p /var/www/html/almacen_moderno/tmp
sudo chown www-data:www-data /var/www/html/almacen_moderno/tmp
sudo chmod 777 /var/www/html/almacen_moderno/tmp

echo "✅ TCPDF instalado exitosamente!"
```

---

## 📊 ESTADO FINAL

| Componente | Estado | Notas |
|------------|--------|-------|
| TCPDF | ✅ Instalado | v6.6.5 |
| Composer | ✅ Configurado | Global |
| Permisos | ✅ Correctos | www-data |
| UTF-8 | ✅ Soportado | Nativo |
| Reportes | ✅ Listos | Actualizar código |

---

**Documentación oficial:**
- TCPDF: https://tcpdf.org/
- Composer: https://getcomposer.org/doc/00-intro.md

**Estado:** ✅ Listo para Ubuntu 22.04  
**Tiempo estimado:** 10-15 minutos

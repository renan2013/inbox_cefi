# 📘 Guía Maestra de Replicación: Módulo Curricular Oficial CONESUP en Inbox CEFI (Laravel)

Este documento es el **plano técnico y funcional definitivo** para replicar la totalidad de las características curriculares avaladas por **CONESUP** (creación y gestión de programas académicos, mallas curriculares, fichas técnicas de 12 filas, cronograma de 15 semanas con modalidad Zoom, rúbricas analíticas de 4 niveles con puntajes Moodle, y generadores PDF/Word de alta gama editorial) en el proyecto **Inbox CEFI** (`C:\Users\KIMBERLY\Documents\renan\inbox_cefi`).

---

## 🎯 1. Resumen de Características CONESUP a Implementar

| Componente | Característica Principal | Estándar Implementado |
|---|---|---|
| **Registro de Programas** | 10 bloques curriculares y sincronización con Plan de Estudios | Formato institucional CEFI con costos, perfiles, netiqueta e imágenes |
| **Ficha Técnica Oficial** | 12 filas estandarizadas | Código, Nombre (resaltado azul/blanco), Créditos, Duración, Distribución horas virtuales, Modalidad, Naturaleza, Ubicación curricular, Requisitos, Correquisitos, Nivel, Profesor |
| **Secciones Curriculares** | Nomenclatura oficial I a XI | I. Descripción general, II. Objetivo general, III. Objetivos específicos, IV. Contenido conceptual, V. Metodología de enseñanza, VI. Estrategias de aprendizaje, VII. Evaluación, VIII. Recursos didácticos, IX. Cronograma, X. Bibliografía obligatoria, XI. Bibliografía complementaria |
| **Cronograma de 15 Semanas** | Orden natural numérico y Modalidad Zoom | Orden estricto 1 a 15 (`CAST(semana AS UNSIGNED)`), 4 columnas (Semana, Contenidos, Actividades, Modalidad Sincrónico Zoom / Asincrónico) |
| **Rúbricas Analíticas** | 4 niveles CONESUP con puntajes Moodle | `Excelente`, `Bueno`, `En proceso`, `Insuficiente`, cálculo proporcional (`Puntos Totales / Cantidad Criterios`), descripción del entregable del estudiante |
| **Diseño Editorial PDF & Word** | Alta gama visual y legibilidad | Callouts con borde izquierdo de 3.5 mm, tablas con encabezado azul institucional, zebra striping, texto negro puro 100% (`#000000`), pie de página con logo Inbox 2.0 en gris centrado y enlaces con `/NewWindow true` |

---

## 🗄️ 2. Esquema de Base de Datos y Resiliencia (Self-Healing)

En Laravel, para garantizar que la base de datos se auto-repare sin necesidad de correr migraciones manuales en entornos locales o de producción, se implementan métodos `ensureColumns()` en los controladores:

### A. Columnas requeridas en `plan_estudios`:
```sql
ALTER TABLE plan_estudios ADD COLUMN IF NOT EXISTS `bibliografia_complementaria` LONGTEXT NULL;
ALTER TABLE plan_estudios ADD COLUMN IF NOT EXISTS `distribucion_horas` TEXT NULL;
ALTER TABLE plan_estudios ADD COLUMN IF NOT EXISTS `horas_teoricas` INT NULL DEFAULT 3;
ALTER TABLE plan_estudios ADD COLUMN IF NOT EXISTS `horas_practicas` INT NULL DEFAULT 1;
ALTER TABLE plan_estudios ADD COLUMN IF NOT EXISTS `horas_independientes` INT NULL DEFAULT 8;
ALTER TABLE plan_estudios ADD COLUMN IF NOT EXISTS `horas_totales` INT NULL DEFAULT 12;
ALTER TABLE plan_estudios ADD COLUMN IF NOT EXISTS `modalidad` VARCHAR(100) NULL DEFAULT 'Virtual (aprendizaje electrónico)';
ALTER TABLE plan_estudios ADD COLUMN IF NOT EXISTS `naturaleza` VARCHAR(100) NULL DEFAULT 'Teórico-práctica';
ALTER TABLE plan_estudios ADD COLUMN IF NOT EXISTS `evaluaciones_json` LONGTEXT NULL;
ALTER TABLE plan_estudios ADD COLUMN IF NOT EXISTS `cronograma_json` LONGTEXT NULL;
ALTER TABLE plan_estudios ADD COLUMN IF NOT EXISTS `rubricas_json` LONGTEXT NULL;
ALTER TABLE plan_estudios ADD COLUMN IF NOT EXISTS `fecha_descriptor_pdf` DATETIME NULL DEFAULT NULL;
```

### B. Columnas requeridas en `silabo_rubricas`:
```sql
ALTER TABLE silabo_rubricas ADD COLUMN IF NOT EXISTS `descripcion_entregable` LONGTEXT NULL;
```

### C. Columnas requeridas en `silabo_cronograma`:
```sql
ALTER TABLE silabo_cronograma ADD COLUMN IF NOT EXISTS `modalidad_trabajo` VARCHAR(100) NULL DEFAULT 'Asincrónico';
ALTER TABLE silabo_cronograma ADD COLUMN IF NOT EXISTS `actividades_moodle` LONGTEXT NULL;
```

### D. Método Self-Healing en `CursoController.php`:
Asegurar que `ensurePlanEstudiosColumns()` en [app/Http/Controllers/CursoController.php](file:///C:/Users/KIMBERLY/Documents/renan/inbox_cefi/app/Http/Controllers/CursoController.php) incluya:
```php
'bibliografia_complementaria' => "LONGTEXT NULL",
```
Y en `silabo_rubricas`:
```php
if (Schema::hasTable('silabo_rubricas')) {
    $existingRub = array_map('strtolower', Schema::getColumnListing('silabo_rubricas'));
    if (!in_array('descripcion_entregable', $existingRub, true)) {
        DB::statement("ALTER TABLE `silabo_rubricas` ADD COLUMN `descripcion_entregable` LONGTEXT NULL");
    }
}
```

---

## 💻 3. Controlador de Programas (`app/Http/Controllers/ProgramaController.php`)

El registro de programas en CEFI incluye tanto los datos generales como el plan de estudios dinámico:
1. **Método `create()`**: Retorna `programas.create`.
2. **Método `store(Request $request)`**:
   - Valida `nombre_programa`, `categoria`, `duracion`, `modalidad`, costos y bloques curriculares.
   - Procesa la subida de imágenes a `public/uploads/programas/`.
   - Llama a `$this->sincronizarPlanEstudios($request, $programa)`.
3. **Método `programasCompletos()`**:
   - Carga programas con sus cursos ordenados por cuatrimestre y código para la vista de mallas completas.
4. **Método `edit($id)`** y **`update(Request $request, $id)`**:
   - Permite modificar toda la ficha del programa y actualizar en vivo las materias del plan de estudios.

---

## 🎓 4. Controlador de Cursos (`app/Http/Controllers/CursoController.php`)

Puntos clave que deben estar activos:

### A. Orden Numérico Estricto en Cronograma
Tanto en consultas SQL como al cargar el JSON en PHP:
```php
// En consulta Eloquent/DB:
$cronograma_data = DB::table('silabo_cronograma')
    ->where('id_silabo', $id_silabo)
    ->orderByRaw('CAST(semana AS UNSIGNED) ASC, id_cronograma ASC')
    ->get()
    ->map(fn($item) => (array)$item)
    ->toArray();

// En PHP natural sort:
usort($cronograma_data, function($a, $b) {
    $semA = (int)preg_replace('/\D/', '', (string)($a['semana'] ?? '0'));
    $semB = (int)preg_replace('/\D/', '', (string)($b['semana'] ?? '0'));
    return $semA <=> $semB;
});
```

### B. Separación de Actividades de Enseñanza-Aprendizaje y Modalidad Zoom
Al procesar las semanas para el PDF o vista web:
- Si la actividad contiene el texto `"Actividades de enseñanza-aprendizaje"`, se extrae para la 3.ª columna.
- La 4.ª columna evalúa si es sincrónico (Zoom) o asincrónico:
```php
$esSincronico = (stripos($modalidad, 'sinc') !== false || stripos($actividad, 'zoom') !== false);
```

### C. Almacenamiento de Rúbricas Analíticas de 4 Niveles
Al guardar (`store` o `update`):
- Procesar `rubrica_descripcion` y guardarla en la columna `descripcion_entregable` y dentro del `contenido_json`.
- Cada rúbrica guarda en JSON los criterios con su puntaje proporcional calculado (`Total / Criterios`).

---

## 🎨 5. Generador Oficial de Descriptor PDF (`app/Services/DescriptorPdfService.php`)

El generador PDF debe aplicar los siguientes estándares de alta gama visual:

### A. Paleta de Colores Corporativa
```php
public $azul_cefi = [11, 61, 112];       // #0B3D70 - Primario institucional
public $verde_cefi = [95, 178, 48];      // #5FB230 - Acento institucional
public $oscuro_cefi = [0, 0, 0];         // Negro 100% puro para máxima legibilidad
public $gris_suave = [248, 250, 252];    // Para sombreado alterno (zebra striping)
public $gris_borde = [203, 213, 225];    // Líneas y bordes finos
```

### B. Celda del Nombre de Curso Resaltada
En la Ficha Técnica oficial:
- Fondo azul corporativo CEFI: `$this->SetFillColor(11, 61, 112);`
- Texto blanco en negrita: `$this->SetTextColor(255, 255, 255);` `$this->SetFont('Arial', 'B', 9);`

### C. Banners Callout para Secciones I a XI
Cada sección curricular debe renderizarse con:
- Borde lateral izquierdo grueso (3.5 mm) en `$this->azul_cefi`.
- Relleno de fondo suave en `$this->gris_suave`.
- Título en negrita y negro puro: `$this->SetTextColor(0, 0, 0);` `$this->SetFont('Arial', 'B', 11);`

### D. Enlaces Externos en Nueva Pestaña
En `app/Services/FPDF/fpdf.php`:
```php
$s .= '/A <</S /URI /URI '.$this->_textstring($pl[4]).' /NewWindow true>>>>';
```

### E. Pie de Página con Logotipo Inbox 2.0 Centrado
En la función `Footer()`:
- Línea divisoria superior.
- Texto izquierdo: `CEFI · Vicerrectoría Académica | {CÓDIGO}` en gris `#64748b`.
- Centro: Logotipo oficial de Inbox 2.0 en escala de grises sobre fondo transparente (`public/imgs/logo_inbox_gris.png`).
- Texto derecho: `Pág. {nb} de {nb}`.

---

## 📄 6. Generador de Documento Word (`app/Services/DescriptorDocService.php`)

Genera el archivo `.doc` (Word XML/HTML compatible) de 16 páginas:
- Cabecera con logo institucional en Base64.
- Ficha técnica completa de 12 filas con casilla de nombre en fondo azul `#0B3D70` y texto blanco.
- Tabla de evaluación CONESUP de 2 columnas.
- Cronograma de 15 semanas en 4 columnas.
- Bloque de rúbricas analíticas con descripciones y tablas de 4 niveles.
- Secciones X (Obligatoria) y XI (Complementaria) para bibliografía con enlaces activos.

---

## 🖥️ 7. Vistas Blade

### A. Listado de Cursos con Acordeones por Carrera (`resources/views/cursos/index.blade.php`)
- Pestañas/acordeones solapados por carrera/programa para evitar scroll excesivo.
- Buscador instantáneo en JavaScript que filtra por código, materia o carrera y despliega automáticamente la coincidencia.
- Botones de acción rápida: *Ver Descriptor*, *Editar Curso*, *Descargar PDF*, *Descargar Word*.

### B. Visor de Curso Oficial CONESUP (`resources/views/cursos/show.blade.php` o `ver_plan_estudios`)
- Cabecera unificada y limpia:
  * Botón 📘 **Descargar Word**
  * Botón 📕 **Descargar PDF**
  * Botón ✏️ **Editar Curso**
  * Botón ⬅️ **Volver a Cursos**
- Subtítulo dinámico con el programa: `Programa: <strong>MAESTRÍA EN...</strong>`.
- Ficha técnica oficial de 12 filas.
- Secciones I a XI con diseño responsivo y enlaces con `target="_blank"`.

### C. Edición y Creación de Cursos (`resources/views/cursos/edit.blade.php` y `create.blade.php`)
- 7 pestañas temáticas organizadas según la estructura CONESUP.
- Subtítulo dinámico sincronizado en tiempo real con el selector de programa `id_programa`.
- Editor interactivo de rúbricas analíticas con renderizado server-side y botones `+ Nivel`, `+ Criterio`.
- Campo de texto para la descripción de lo que el alumno debe entregar en cada rúbrica.

---

## ✅ 8. Checklist de Ejecución al Abrir `inbox_cefi`

Cuando el agente abra el proyecto `inbox_cefi`, debe ejecutar los siguientes pasos en orden:

1. [ ] **Verificar Base de Datos:** Ejecutar o validar que `ensurePlanEstudiosColumns()` en `CursoController` y `ensureProgramasColumns()` en `ProgramaController` contengan todas las columnas del esquema CONESUP.
2. [ ] **Verificar Vistas Blade:** Asegurar que `resources/views/cursos/index.blade.php`, `create.blade.php`, `edit.blade.php` y la vista de visualización cuenten con los campos de bibliografía complementaria y descripción de entregable en rúbricas.
3. [ ] **Verificar Cronograma:** Confirmar que `silabo_cronograma` utilice `ORDER BY CAST(semana AS UNSIGNED) ASC` y que la columna `Modalidad` incluya el formato Zoom.
4. [ ] **Verificar Descriptor PDF:** Probar la descarga de PDF (`/cursos/plan-estudio/{id}/descriptor-pdf`) verificando el encabezado azul, casilla de nombre destacada, 15 semanas en orden numérico y pie de página con logo Inbox en escala de grises.
5. [ ] **Verificar Descriptor Word:** Probar la descarga de Word (`/cursos/plan-estudio/{id}/descriptor-doc`) verificando la compatibilidad de estilos.
6. [ ] **Hacer Commit y Push:** Ejecutar `git add .`, `git commit -m "feat(conesup): replicar adaptacion curricular oficial conesup en inbox_cefi"` y `git push`.


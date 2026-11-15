# Contexto de Sesión - Inbox CEFI (Laravel)

**Última actualización:** 15 de Noviembre, 2026
**Directiva Maestra:** Replicación e implementación del Módulo Oficial de Registro de Programas y Adaptación Curricular CONESUP (Mallas, Cursos, Fichas Técnicas de 12 filas, Cronograma de 15 semanas con Modalidad Zoom, Rúbricas Analíticas de 4 niveles, Generadores PDF y Word de Alta Gama).

---

## 📌 Estado de Replicación CONESUP

El sistema `inbox_cefi` es una aplicación moderna desarrollada en **Laravel 10/11** con arquitectura basada en `bpm_unela_2`. Contiene la infraestructura de `ProgramaController`, `CursoController`, `DescriptorPdfService` y `DescriptorDocService`.

Para alcanzar la paridad 100% con las funcionalidades aprobadas por CONESUP en `bpm_unela`, se deben asegurar los siguientes componentes:

### 1. Base de Datos Resiliente y Columnas Faltantes
- `plan_estudios`: Incluir `bibliografia_complementaria` (`LONGTEXT NULL`) junto a `bibliografia`.
- `silabo_rubricas`: Incluir `descripcion_entregable` (`LONGTEXT NULL`) para almacenar la explicación de lo que el alumno debe entregar antes de la tabla de rúbrica.
- `silabo_cronograma`: Asegurar columna `modalidad_trabajo` (`VARCHAR(100) DEFAULT 'Asincrónico'`).
- `ensurePlanEstudiosColumns()` en `CursoController` y `ensureProgramasColumns()` en `ProgramaController` deben auto-reparar estas columnas en MySQL.

### 2. Ordenamiento Numérico en Cronograma
- Ordenamiento estricto por `CAST(semana AS UNSIGNED) ASC, id_cronograma ASC` en Eloquent/DB query y filtrado `preg_replace('/\D/', '', $semana)` en PHP para evitar que la "Semana 10" aparezca tras la "Semana 1".

### 3. Rúbricas Analíticas Oficiales de 4 Niveles
- Escala estándar CONESUP: `Excelente`, `Bueno`, `En proceso`, `Insuficiente`.
- Cálculo automático de puntaje proporcional para Moodle (`Puntos Totales / Cantidad Criterios`).
- Renderizado server-side interactivo con descripciones de entregable.

### 4. Generador Descriptor PDF & Word de Alta Gama
- Encabezados de tabla con fondo azul institucional y texto blanco en negrita.
- Casilla del nombre del curso en la Ficha Técnica con relleno azul y letras blancas negritas.
- Secciones I a XI con banners estilo Callout Ejecutivo y borde lateral izquierdo grueso (3.5 mm).
- Sombreado alterno (Zebra striping) en cronograma y rúbricas.
- Tipografía en negro puro 100% (`RGB [0, 0, 0]` / `#000000`) para máxima legibilidad.
- Footer con logotipo de Inbox 2.0 centrado en escala de grises sobre fondo transparente/blanco.
- Soporte para apertura de enlaces en nueva pestaña (`/NewWindow true` en FPDF).

---

## 📋 Documento de Referencia
Para instrucciones técnicas paso a paso, controladores, migraciones y vistas Blade, consulta:
- [GUIA_REPLICACION_CONESUP_CEFI.md](file:///C:/Users/KIMBERLY/Documents/renan/inbox_cefi/GUIA_REPLICACION_CONESUP_CEFI.md)


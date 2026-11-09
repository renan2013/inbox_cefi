<?php

namespace App\Http\Controllers;

use App\Models\PlanEstudio;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CursoController extends Controller
{
    /**
     * Asegura de forma preventiva que todas las columnas de la ficha curricular y las tablas de sílabos existan.
     */
    protected function ensurePlanEstudiosColumns()
    {
        try {
            if (Schema::hasTable('plan_estudios')) {
                $existing = array_map('strtolower', Schema::getColumnListing('plan_estudios'));
                $cols = [
                    'duracion' => "VARCHAR(100) NULL DEFAULT '15 semanas'",
                    'distribucion_horas' => "TEXT NULL",
                    'horas_teoricas' => "INT NULL DEFAULT 3",
                    'horas_practicas' => "INT NULL DEFAULT 1",
                    'horas_independientes' => "INT NULL DEFAULT 8",
                    'horas_totales' => "INT NULL DEFAULT 12",
                    'modalidad' => "VARCHAR(100) NULL DEFAULT 'Virtual (aprendizaje electrónico)'",
                    'naturaleza' => "VARCHAR(100) NULL DEFAULT 'Teórico-práctica'",
                    'requisitos' => "TEXT NULL",
                    'correquisitos' => "TEXT NULL",
                    'nivel' => "VARCHAR(100) NULL DEFAULT NULL",
                    'profesor' => "VARCHAR(255) NULL DEFAULT NULL",
                    'descripcion_curso' => "LONGTEXT NULL",
                    'objetivo_general' => "LONGTEXT NULL",
                    'objetivos_especificos' => "LONGTEXT NULL",
                    'contenidos_tematicos' => "LONGTEXT NULL",
                    'metodologia_ensenanza' => "LONGTEXT NULL",
                    'estrategias_aprendizaje' => "LONGTEXT NULL",
                    'evaluacion_aprendizajes' => "LONGTEXT NULL",
                    'recursos_didacticos' => "LONGTEXT NULL",
                    'cronograma' => "LONGTEXT NULL",
                    'guias_evaluacion' => "LONGTEXT NULL",
                    'bibliografia' => "LONGTEXT NULL",
                    'evaluaciones_json' => "LONGTEXT NULL",
                    'cronograma_json' => "LONGTEXT NULL",
                    'rubricas_json' => "LONGTEXT NULL",
                    'fecha_descriptor_pdf' => "DATETIME NULL DEFAULT NULL"
                ];

                foreach ($cols as $col => $type) {
                    if (!in_array(strtolower($col), $existing, true)) {
                        try {
                            DB::statement("ALTER TABLE plan_estudios ADD COLUMN `{$col}` {$type}");
                        } catch (\Throwable $e) {
                            // Continuar con la siguiente columna
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silenciosamente continuar
        }

        // Asegurar tablas y columnas de sílabo / evaluación / cronograma / rúbricas
        try {
            if (!Schema::hasTable('silabos')) {
                DB::statement("CREATE TABLE IF NOT EXISTS `silabos` (
                    `id_silabo` INT AUTO_INCREMENT PRIMARY KEY,
                    `id_plan` INT NOT NULL,
                    `id_profesor` INT NULL DEFAULT NULL,
                    `descripcion` LONGTEXT NULL,
                    `metodologia` LONGTEXT NULL,
                    `estrategias_aprendizaje` LONGTEXT NULL,
                    `recursos_aprendizaje` LONGTEXT NULL,
                    `contenidos` LONGTEXT NULL,
                    `cronograma` LONGTEXT NULL,
                    `bibliografia` LONGTEXT NULL,
                    `modalidad` VARCHAR(150) NULL,
                    `horario` VARCHAR(150) NULL,
                    `tipo_curso` VARCHAR(150) NULL,
                    `objetivo_general` LONGTEXT NULL,
                    `objetivos_especificos` LONGTEXT NULL,
                    `anexos` LONGTEXT NULL,
                    `fecha_creacion` DATETIME NULL
                )");
            } else {
                $existingSil = array_map('strtolower', Schema::getColumnListing('silabos'));
                $silCols = [
                    'estrategias_aprendizaje' => "LONGTEXT NULL",
                    'recursos_aprendizaje' => "LONGTEXT NULL",
                    'objetivo_general' => "LONGTEXT NULL",
                    'objetivos_especificos' => "LONGTEXT NULL",
                    'anexos' => "LONGTEXT NULL",
                    'horario' => "VARCHAR(150) NULL",
                    'tipo_curso' => "VARCHAR(150) NULL",
                    'modalidad' => "VARCHAR(150) NULL",
                    'fecha_creacion' => "DATETIME NULL"
                ];
                foreach ($silCols as $col => $type) {
                    if (!in_array(strtolower($col), $existingSil, true)) {
                        try {
                            DB::statement("ALTER TABLE `silabos` ADD COLUMN `{$col}` {$type}");
                        } catch (\Throwable $e) {}
                    }
                }
            }

            if (!Schema::hasTable('silabo_evaluacion')) {
                DB::statement("CREATE TABLE IF NOT EXISTS `silabo_evaluacion` (
                    `id_evaluacion` INT AUTO_INCREMENT PRIMARY KEY,
                    `id_silabo` INT NOT NULL,
                    `rubro` VARCHAR(255) NOT NULL,
                    `porcentaje` DECIMAL(5,2) NOT NULL DEFAULT 0,
                    `cantidad` INT NOT NULL DEFAULT 1,
                    `anexo` VARCHAR(255) NULL,
                    `tipo_moodle` VARCHAR(100) NULL
                )");
            } else {
                $existingEval = array_map('strtolower', Schema::getColumnListing('silabo_evaluacion'));
                foreach (['cantidad' => "INT NOT NULL DEFAULT 1", 'anexo' => "VARCHAR(255) NULL", 'tipo_moodle' => "VARCHAR(100) NULL"] as $col => $type) {
                    if (!in_array(strtolower($col), $existingEval, true)) {
                        try {
                            DB::statement("ALTER TABLE `silabo_evaluacion` ADD COLUMN `{$col}` {$type}");
                        } catch (\Throwable $e) {}
                    }
                }
            }

            if (!Schema::hasTable('silabo_cronograma')) {
                DB::statement("CREATE TABLE IF NOT EXISTS `silabo_cronograma` (
                    `id_cronograma` INT AUTO_INCREMENT PRIMARY KEY,
                    `id_silabo` INT NOT NULL,
                    `semana` VARCHAR(50) NOT NULL,
                    `fecha` VARCHAR(50) NULL,
                    `actividad` LONGTEXT NULL,
                    `tareas` LONGTEXT NULL,
                    `modalidad_trabajo` VARCHAR(100) NULL DEFAULT 'Asincrónico',
                    `actividades_moodle` LONGTEXT NULL
                )");
            } else {
                $existingCro = array_map('strtolower', Schema::getColumnListing('silabo_cronograma'));
                foreach (['fecha' => "VARCHAR(50) NULL", 'tareas' => "LONGTEXT NULL", 'modalidad_trabajo' => "VARCHAR(100) NULL DEFAULT 'Asincrónico'", 'actividades_moodle' => "LONGTEXT NULL"] as $col => $type) {
                    if (!in_array(strtolower($col), $existingCro, true)) {
                        try {
                            DB::statement("ALTER TABLE `silabo_cronograma` ADD COLUMN `{$col}` {$type}");
                        } catch (\Throwable $e) {}
                    }
                }
            }

            if (!Schema::hasTable('silabo_rubricas')) {
                DB::statement("CREATE TABLE IF NOT EXISTS `silabo_rubricas` (
                    `id_rubrica` INT AUTO_INCREMENT PRIMARY KEY,
                    `id_silabo` INT NOT NULL,
                    `codigo` VARCHAR(50) NULL,
                    `nombre` VARCHAR(255) NOT NULL,
                    `porcentaje_total` DECIMAL(5,2) NULL DEFAULT 0,
                    `cantidad` INT NULL DEFAULT 1,
                    `valor` DECIMAL(8,2) NULL DEFAULT 0,
                    `contenido_json` LONGTEXT NULL
                )");
            }
        } catch (\Throwable $e) {
            // Continuar
        }
    }

    /**
     * Filtra un arreglo asociativo dejando únicamente las columnas que existen en la tabla indicada.
     */
    protected function filterTableColumns(string $table, array $row): array
    {
        try {
            $cols = array_map('strtolower', Schema::getColumnListing($table));
            if (empty($cols)) {
                return $row;
            }
            $filtered = [];
            foreach ($row as $k => $v) {
                if (in_array(strtolower($k), $cols, true)) {
                    $filtered[$k] = $v;
                }
            }
            return $filtered;
        } catch (\Throwable $e) {
            return $row;
        }
    }

    /**
     * Sincroniza la plantilla de sílabo, evaluación, cronograma y rúbricas en las tablas relacionales.
     */
    protected function sincronizarPlantillaSilabo(PlanEstudio $plan, array $evaluaciones_arr, array $cronograma_arr, array $rubricas_arr): void
    {
        try {
            if (!Schema::hasTable('silabos')) {
                return;
            }

            $silabo = DB::table('silabos')
                ->where('id_plan', $plan->id_plan)
                ->orderBy('id_silabo', 'desc')
                ->first();

            $profesorId = Auth::id() ?: (DB::table('usuarios')->value('id') ?? 1);

            $baseSilaboData = [
                'id_plan' => $plan->id_plan,
                'descripcion' => $plan->descripcion_curso ?? '',
                'metodologia' => $plan->metodologia_ensenanza ?? '',
                'estrategias_aprendizaje' => $plan->estrategias_aprendizaje ?? '',
                'recursos_aprendizaje' => $plan->recursos_didacticos ?? '',
                'contenidos' => $plan->contenidos_tematicos ?? '',
                'cronograma' => $plan->cronograma ?? '',
                'bibliografia' => $plan->bibliografia ?? '',
                'modalidad' => $plan->modalidad ?? 'Virtual (aprendizaje electrónico)',
                'horario' => '',
                'tipo_curso' => $plan->naturaleza ?? 'Teórico-práctica',
                'objetivo_general' => $plan->objetivo_general ?? '',
                'objetivos_especificos' => $plan->objetivos_especificos ?? '',
                'anexos' => ''
            ];

            $id_silabo = null;
            if ($silabo) {
                $id_silabo = $silabo->id_silabo;
                $updatePayload = $this->filterTableColumns('silabos', $baseSilaboData);
                DB::table('silabos')->where('id_silabo', $id_silabo)->update($updatePayload);
            } else {
                $insertPayload = $baseSilaboData;
                $insertPayload['id_profesor'] = $profesorId;
                $insertPayload['fecha_creacion'] = now();
                $insertPayload = $this->filterTableColumns('silabos', $insertPayload);
                try {
                    $id_silabo = DB::table('silabos')->insertGetId($insertPayload);
                } catch (\Throwable $eInsert) {
                    // Fallback si id_profesor admite NULL o 0
                    $insertPayload['id_profesor'] = null;
                    try {
                        $id_silabo = DB::table('silabos')->insertGetId($insertPayload);
                    } catch (\Throwable $eInsert2) {
                        $insertPayload['id_profesor'] = 0;
                        $id_silabo = DB::table('silabos')->insertGetId($insertPayload);
                    }
                }
            }

            if (!$id_silabo) {
                return;
            }

            // 1. Sincronizar Evaluación
            try {
                if (Schema::hasTable('silabo_evaluacion')) {
                    DB::table('silabo_evaluacion')->where('id_silabo', $id_silabo)->delete();
                    foreach ($evaluaciones_arr as $ev) {
                        $rowEval = $this->filterTableColumns('silabo_evaluacion', [
                            'id_silabo' => $id_silabo,
                            'rubro' => $ev['rubro'],
                            'porcentaje' => $ev['porcentaje'],
                            'cantidad' => $ev['cantidad'],
                            'anexo' => $ev['anexo'],
                            'tipo_moodle' => $ev['tipo_moodle'],
                        ]);
                        DB::table('silabo_evaluacion')->insert($rowEval);
                    }
                }
            } catch (\Throwable $eEval) {}

            // 2. Sincronizar Cronograma
            try {
                if (Schema::hasTable('silabo_cronograma')) {
                    DB::table('silabo_cronograma')->where('id_silabo', $id_silabo)->delete();
                    foreach ($cronograma_arr as $cro) {
                        $rowCro = $this->filterTableColumns('silabo_cronograma', [
                            'id_silabo' => $id_silabo,
                            'semana' => (string)$cro['semana'],
                            'fecha' => (string)($cro['fecha'] ?? ''),
                            'actividad' => (string)($cro['actividad'] ?? ''),
                            'tareas' => (string)($cro['tareas'] ?? ''),
                            'modalidad_trabajo' => (string)($cro['modalidad_trabajo'] ?? 'Asincrónico'),
                            'actividades_moodle' => (string)($cro['actividades_moodle'] ?? ''),
                        ]);
                        DB::table('silabo_cronograma')->insert($rowCro);
                    }
                }
            } catch (\Throwable $eCro) {}

            // 3. Sincronizar Rúbricas
            try {
                if (Schema::hasTable('silabo_rubricas')) {
                    DB::table('silabo_rubricas')->where('id_silabo', $id_silabo)->delete();
                    foreach ($rubricas_arr as $rub) {
                        $rowRub = $this->filterTableColumns('silabo_rubricas', [
                            'id_silabo' => $id_silabo,
                            'codigo' => $rub['codigo'],
                            'nombre' => $rub['nombre'],
                            'porcentaje_total' => $rub['porcentaje_total'],
                            'cantidad' => $rub['cantidad'],
                            'valor' => $rub['valor'],
                            'contenido_json' => $rub['contenido_json'],
                        ]);
                        DB::table('silabo_rubricas')->insert($rowRub);
                    }
                }
            } catch (\Throwable $eRub) {}
        } catch (\Throwable $e) {
            // Silenciosamente continuar
        }
    }

    /**
     * Muestra el listado de materias/planes de estudio.
     */
    public function index(Request $request)
    {
        $this->ensurePlanEstudiosColumns();

        $query = PlanEstudio::with('programa');

        $isFiltered = false;

        // Búsqueda por término (materia o código)
        if ($request->filled('search')) {
            $isFiltered = true;
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('materia', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%");
            });
        }

        // Filtro por Programa
        if ($request->filled('programa_id')) {
            $isFiltered = true;
            $query->where('id_programa', $request->programa_id);
        }

        $cursos = $query->orderBy('cuatrimestre', 'asc')
                        ->orderBy('id_plan', 'asc')
                        ->get();

        $cursosPorPrograma = $cursos->groupBy('id_programa');
        $totalCursos = $cursos->count();

        $programas = Programa::orderBy('categoria', 'asc')->orderBy('nombre_programa', 'asc')->get();

        return view('cursos.index', compact('cursos', 'cursosPorPrograma', 'programas', 'totalCursos', 'isFiltered'));
    }

    /**
     * Muestra el formulario completo oficial (CEFI) para añadir un nuevo curso al plan de estudios.
     */
    public function create()
    {
        $programas = Programa::orderBy('categoria', 'asc')->orderBy('nombre_programa', 'asc')->get();

        // 15 semanas por defecto para el cronograma
        $cronograma_data = [];
        for ($i = 1; $i <= 15; $i++) {
            $cronograma_data[] = [
                'semana' => $i,
                'fecha' => '',
                'actividad' => '',
                'tareas' => '',
                'actividades_moodle' => '',
                'modalidad_trabajo' => 'Asincrónico'
            ];
        }

        $evaluaciones = [];
        $rubricas_data = [];

        return view('cursos.create', compact('programas', 'cronograma_data', 'evaluaciones', 'rubricas_data'));
    }

    /**
     * Almacena una nueva materia en el plan de estudios con su ficha técnica oficial y sílabo.
     */
    public function store(Request $request)
    {
        $this->ensurePlanEstudiosColumns();

        $request->validate([
            'id_programa' => 'required|exists:programas,id_programa',
            'cuatrimestre' => 'required|string|max:100',
            'codigo' => 'required|string|max:50',
            'materia' => 'required|string|max:255',
            'creditos' => 'nullable|integer|min:0',
        ]);

        $data = $request->except([
            'adjunto_pdf', '_token', 'active_tab',
            'rubros', 'porcentajes', 'cantidades_eval', 'tipos_moodle_eval', 'anexos_eval',
            'c_semana', 'c_fecha', 'c_actividad', 'c_tareas', 'c_moodle', 'c_modalidad',
            'rubrica_nombre_sel', 'rubrica_nombre_custom', 'rubrica_porcentaje', 'rubrica_cantidad', 'rubrica_valor', 'rubrica_json'
        ]);

        // Formatear duración
        $duracion_input = trim($request->input('duracion', '15'));
        $data['duracion'] = is_numeric($duracion_input) ? ($duracion_input . ' semanas') : ($duracion_input ?: '15 semanas');

        // Distribución horas
        $h_teo = (int)$request->input('horas_teoricas', 3);
        $h_prac = (int)$request->input('horas_practicas', 1);
        $h_ind = (int)$request->input('horas_independientes', 8);
        $h_tot = (int)$request->input('horas_totales', ($h_teo + $h_prac + $h_ind));
        $data['horas_teoricas'] = $h_teo;
        $data['horas_practicas'] = $h_prac;
        $data['horas_independientes'] = $h_ind;
        $data['horas_totales'] = $h_tot;

        if (empty($request->input('distribucion_horas'))) {
            $tPrac = ($h_prac === 1) ? "1 hora práctica" : "{$h_prac} horas prácticas";
            $tTeo = ($h_teo === 1) ? "1 hora teórica" : "{$h_teo} horas teóricas";
            $tInd = ($h_ind === 1) ? "1 hora de estudio independiente" : "{$h_ind} horas de estudio independiente";
            $data['distribucion_horas'] = "Este curso comprende un total de {$h_tot} horas distribuidas en {$tTeo}, {$tPrac} y {$tInd}.";
        } else {
            $data['distribucion_horas'] = $request->input('distribucion_horas');
        }

        // Precio sugerido o del programa
        if ($request->filled('precio') && is_numeric($request->precio)) {
            $data['precio'] = $request->precio;
        } else {
            $programa = Programa::find($request->id_programa);
            $data['precio'] = $programa ? $programa->costo_materia : 0.00;
        }

        // Subida de PDF
        if ($request->hasFile('adjunto_pdf')) {
            $file = $request->file('adjunto_pdf');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $dest = public_path('uploads/planes_estudio');
            if (!file_exists($dest)) {
                mkdir($dest, 0755, true);
            }
            $file->move($dest, $filename);
            $data['adjunto_pdf'] = $filename;
        } else {
            $data['adjunto_pdf'] = '';
        }

        // Construir arreglos estructurados y representación HTML para evaluación, cronograma y rúbricas
        $rubros = $request->input('rubros', []);
        $porcentajes = $request->input('porcentajes', []);
        $cantidades_eval = $request->input('cantidades_eval', []);
        $tipos_moodle = $request->input('tipos_moodle_eval', []);
        $anexos_eval = $request->input('anexos_eval', []);

        $evaluaciones_arr = [];
        if (is_array($rubros)) {
            foreach ($rubros as $k => $nom) {
                $nom = trim((string)$nom);
                if ($nom !== '') {
                    $evaluaciones_arr[] = [
                        'rubro' => $nom,
                        'porcentaje' => floatval($porcentajes[$k] ?? 0),
                        'cantidad' => intval($cantidades_eval[$k] ?? 1),
                        'anexo' => (string)($anexos_eval[$k] ?? ''),
                        'tipo_moodle' => (string)($tipos_moodle[$k] ?? ''),
                    ];
                }
            }
        }

        if (!empty($evaluaciones_arr)) {
            $evalHtml = '<table width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; border: 1px solid #cbd5e1; font-size: 10pt;">';
            $evalHtml .= '<tr style="background-color: #f1f5f9; font-weight: bold;"><th>Rubro</th><th style="text-align:center;">%</th><th style="text-align:center;">Cant.</th><th style="text-align:center;">Valor Unit.</th></tr>';
            $totP = 0;
            foreach ($evaluaciones_arr as $ev) {
                $p = $ev['porcentaje'];
                $totP += $p;
                $cant = $ev['cantidad'];
                $unit = $cant > 0 ? round($p / $cant, 2) : $p;
                $evalHtml .= "<tr><td>{$ev['rubro']}</td><td style='text-align:center;'>{$p}%</td><td style='text-align:center;'>{$cant}</td><td style='text-align:center;'>{$unit}%</td></tr>";
            }
            $evalHtml .= "<tr style='font-weight: bold; background-color: #f8fafc;'><td style='text-align: right;'>TOTAL:</td><td style='text-align:center;'>{$totP}%</td><td colspan='2'></td></tr>";
            $evalHtml .= '</table>';
            $data['evaluacion_aprendizajes'] = $evalHtml;
        }

        // Cronograma
        $semanas = $request->input('c_semana', []);
        $fechas = $request->input('c_fecha', []);
        $actividades = $request->input('c_actividad', []);
        $tareas_post = $request->input('c_tareas', []);
        $modalidades = $request->input('c_modalidad', []);
        $moodle_post = $request->input('c_moodle', []);

        $cronograma_arr = [];
        if (is_array($semanas)) {
            foreach ($semanas as $k => $sem) {
                $cronograma_arr[] = [
                    'semana' => intval($sem),
                    'fecha' => !empty($fechas[$k]) ? (string)$fechas[$k] : '',
                    'actividad' => (string)($actividades[$k] ?? ''),
                    'tareas' => (string)($tareas_post[$k] ?? ''),
                    'modalidad_trabajo' => (string)($modalidades[$k] ?? 'Asincrónico'),
                    'actividades_moodle' => (string)($moodle_post[$k] ?? ''),
                ];
            }
        }

        if (!empty($cronograma_arr)) {
            $cronoHtml = '<table width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; border: 1px solid #cbd5e1; font-size: 9.5pt;">';
            $cronoHtml .= '<tr style="background-color: #f1f5f9; font-weight: bold;"><th style="width: 12%;">Semana</th><th style="width: 40%;">Actividades / Contenidos</th><th style="width: 33%;">Tareas / Evaluación</th><th style="width: 15%;">Modalidad</th></tr>';
            foreach ($cronograma_arr as $cro) {
                $semLabel = $cro['semana'];
                if (!empty($cro['fecha'])) {
                    $ts = strtotime($cro['fecha']);
                    $fechaFmt = $ts ? date('d/m/Y', $ts) : $cro['fecha'];
                    $semLabel .= " ({$fechaFmt})";
                }
                $cronoHtml .= "<tr><td style='text-align:center; font-weight: bold;'>{$semLabel}</td><td>{$cro['actividad']}</td><td>{$cro['tareas']}</td><td style='text-align:center;'>{$cro['modalidad_trabajo']}</td></tr>";
            }
            $cronoHtml .= '</table>';
            $data['cronograma'] = $cronoHtml;
        }

        // Rúbricas
        $r_nombres_sel = $request->input('rubrica_nombre_sel', []);
        $r_nombres_custom = $request->input('rubrica_nombre_custom', []);
        $r_porcentajes = $request->input('rubrica_porcentaje', []);
        $r_cantidades = $request->input('rubrica_cantidad', []);
        $r_valores = $request->input('rubrica_valor', []);
        $r_json = $request->input('rubrica_json', []);

        $rubricas_arr = [];
        if (is_array($r_nombres_sel)) {
            foreach ($r_nombres_sel as $k => $sel_val) {
                $nom = ($sel_val === 'CUSTOM') ? trim((string)($r_nombres_custom[$k] ?? '')) : trim((string)$sel_val);
                $js = (string)($r_json[$k] ?? '');
                if ($nom !== '' && $js !== '') {
                    $rubricas_arr[] = [
                        'codigo' => 'R' . ($k + 1),
                        'nombre' => $nom,
                        'porcentaje_total' => floatval($r_porcentajes[$k] ?? 0),
                        'cantidad' => intval($r_cantidades[$k] ?? 1),
                        'valor' => floatval($r_valores[$k] ?? 0),
                        'contenido_json' => $js,
                    ];
                }
            }
        }

        $data['evaluaciones_json'] = json_encode($evaluaciones_arr, JSON_UNESCAPED_UNICODE);
        $data['cronograma_json'] = json_encode($cronograma_arr, JSON_UNESCAPED_UNICODE);
        $data['rubricas_json'] = json_encode($rubricas_arr, JSON_UNESCAPED_UNICODE);

        $data = $this->filterTableColumns('plan_estudios', $data);
        $plan = PlanEstudio::create($data);

        $this->sincronizarPlantillaSilabo($plan, $evaluaciones_arr, $cronograma_arr, $rubricas_arr);

        return redirect()->route('cursos.index')->with('success', '¡Curso añadido exitosamente al plan de estudios con su ficha técnica oficial!');
    }

    /**
     * Muestra el formulario para editar una materia del plan de estudios.
     */
    public function edit($id)
    {
        $this->ensurePlanEstudiosColumns();

        $curso = PlanEstudio::findOrFail($id);
        $programas = Programa::orderBy('categoria', 'asc')->orderBy('nombre_programa', 'asc')->get();

        // Cargar datos de sílabo si existen
        $id_silabo = null;
        try {
            $silabo = DB::table('silabos')
                ->where('id_plan', $id)
                ->orderBy('id_silabo', 'desc')
                ->first();
            if ($silabo) $id_silabo = $silabo->id_silabo;
        } catch (\Throwable $e) {
            $id_silabo = null;
        }

        $evaluaciones = [];
        if (!empty($curso->evaluaciones_json)) {
            $decodedEval = json_decode($curso->evaluaciones_json, true);
            if (is_array($decodedEval) && !empty($decodedEval)) {
                $evaluaciones = $decodedEval;
            }
        }
        if (empty($evaluaciones) && $id_silabo) {
            try {
                $evaluaciones = DB::table('silabo_evaluacion')
                    ->where('id_silabo', $id_silabo)
                    ->orderBy('id_evaluacion', 'asc')
                    ->get()
                    ->map(fn($item) => (array)$item)
                    ->toArray();
            } catch (\Throwable $e) {
                $evaluaciones = [];
            }
        }

        $cronograma_data = [];
        if (!empty($curso->cronograma_json)) {
            $decodedCro = json_decode($curso->cronograma_json, true);
            if (is_array($decodedCro) && !empty($decodedCro)) {
                $cronograma_data = $decodedCro;
            }
        }
        if (empty($cronograma_data) && $id_silabo) {
            try {
                $cronograma_data = DB::table('silabo_cronograma')
                    ->where('id_silabo', $id_silabo)
                    ->orderByRaw('CAST(semana AS UNSIGNED) ASC')
                    ->get()
                    ->map(fn($item) => (array)$item)
                    ->toArray();
            } catch (\Throwable $e) {
                $cronograma_data = [];
            }
        }
        if (!empty($cronograma_data)) {
            $this->depurarCronogramaSegunEvaluacion($cronograma_data, $evaluaciones);
        } else {
            for ($i = 1; $i <= 15; $i++) {
                $cronograma_data[] = [
                    'semana' => $i,
                    'fecha' => '',
                    'actividad' => '',
                    'tareas' => '',
                    'actividades_moodle' => '',
                    'modalidad_trabajo' => 'Asincrónico'
                ];
            }
        }

        $rubricas_data = [];
        if (!empty($curso->rubricas_json)) {
            $decodedRub = json_decode($curso->rubricas_json, true);
            if (is_array($decodedRub) && !empty($decodedRub)) {
                $rubricas_data = $decodedRub;
            }
        }
        if (empty($rubricas_data) && $id_silabo) {
            try {
                $rubricas_data = DB::table('silabo_rubricas')
                    ->where('id_silabo', $id_silabo)
                    ->orderBy('id_rubrica', 'asc')
                    ->get()
                    ->map(fn($item) => (array)$item)
                    ->toArray();
            } catch (\Throwable $e) {
                $rubricas_data = [];
            }
        }

        return view('cursos.edit', compact('curso', 'programas', 'cronograma_data', 'evaluaciones', 'rubricas_data'));
    }

    /**
     * Actualiza una materia del plan de estudios con toda su ficha técnica.
     */
    public function update(Request $request, $id)
    {
        $this->ensurePlanEstudiosColumns();
        $curso = PlanEstudio::findOrFail($id);

        $request->validate([
            'id_programa' => 'required|exists:programas,id_programa',
            'cuatrimestre' => 'required|string|max:100',
            'codigo' => 'required|string|max:50',
            'materia' => 'required|string|max:255',
            'creditos' => 'nullable|integer|min:0',
        ]);

        $data = $request->except([
            'adjunto_pdf', '_token', 'active_tab',
            'rubros', 'porcentajes', 'cantidades_eval', 'tipos_moodle_eval', 'anexos_eval',
            'c_semana', 'c_fecha', 'c_actividad', 'c_tareas', 'c_moodle', 'c_modalidad',
            'rubrica_nombre_sel', 'rubrica_nombre_custom', 'rubrica_porcentaje', 'rubrica_cantidad', 'rubrica_valor', 'rubrica_json'
        ]);

        // Formatear duración
        $duracion_input = trim($request->input('duracion', '15'));
        $data['duracion'] = is_numeric($duracion_input) ? ($duracion_input . ' semanas') : ($duracion_input ?: '15 semanas');

        // Distribución horas
        $h_teo = (int)$request->input('horas_teoricas', 3);
        $h_prac = (int)$request->input('horas_practicas', 1);
        $h_ind = (int)$request->input('horas_independientes', 8);
        $h_tot = (int)$request->input('horas_totales', ($h_teo + $h_prac + $h_ind));
        $data['horas_teoricas'] = $h_teo;
        $data['horas_practicas'] = $h_prac;
        $data['horas_independientes'] = $h_ind;
        $data['horas_totales'] = $h_tot;

        if (empty($request->input('distribucion_horas'))) {
            $tPrac = ($h_prac === 1) ? "1 hora práctica" : "{$h_prac} horas prácticas";
            $tTeo = ($h_teo === 1) ? "1 hora teórica" : "{$h_teo} horas teóricas";
            $tInd = ($h_ind === 1) ? "1 hora de estudio independiente" : "{$h_ind} horas de estudio independiente";
            $data['distribucion_horas'] = "Este curso comprende un total de {$h_tot} horas distribuidas en {$tTeo}, {$tPrac} y {$tInd}.";
        } else {
            $data['distribucion_horas'] = $request->input('distribucion_horas');
        }

        // Precio
        if ($request->filled('precio') && is_numeric($request->precio)) {
            $data['precio'] = $request->precio;
        } else {
            $programa = Programa::find($request->id_programa);
            $data['precio'] = $programa ? $programa->costo_materia : $curso->precio;
        }

        if ($request->hasFile('adjunto_pdf')) {
            $file = $request->file('adjunto_pdf');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $dest = public_path('uploads/planes_estudio');
            if (!file_exists($dest)) {
                mkdir($dest, 0755, true);
            }
            $file->move($dest, $filename);
            $data['adjunto_pdf'] = $filename;
        }

        // Re-generar arreglos estructurados y resúmenes HTML para evaluación, cronograma y rúbricas
        $rubros = $request->input('rubros', []);
        $porcentajes = $request->input('porcentajes', []);
        $cantidades_eval = $request->input('cantidades_eval', []);
        $tipos_moodle = $request->input('tipos_moodle_eval', []);
        $anexos_eval = $request->input('anexos_eval', []);

        $evaluaciones_arr = [];
        if (is_array($rubros)) {
            foreach ($rubros as $k => $nom) {
                $nom = trim((string)$nom);
                if ($nom !== '') {
                    $evaluaciones_arr[] = [
                        'rubro' => $nom,
                        'porcentaje' => floatval($porcentajes[$k] ?? 0),
                        'cantidad' => intval($cantidades_eval[$k] ?? 1),
                        'anexo' => (string)($anexos_eval[$k] ?? ''),
                        'tipo_moodle' => (string)($tipos_moodle[$k] ?? ''),
                    ];
                }
            }
        }

        if (!empty($evaluaciones_arr)) {
            $evalHtml = '<table width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; border: 1px solid #cbd5e1; font-size: 10pt;">';
            $evalHtml .= '<tr style="background-color: #f1f5f9; font-weight: bold;"><th>Rubro</th><th style="text-align:center;">%</th><th style="text-align:center;">Cant.</th><th style="text-align:center;">Valor Unit.</th></tr>';
            $totP = 0;
            foreach ($evaluaciones_arr as $ev) {
                $p = $ev['porcentaje'];
                $totP += $p;
                $cant = $ev['cantidad'];
                $unit = $cant > 0 ? round($p / $cant, 2) : $p;
                $evalHtml .= "<tr><td>{$ev['rubro']}</td><td style='text-align:center;'>{$p}%</td><td style='text-align:center;'>{$cant}</td><td style='text-align:center;'>{$unit}%</td></tr>";
            }
            $evalHtml .= "<tr style='font-weight: bold; background-color: #f8fafc;'><td style='text-align: right;'>TOTAL:</td><td style='text-align:center;'>{$totP}%</td><td colspan='2'></td></tr>";
            $evalHtml .= '</table>';
            $data['evaluacion_aprendizajes'] = $evalHtml;
        } else {
            $data['evaluacion_aprendizajes'] = '';
        }

        $semanas = $request->input('c_semana', []);
        $fechas = $request->input('c_fecha', []);
        $actividades = $request->input('c_actividad', []);
        $tareas_post = $request->input('c_tareas', []);
        $modalidades = $request->input('c_modalidad', []);
        $moodle_post = $request->input('c_moodle', []);

        $cronograma_arr = [];
        if (is_array($semanas)) {
            foreach ($semanas as $k => $sem) {
                $cronograma_arr[] = [
                    'semana' => intval($sem),
                    'fecha' => !empty($fechas[$k]) ? (string)$fechas[$k] : '',
                    'actividad' => (string)($actividades[$k] ?? ''),
                    'tareas' => (string)($tareas_post[$k] ?? ''),
                    'modalidad_trabajo' => (string)($modalidades[$k] ?? 'Asincrónico'),
                    'actividades_moodle' => (string)($moodle_post[$k] ?? ''),
                ];
            }
        }

        if (!empty($cronograma_arr)) {
            $cronoHtml = '<table width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; border: 1px solid #cbd5e1; font-size: 9.5pt;">';
            $cronoHtml .= '<tr style="background-color: #f1f5f9; font-weight: bold;"><th style="width: 12%;">Semana</th><th style="width: 40%;">Actividades / Contenidos</th><th style="width: 33%;">Tareas / Evaluación</th><th style="width: 15%;">Modalidad</th></tr>';
            foreach ($cronograma_arr as $cro) {
                $semLabel = $cro['semana'];
                if (!empty($cro['fecha'])) {
                    $ts = strtotime($cro['fecha']);
                    $fechaFmt = $ts ? date('d/m/Y', $ts) : $cro['fecha'];
                    $semLabel .= " ({$fechaFmt})";
                }
                $cronoHtml .= "<tr><td style='text-align:center; font-weight: bold;'>{$semLabel}</td><td>{$cro['actividad']}</td><td>{$cro['tareas']}</td><td style='text-align:center;'>{$cro['modalidad_trabajo']}</td></tr>";
            }
            $cronoHtml .= '</table>';
            $data['cronograma'] = $cronoHtml;
        }

        $r_nombres_sel = $request->input('rubrica_nombre_sel', []);
        $r_nombres_custom = $request->input('rubrica_nombre_custom', []);
        $r_porcentajes = $request->input('rubrica_porcentaje', []);
        $r_cantidades = $request->input('rubrica_cantidad', []);
        $r_valores = $request->input('rubrica_valor', []);
        $r_json = $request->input('rubrica_json', []);

        $rubricas_arr = [];
        if (is_array($r_nombres_sel)) {
            foreach ($r_nombres_sel as $k => $sel_val) {
                $nom = ($sel_val === 'CUSTOM') ? trim((string)($r_nombres_custom[$k] ?? '')) : trim((string)$sel_val);
                $js = (string)($r_json[$k] ?? '');
                if ($nom !== '' && $js !== '') {
                    $rubricas_arr[] = [
                        'codigo' => 'R' . ($k + 1),
                        'nombre' => $nom,
                        'porcentaje_total' => floatval($r_porcentajes[$k] ?? 0),
                        'cantidad' => intval($r_cantidades[$k] ?? 1),
                        'valor' => floatval($r_valores[$k] ?? 0),
                        'contenido_json' => $js,
                    ];
                }
            }
        }

        $data['evaluaciones_json'] = json_encode($evaluaciones_arr, JSON_UNESCAPED_UNICODE);
        $data['cronograma_json'] = json_encode($cronograma_arr, JSON_UNESCAPED_UNICODE);
        $data['rubricas_json'] = json_encode($rubricas_arr, JSON_UNESCAPED_UNICODE);

        $data = $this->filterTableColumns('plan_estudios', $data);
        $curso->update($data);

        $this->sincronizarPlantillaSilabo($curso, $evaluaciones_arr, $cronograma_arr, $rubricas_arr);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '¡Cambios guardados exitosamente! Puede continuar editando.',
                'curso' => [
                    'id_plan' => $curso->id_plan,
                    'codigo' => $curso->codigo,
                    'materia' => $curso->materia,
                ]
            ]);
        }

        return redirect()
            ->route('cursos.edit', $curso->id_plan)
            ->with('success', '¡Cambios guardados exitosamente! Puede continuar editando.')
            ->with('active_tab', $request->input('active_tab', 'ficha_tecnica'));
    }

    /**
     * Elimina una materia del plan de estudios si no tiene cursos activos.
     */
    public function destroy($id)
    {
        $curso = PlanEstudio::findOrFail($id);

        $activosCount = \App\Models\CursoActivo::where('id_plan', $id)->count();
        if ($activosCount > 0) {
            return redirect()->route('cursos.index')->with('error', "No se puede eliminar '{$curso->materia}' porque tiene {$activosCount} grupo(s) o curso(s) activo(s) vinculados.");
        }

        // Limpiar plantillas de sílabo asociadas al plan
        try {
            if (Schema::hasTable('silabos')) {
                $silabosIds = DB::table('silabos')->where('id_plan', $id)->pluck('id_silabo')->toArray();
                if (!empty($silabosIds)) {
                    if (Schema::hasTable('silabo_evaluacion')) {
                        DB::table('silabo_evaluacion')->whereIn('id_silabo', $silabosIds)->delete();
                    }
                    if (Schema::hasTable('silabo_cronograma')) {
                        DB::table('silabo_cronograma')->whereIn('id_silabo', $silabosIds)->delete();
                    }
                    if (Schema::hasTable('silabo_rubricas')) {
                        DB::table('silabo_rubricas')->whereIn('id_silabo', $silabosIds)->delete();
                    }
                    DB::table('silabos')->whereIn('id_silabo', $silabosIds)->delete();
                }
            }
        } catch (\Throwable $e) {
            // Continuar
        }

        $curso->delete();

        return redirect()->route('cursos.index')->with('success', 'Materia eliminada del plan de estudios exitosamente.');
    }

    /**
     * Genera y descarga el Descriptor Oficial de Curso en formato Microsoft Word (.doc).
     */
    public function generarDescriptorDoc($id)
    {
        return \App\Services\DescriptorDocService::generar((int)$id);
    }

    /**
     * Genera y transmite el Descriptor Oficial de Curso en formato PDF.
     */
    public function generarDescriptorPdf($id)
    {
        return \App\Services\DescriptorPdfService::generar((int)$id);
    }

    /**
     * Depura actividades Moodle huérfanas o excedentes respecto a la tabla de evaluación.
     */
    private function depurarCronogramaSegunEvaluacion(array &$cronograma_data, array $evaluaciones): void
    {
        if (empty($evaluaciones) || empty($cronograma_data)) {
            return;
        }

        $rubrosCanon = [];
        $rubrosMax = [];
        foreach ($evaluaciones as $ev) {
            $nom = trim($ev['rubro'] ?? '');
            $cant = intval($ev['cantidad'] ?? 0);
            if ($nom !== '' && $cant > 0) {
                $key = mb_strtolower($nom, 'UTF-8');
                $rubrosCanon[$key] = $nom;
                $rubrosMax[$nom] = ($rubrosMax[$nom] ?? 0) + $cant;
            }
        }

        if (empty($rubrosCanon)) {
            return;
        }

        $usados = [];
        foreach ($cronograma_data as &$cro) {
            $jsonRaw = $cro['actividades_moodle'] ?? '';
            if (empty($jsonRaw) || $jsonRaw === '[]') {
                continue;
            }

            $acts = json_decode($jsonRaw, true);
            if (!is_array($acts)) {
                continue;
            }

            $actsValidas = [];
            $modificado = false;

            foreach ($acts as $act) {
                $kTipo = mb_strtolower(trim($act['tipo'] ?? ''), 'UTF-8');
                $kNom = mb_strtolower(trim($act['nombre'] ?? ''), 'UTF-8');
                $canon = $rubrosCanon[$kTipo] ?? ($rubrosCanon[$kNom] ?? null);

                if ($canon !== null && ($usados[$canon] ?? 0) < ($rubrosMax[$canon] ?? 0)) {
                    $usados[$canon] = ($usados[$canon] ?? 0) + 1;
                    $act['tipo'] = $canon;
                    if (mb_strtolower(trim($act['nombre'] ?? ''), 'UTF-8') === mb_strtolower($canon, 'UTF-8')) {
                        $act['nombre'] = '';
                    }
                    $actsValidas[] = $act;
                } else {
                    $modificado = true;
                }
            }

            if ($modificado) {
                $nuevoJson = !empty($actsValidas) ? json_encode($actsValidas, JSON_UNESCAPED_UNICODE) : '';
                $cro['actividades_moodle'] = $nuevoJson;
                if (empty($actsValidas)) {
                    $cro['tareas'] = '';
                }
                if (!empty($cro['id_cronograma'])) {
                    DB::table('silabo_cronograma')
                        ->where('id_cronograma', $cro['id_cronograma'])
                        ->update([
                            'actividades_moodle' => $nuevoJson,
                            'tareas' => $cro['tareas'] ?? '',
                        ]);
                }
            }
        }
        unset($cro);
    }
}

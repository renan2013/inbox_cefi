<?php

namespace App\Http\Controllers;

use App\Models\PlanEstudio;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CursoController extends Controller
{
    /**
     * Asegura de forma preventiva que todas las columnas de la ficha curricular existan en la tabla.
     */
    protected function ensurePlanEstudiosColumns()
    {
        try {
            $cols = [
                'duracion' => "VARCHAR(100) NULL DEFAULT '15 semanas'",
                'distribucion_horas' => "TEXT NULL",
                'horas_teoricas' => "INT NULL DEFAULT 3",
                'horas_practicas' => "INT NULL DEFAULT 1",
                'horas_independientes' => "INT NULL DEFAULT 8",
                'horas_totales' => "INT NULL DEFAULT 12",
                'modalidad' => "VARCHAR(100) NULL DEFAULT 'Virtual (aprendizaje electrónico)'",
                'naturaleza' => "VARCHAR(100) NULL DEFAULT 'Teórico-práctica'",
                'correquisitos' => "TEXT NULL",
                'nivel' => "VARCHAR(100) NULL DEFAULT NULL",
                'profesor' => "VARCHAR(255) NULL DEFAULT NULL",
                'descripcion_curso' => "LONGTEXT NULL",
                'contenidos_tematicos' => "LONGTEXT NULL",
                'metodologia_ensenanza' => "LONGTEXT NULL",
                'estrategias_aprendizaje' => "LONGTEXT NULL",
                'evaluacion_aprendizajes' => "LONGTEXT NULL",
                'recursos_didacticos' => "LONGTEXT NULL",
                'cronograma' => "LONGTEXT NULL",
                'guias_evaluacion' => "LONGTEXT NULL",
                'bibliografia' => "LONGTEXT NULL",
                'fecha_descriptor_pdf' => "DATETIME NULL DEFAULT NULL"
            ];

            foreach ($cols as $col => $type) {
                if (!Schema::hasColumn('plan_estudios', $col)) {
                    DB::statement("ALTER TABLE plan_estudios ADD COLUMN `{$col}` {$type}");
                }
            }
        } catch (\Throwable $e) {
            // Silenciosamente continuar si no hay permisos ALTER o si ya existen
        }
    }

    /**
     * Muestra el listado de materias/planes de estudio.
     */
    public function index(Request $request)
    {
        $this->ensurePlanEstudiosColumns();
        $query = PlanEstudio::with('programa');

        // Búsqueda por término (materia o código)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('materia', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%");
            });
        }

        // Filtro por Programa
        if ($request->filled('programa_id')) {
            $query->where('id_programa', $request->programa_id);
        }

        $cursos = $query->orderBy('cuatrimestre', 'asc')->paginate(15)->withQueryString();
        $programas = Programa::orderBy('categoria', 'asc')->orderBy('nombre_programa', 'asc')->get();

        return view('cursos.index', compact('cursos', 'programas'));
    }

    /**
     * Muestra el formulario completo oficial (UNELA) para añadir un nuevo curso al plan de estudios.
     */
    public function create()
    {
        $this->ensurePlanEstudiosColumns();
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

        // Construir representación HTML para secciones de evaluación y cronograma si están vacías
        $rubros = $request->input('rubros', []);
        $porcentajes = $request->input('porcentajes', []);
        $cantidades_eval = $request->input('cantidades_eval', []);
        $tipos_moodle = $request->input('tipos_moodle_eval', []);
        $anexos_eval = $request->input('anexos_eval', []);

        if (empty($data['evaluacion_aprendizajes']) && !empty($rubros)) {
            $evalHtml = '<table width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; border: 1px solid #cbd5e1; font-size: 10pt;">';
            $evalHtml .= '<tr style="background-color: #f1f5f9; font-weight: bold;"><th>Rubro</th><th style="text-align:center;">%</th><th style="text-align:center;">Cant.</th><th style="text-align:center;">Valor Unit.</th></tr>';
            $totP = 0;
            foreach ($rubros as $k => $nom) {
                if (!empty($nom)) {
                    $p = floatval($porcentajes[$k] ?? 0);
                    $totP += $p;
                    $cant = intval($cantidades_eval[$k] ?? 1);
                    $unit = $cant > 0 ? round($p / $cant, 2) : $p;
                    $evalHtml .= "<tr><td>{$nom}</td><td style='text-align:center;'>{$p}%</td><td style='text-align:center;'>{$cant}</td><td style='text-align:center;'>{$unit}%</td></tr>";
                }
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

        if (empty($data['cronograma']) && !empty($semanas)) {
            $cronoHtml = '<table width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; border: 1px solid #cbd5e1; font-size: 9.5pt;">';
            $cronoHtml .= '<tr style="background-color: #f1f5f9; font-weight: bold;"><th style="width: 8%;">Semana</th><th style="width: 42%;">Actividades / Contenidos</th><th style="width: 35%;">Tareas / Evaluación</th><th style="width: 15%;">Modalidad</th></tr>';
            foreach ($semanas as $k => $sem) {
                $act = $actividades[$k] ?? '';
                $tar = $tareas_post[$k] ?? '';
                $mod = $modalidades[$k] ?? 'Asincrónico';
                $cronoHtml .= "<tr><td style='text-align:center; font-weight: bold;'>{$sem}</td><td>{$act}</td><td>{$tar}</td><td style='text-align:center;'>{$mod}</td></tr>";
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

        $plan = PlanEstudio::create($data);

        // Guardar plantilla de sílabo asociada si existe la tabla
        try {
            if (Schema::hasTable('silabos')) {
                $id_silabo = DB::table('silabos')->insertGetId([
                    'id_plan' => $plan->id_plan,
                    'id_profesor' => 0,
                    'descripcion' => $plan->descripcion_curso ?? '',
                    'metodologia' => $plan->metodologia_ensenanza ?? '',
                    'estrategias_aprendizaje' => $plan->estrategias_aprendizaje ?? '',
                    'recursos_aprendizaje' => $plan->recursos_didacticos ?? '',
                    'contenidos' => $plan->contenidos_tematicos ?? '',
                    'cronograma' => $plan->cronograma ?? '',
                    'bibliografia' => $plan->bibliografia ?? '',
                    'modalidad' => $plan->modalidad ?? 'Virtual (aprendizaje electrónico)',
                    'horario' => '',
                    'tipo_curso' => '',
                    'objetivo_general' => $plan->objetivo_general ?? '',
                    'objetivos_especificos' => $plan->objetivos_especificos ?? '',
                    'anexos' => '',
                    'fecha_creacion' => now()
                ]);

                if ($id_silabo) {
                    if (Schema::hasTable('silabo_evaluacion')) {
                        foreach ($rubros as $k => $nom) {
                            $nom = trim($nom);
                            if (!empty($nom)) {
                                DB::table('silabo_evaluacion')->insert([
                                    'id_silabo' => $id_silabo,
                                    'rubro' => $nom,
                                    'porcentaje' => floatval($porcentajes[$k] ?? 0),
                                    'cantidad' => intval($cantidades_eval[$k] ?? 1),
                                    'anexo' => $anexos_eval[$k] ?? '',
                                    'tipo_moodle' => $tipos_moodle[$k] ?? ''
                                ]);
                            }
                        }
                    }

                    if (Schema::hasTable('silabo_cronograma')) {
                        foreach ($semanas as $k => $sem) {
                            DB::table('silabo_cronograma')->insert([
                                'id_silabo' => $id_silabo,
                                'semana' => intval($sem),
                                'fecha' => !empty($fechas[$k]) ? $fechas[$k] : null,
                                'actividad' => $actividades[$k] ?? '',
                                'tareas' => $tareas_post[$k] ?? '',
                                'modalidad_trabajo' => $modalidades[$k] ?? 'Asincrónico',
                                'actividades_moodle' => $moodle_post[$k] ?? ''
                            ]);
                        }
                    }

                    if (Schema::hasTable('silabo_rubricas') && is_array($r_nombres_sel)) {
                        foreach ($r_nombres_sel as $k => $sel_val) {
                            $nom = ($sel_val === 'CUSTOM') ? ($r_nombres_custom[$k] ?? '') : $sel_val;
                            $js = $r_json[$k] ?? '';
                            if (!empty($nom) && !empty($js)) {
                                DB::table('silabo_rubricas')->insert([
                                    'id_silabo' => $id_silabo,
                                    'codigo' => 'R' . ($k + 1),
                                    'nombre' => $nom,
                                    'porcentaje_total' => floatval($r_porcentajes[$k] ?? 0),
                                    'cantidad' => intval($r_cantidades[$k] ?? 1),
                                    'valor' => floatval($r_valores[$k] ?? 0),
                                    'contenido_json' => $js
                                ]);
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silenciosamente continuar
        }

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
        if (Schema::hasTable('silabos')) {
            $silabo = DB::table('silabos')
                ->where('id_plan', $id)
                ->orderBy('id_silabo', 'desc')
                ->first();
            if ($silabo) $id_silabo = $silabo->id_silabo;
        }

        $evaluaciones = [];
        if ($id_silabo && Schema::hasTable('silabo_evaluacion')) {
            $evaluaciones = DB::table('silabo_evaluacion')
                ->where('id_silabo', $id_silabo)
                ->orderBy('id_evaluacion', 'asc')
                ->get()
                ->map(fn($item) => (array)$item)
                ->toArray();
        }

        $cronograma_data = [];
        if ($id_silabo && Schema::hasTable('silabo_cronograma')) {
            $cronograma_data = DB::table('silabo_cronograma')
                ->where('id_silabo', $id_silabo)
                ->orderBy('semana', 'asc')
                ->get()
                ->map(fn($item) => (array)$item)
                ->toArray();
        }
        if (empty($cronograma_data)) {
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
        if ($id_silabo && Schema::hasTable('silabo_rubricas')) {
            $rubricas_data = DB::table('silabo_rubricas')
                ->where('id_silabo', $id_silabo)
                ->orderBy('id_rubrica', 'asc')
                ->get()
                ->map(fn($item) => (array)$item)
                ->toArray();
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

        // Re-generar resúmenes HTML si se editaron rubros o cronograma
        $rubros = $request->input('rubros', []);
        $porcentajes = $request->input('porcentajes', []);
        $cantidades_eval = $request->input('cantidades_eval', []);
        $tipos_moodle = $request->input('tipos_moodle_eval', []);
        $anexos_eval = $request->input('anexos_eval', []);

        if (empty($data['evaluacion_aprendizajes']) && !empty($rubros)) {
            $evalHtml = '<table width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; border: 1px solid #cbd5e1; font-size: 10pt;">';
            $evalHtml .= '<tr style="background-color: #f1f5f9; font-weight: bold;"><th>Rubro</th><th style="text-align:center;">%</th><th style="text-align:center;">Cant.</th><th style="text-align:center;">Valor Unit.</th></tr>';
            $totP = 0;
            foreach ($rubros as $k => $nom) {
                if (!empty($nom)) {
                    $p = floatval($porcentajes[$k] ?? 0);
                    $totP += $p;
                    $cant = intval($cantidades_eval[$k] ?? 1);
                    $unit = $cant > 0 ? round($p / $cant, 2) : $p;
                    $evalHtml .= "<tr><td>{$nom}</td><td style='text-align:center;'>{$p}%</td><td style='text-align:center;'>{$cant}</td><td style='text-align:center;'>{$unit}%</td></tr>";
                }
            }
            $evalHtml .= "<tr style='font-weight: bold; background-color: #f8fafc;'><td style='text-align: right;'>TOTAL:</td><td style='text-align:center;'>{$totP}%</td><td colspan='2'></td></tr>";
            $evalHtml .= '</table>';
            $data['evaluacion_aprendizajes'] = $evalHtml;
        }

        $semanas = $request->input('c_semana', []);
        $fechas = $request->input('c_fecha', []);
        $actividades = $request->input('c_actividad', []);
        $tareas_post = $request->input('c_tareas', []);
        $modalidades = $request->input('c_modalidad', []);
        $moodle_post = $request->input('c_moodle', []);

        if (empty($data['cronograma']) && !empty($semanas)) {
            $cronoHtml = '<table width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; border: 1px solid #cbd5e1; font-size: 9.5pt;">';
            $cronoHtml .= '<tr style="background-color: #f1f5f9; font-weight: bold;"><th style="width: 8%;">Semana</th><th style="width: 42%;">Actividades / Contenidos</th><th style="width: 35%;">Tareas / Evaluación</th><th style="width: 15%;">Modalidad</th></tr>';
            foreach ($semanas as $k => $sem) {
                $act = $actividades[$k] ?? '';
                $tar = $tareas_post[$k] ?? '';
                $mod = $modalidades[$k] ?? 'Asincrónico';
                $cronoHtml .= "<tr><td style='text-align:center; font-weight: bold;'>{$sem}</td><td>{$act}</td><td>{$tar}</td><td style='text-align:center;'>{$mod}</td></tr>";
            }
            $cronoHtml .= '</table>';
            $data['cronograma'] = $cronoHtml;
        }

        $curso->update($data);

        // Sincronizar con plantilla de sílabo
        try {
            if (Schema::hasTable('silabos')) {
                $silabo = DB::table('silabos')->where('id_plan', $id)->first();
                if ($silabo) {
                    $id_silabo = $silabo->id_silabo;
                    DB::table('silabos')->where('id_silabo', $id_silabo)->update([
                        'descripcion' => $curso->descripcion_curso ?? '',
                        'metodologia' => $curso->metodologia_ensenanza ?? '',
                        'estrategias_aprendizaje' => $curso->estrategias_aprendizaje ?? '',
                        'recursos_aprendizaje' => $curso->recursos_didacticos ?? '',
                        'contenidos' => $curso->contenidos_tematicos ?? '',
                        'cronograma' => $curso->cronograma ?? '',
                        'bibliografia' => $curso->bibliografia ?? '',
                        'modalidad' => $curso->modalidad ?? 'Virtual (aprendizaje electrónico)',
                        'objetivo_general' => $curso->objetivo_general ?? '',
                        'objetivos_especificos' => $curso->objetivos_especificos ?? ''
                    ]);
                } else {
                    $id_silabo = DB::table('silabos')->insertGetId([
                        'id_plan' => $id,
                        'id_profesor' => 0,
                        'descripcion' => $curso->descripcion_curso ?? '',
                        'metodologia' => $curso->metodologia_ensenanza ?? '',
                        'estrategias_aprendizaje' => $curso->estrategias_aprendizaje ?? '',
                        'recursos_aprendizaje' => $curso->recursos_didacticos ?? '',
                        'contenidos' => $curso->contenidos_tematicos ?? '',
                        'cronograma' => $curso->cronograma ?? '',
                        'bibliografia' => $curso->bibliografia ?? '',
                        'modalidad' => $curso->modalidad ?? 'Virtual (aprendizaje electrónico)',
                        'horario' => '',
                        'tipo_curso' => '',
                        'objetivo_general' => $curso->objetivo_general ?? '',
                        'objetivos_especificos' => $curso->objetivos_especificos ?? '',
                        'anexos' => '',
                        'fecha_creacion' => now()
                    ]);
                }

                if ($id_silabo) {
                    if (Schema::hasTable('silabo_evaluacion')) {
                        DB::table('silabo_evaluacion')->where('id_silabo', $id_silabo)->delete();
                        foreach ($rubros as $k => $nom) {
                            $nom = trim($nom);
                            if (!empty($nom)) {
                                DB::table('silabo_evaluacion')->insert([
                                    'id_silabo' => $id_silabo,
                                    'rubro' => $nom,
                                    'porcentaje' => floatval($porcentajes[$k] ?? 0),
                                    'cantidad' => intval($cantidades_eval[$k] ?? 1),
                                    'anexo' => $anexos_eval[$k] ?? '',
                                    'tipo_moodle' => $tipos_moodle[$k] ?? ''
                                ]);
                            }
                        }
                    }

                    if (Schema::hasTable('silabo_cronograma')) {
                        DB::table('silabo_cronograma')->where('id_silabo', $id_silabo)->delete();
                        foreach ($semanas as $k => $sem) {
                            DB::table('silabo_cronograma')->insert([
                                'id_silabo' => $id_silabo,
                                'semana' => intval($sem),
                                'fecha' => !empty($fechas[$k]) ? $fechas[$k] : null,
                                'actividad' => $actividades[$k] ?? '',
                                'tareas' => $tareas_post[$k] ?? '',
                                'modalidad_trabajo' => $modalidades[$k] ?? 'Asincrónico',
                                'actividades_moodle' => $moodle_post[$k] ?? ''
                            ]);
                        }
                    }

                    $r_nombres_sel = $request->input('rubrica_nombre_sel', []);
                    $r_nombres_custom = $request->input('rubrica_nombre_custom', []);
                    $r_porcentajes = $request->input('rubrica_porcentaje', []);
                    $r_cantidades = $request->input('rubrica_cantidad', []);
                    $r_valores = $request->input('rubrica_valor', []);
                    $r_json = $request->input('rubrica_json', []);

                    if (Schema::hasTable('silabo_rubricas') && is_array($r_nombres_sel)) {
                        DB::table('silabo_rubricas')->where('id_silabo', $id_silabo)->delete();
                        foreach ($r_nombres_sel as $k => $sel_val) {
                            $nom = ($sel_val === 'CUSTOM') ? ($r_nombres_custom[$k] ?? '') : $sel_val;
                            $js = $r_json[$k] ?? '';
                            if (!empty($nom) && !empty($js)) {
                                DB::table('silabo_rubricas')->insert([
                                    'id_silabo' => $id_silabo,
                                    'codigo' => 'R' . ($k + 1),
                                    'nombre' => $nom,
                                    'porcentaje_total' => floatval($r_porcentajes[$k] ?? 0),
                                    'cantidad' => intval($r_cantidades[$k] ?? 1),
                                    'valor' => floatval($r_valores[$k] ?? 0),
                                    'contenido_json' => $js
                                ]);
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silenciosamente continuar
        }

        return redirect()->route('cursos.index')->with('success', '¡Curso/materia actualizado exitosamente!');
    }

    /**
     * Elimina una materia del plan de estudios si no tiene cursos activos.
     */
    public function destroy($id)
    {
        $this->ensurePlanEstudiosColumns();
        $curso = PlanEstudio::findOrFail($id);

        $activosCount = \App\Models\CursoActivo::where('id_plan', $id)->count();
        if ($activosCount > 0) {
            return redirect()->route('cursos.index')->with('error', "No se puede eliminar '{$curso->materia}' porque tiene {$activosCount} grupo(s) o curso(s) activo(s) vinculados.");
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
}

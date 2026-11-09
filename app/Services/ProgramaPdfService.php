<?php

namespace App\Services;

require_once __DIR__ . '/FPDF/fpdf.php';

use FPDF;
use Illuminate\Support\Facades\DB;

class ProgramaPDF extends FPDF
{
    public string $nombre_programa = '';
    public string $categoria = '';
    public string $duracion = '12 meses';
    public string $modalidad = 'Presencial';
    public ?string $header_path = null;
    public float $header_h = 35.0;
    public ?string $hero_path = null;
    public ?string $secondary_path = null;
    public ?string $footer_path = null;

    // Paleta de colores del nuevo diseño CEFI
    public array $verde_oscuro_hdr = [24, 74, 44];     // #184a2c (fondo izquierdo header)
    public array $verde_curva_hdr  = [76, 166, 76];    // #4ca64c (acento curva header)
    public array $azul_hdr         = [17, 97, 155];    // #11619b (azul oscuro institucional para todo el documento)
    public array $azul_marino      = [17, 97, 155];    // #11619b (bloques azules oscuros y títulos en todo el documento)
    public array $verde_lima       = [120, 184, 51];   // #78b833 (cuadro acento título descripción)
    public array $verde_cefi       = [0, 160, 77];     // #00a04d (footer WhatsApp tab y sello)
    public array $teal_tabla       = [17, 97, 155];    // #11619b (encabezado tabla plan de estudios)
    public array $gris_texto       = [75, 85, 99];

    function Header()
    {
        $headerH = 35.0;
        $this->header_h = $headerH;

        // 1. Dibujar el encabezado institucional (degradado verde, curva S azul #11619b y sello CEFI) 100% en vectores nativos PDF
        $this->drawVectorHeaderCurve($headerH);

        // Texto institucional "CENTRO DE ENSEÑANZA Y FORMACION INTEGRAL" en vectores nítidos
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', '', 8.0);
        $this->SetXY(136.0, 8.2);
        $this->Cell(34.0, 4.2, $this->toPdf('CENTRO DE'), 0, 1, 'R');
        $this->SetX(136.0);
        $this->Cell(34.0, 4.2, $this->toPdf('ENSEÑANZA'), 0, 1, 'R');
        $this->SetX(136.0);
        $this->Cell(34.0, 4.2, $this->toPdf('Y FORMACION'), 0, 1, 'R');
        $this->SetX(136.0);
        $this->Cell(34.0, 4.2, $this->toPdf('INTEGRAL'), 0, 1, 'R');

        // Línea vertical blanca divisoria vectorial
        $this->SetDrawColor(255, 255, 255);
        $this->SetLineWidth(0.42);
        $this->Line(173.5, 5.0, 173.5, $headerH - 5.8);

        // Sello circular CEFI PARAUNIVERSITARIO 100% vectorial a la derecha
        $this->drawCefiBadgeRight(191.2, 17.2, 26.2);

        // 2. Textos dinámicos del Programa a la izquierda (sobre el bloque verde oscuro, más abajo y con título más grande)
        [$prefijoCat, $tituloLimpio] = $this->splitTituloModerno($this->nombre_programa, $this->categoria);

        $yCat      = round($headerH * 0.22, 2);
        $yTitulo   = round($headerH * 0.43, 2);
        $yDuracion = round($headerH * 0.74, 2);

        $this->SetTextColor(255, 255, 255);
        $this->SetXY(8.0, $yCat);
        $this->SetFont('Arial', '', 13);
        $this->Cell(122, 5.5, $this->toPdf($prefijoCat), 0, 1, 'L');

        // Título principal más grande (ej. "Diseño Gráfico" / "Diseño Gráfico Digital con IA")
        $this->SetXY(8.0, $yTitulo);
        $lenTitulo = mb_strlen($tituloLimpio, 'UTF-8');
        $fontSizeTitulo = $lenTitulo > 34 ? 18.0 : ($lenTitulo > 26 ? 21.0 : 24.5);
        $this->SetFont('Arial', 'B', $fontSizeTitulo);
        $this->Cell(124, 9.5, $this->toPdf($tituloLimpio), 0, 1, 'L');

        // Duración y Modalidad en una sola línea debajo
        $durClean = rtrim(trim($this->duracion), '.');
        if ($durClean === '') {
            $durClean = '12 meses';
        }
        $modClean = rtrim(trim($this->modalidad), '.');
        if ($modClean === '') {
            $modClean = 'Presencial';
        }

        $this->SetXY(8.0, $yDuracion);
        $this->SetFont('Arial', '', 9.8);
        $this->SetTextColor(240, 248, 242);
        $this->Cell(124, 5, $this->toPdf("Duración: {$durClean}  -  Modalidad: {$modClean}"), 0, 1, 'L');

        $this->SetY($headerH);
    }

    /**
     * Dibuja el fondo del encabezado (degradado verde izquierdo + curva S azul #11619b derecha)
     * utilizando trazos vectoriales nativos de PDF (Bézier cúbica) sin pixelado.
     */
    function drawVectorHeaderCurve(float $headerH): void
    {
        $k = $this->k;
        $hp = $this->h;

        // 1. Degradado horizontal verde en la sección inferior/izquierda (X = 0 a 210, Y = 2.8 a $headerH)
        $steps = 85;
        $startX = 0.0;
        $endX = 210.0;
        $stepW = ($endX - $startX) / $steps;
        $r0 = 10; $g0 = 32;  $b0 = 19;  // Verde muy oscuro izquierdo
        $r1 = 15; $g1 = 136; $b1 = 68;  // Verde esmeralda CEFI hacia el centro/derecha

        for ($i = 0; $i < $steps; $i++) {
            $t = min(1.0, ($i / ($steps - 1)) * 1.35);
            $r = (int)round($r0 + ($r1 - $r0) * $t);
            $g = (int)round($g0 + ($g1 - $g0) * $t);
            $b = (int)round($b0 + ($b1 - $b0) * $t);
            $this->SetFillColor($r, $g, $b);
            $this->Rect($startX + $i * $stepW, 2.8, $stepW + 0.35, $headerH - 2.8, 'F');
        }

        // 2. Sombra suave vectorial bajo el borde de la curva S para dar profundidad
        $yTop = 3.1;
        $yBot = $headerH - 2.8;
        $shadows = [
            ['dx' => -2.2, 'dy' => 1.0, 'rgb' => [11, 88, 45]],
            ['dx' => -1.4, 'dy' => 0.7, 'rgb' => [9, 68, 35]],
            ['dx' => -0.7, 'dy' => 0.35, 'rgb' => [7, 48, 25]],
        ];
        foreach ($shadows as $sh) {
            $this->SetFillColor($sh['rgb'][0], $sh['rgb'][1], $sh['rgb'][2]);
            $sx0  = 103.0 + $sh['dx']; $sy0  = $yTop;
            $scx1 = 123.0 + $sh['dx']; $scy1 = $yTop;
            $scx2 = 131.0 + $sh['dx']; $scy2 = min($headerH, $yBot + $sh['dy']);
            $sx1  = 152.0 + $sh['dx']; $sy1  = min($headerH, $yBot + $sh['dy']);

            $this->_out(sprintf('%.2F %.2F m', $sx0 * $k, ($hp - $sy0) * $k));
            $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
                $scx1 * $k, ($hp - $scy1) * $k,
                $scx2 * $k, ($hp - $scy2) * $k,
                $sx1 * $k,  ($hp - $sy1) * $k
            ));
            $this->_out(sprintf('%.2F %.2F l', 210.0 * $k, ($hp - $sy1) * $k));
            $this->_out(sprintf('%.2F %.2F l', 210.0 * $k, ($hp - 0.0) * $k));
            $this->_out(sprintf('%.2F %.2F l h f', $sx0 * $k, ($hp - 0.0) * $k));
        }

        // 3. Franja superior y bloque derecho con curva S en azul oscuro institucional (#11619b)
        $this->SetFillColor($this->azul_hdr[0], $this->azul_hdr[1], $this->azul_hdr[2]);
        $x0  = 103.0; $y0  = $yTop;
        $cx1 = 123.0; $cy1 = $yTop;
        $cx2 = 131.0; $cy2 = $yBot;
        $x1  = 152.0; $y1  = $yBot;

        $this->_out(sprintf('%.2F %.2F m', 0.0 * $k, ($hp - 0.0) * $k));
        $this->_out(sprintf('%.2F %.2F l', 210.0 * $k, ($hp - 0.0) * $k));
        $this->_out(sprintf('%.2F %.2F l', 210.0 * $k, ($hp - $y1) * $k));
        $this->_out(sprintf('%.2F %.2F l', $x1 * $k, ($hp - $y1) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            $cx2 * $k, ($hp - $cy2) * $k,
            $cx1 * $k, ($hp - $cy1) * $k,
            $x0 * $k,  ($hp - $y0) * $k
        ));
        $this->_out(sprintf('%.2F %.2F l h f', 0.0 * $k, ($hp - $y0) * $k));
    }

    /**
     * Dibuja el emblema circular oficial "CEFI PARAUNIVERSITARIO" 100% en vectores PDF
     * leyendo las curvas originales de public/imgs/logo_cefi_blanco.svg.
     */
    function drawCefiBadgeRight(float $cx, float $cy, float $diameter = 26.2): void
    {
        $svgPath = public_path('imgs/logo_cefi_blanco.svg');
        if (file_exists($svgPath)) {
            $svgContent = @file_get_contents($svgPath);
            if (!empty($svgContent) && $this->renderSvgBadgePaths($svgContent, $cx, $cy, $diameter)) {
                $this->SetLineWidth(0.2);
                return;
            }
        }

        // Respaldo vectorial en caso de que no exista el archivo SVG
        $r = $diameter / 2;
        $this->SetFillColor(255, 255, 255);
        $this->drawVectorCircle($cx, $cy, $r, 'F');
        $this->SetDrawColor(0, 160, 77);
        $this->SetLineWidth(0.8);
        $this->drawVectorCircle($cx, $cy, $r - 0.8, 'D');

        $this->SetXY($cx - 11, $cy - 5.5);
        $this->SetFont('Arial', 'B', 12.5);
        $this->SetTextColor(0, 160, 77);
        $this->Cell(22, 5.5, $this->toPdf('CEFI'), 0, 1, 'C');

        $this->SetXY($cx - 11, $cy);
        $this->SetFont('Arial', 'B', 3.6);
        $this->SetTextColor(17, 97, 155);
        $this->Cell(22, 1.8, $this->toPdf('Centro de Enseñanza'), 0, 1, 'C');
        $this->SetX($cx - 11);
        $this->Cell(22, 1.8, $this->toPdf('y Formación Integral'), 0, 1, 'C');
        $this->SetLineWidth(0.2);
    }

    /**
     * Interpreta los elementos vectoriales (<circle>, <ellipse>, <polygon>, <path>) de logo_cefi_blanco.svg
     * y los emite como primitivas vectoriales nativas en el flujo del PDF.
     */
    function renderSvgBadgePaths(string $svg, float $cx, float $cy, float $diameter): bool
    {
        $scale = $diameter / 1095.0;
        $svgCx = 609.2;
        $svgCy = 545.0;

        $styles = [
            'st0'  => ['fill' => [255, 255, 255], 'op' => 'f'],
            'st1'  => ['fill' => [0, 160, 77],    'op' => 'f*'],
            'st2'  => ['fill' => [17, 97, 155],   'op' => 'f*'],
            'st7'  => ['fill' => [152, 196, 81],  'op' => 'f'],
            'st8'  => ['fill' => [153, 197, 83],  'op' => 'f'],
            'st9'  => ['fill' => [17, 97, 155],   'op' => 'f'],
            'st10' => ['fill' => [8, 42, 58],     'op' => 'f*'],
            'st11' => ['fill' => [11, 68, 103],   'stroke' => [255, 255, 255], 'lw' => 0.08, 'op' => 'B'],
            'st12' => ['fill' => [11, 68, 103],   'op' => 'f'],
            'st13' => ['fill' => [17, 97, 155],   'stroke' => [255, 255, 255], 'lw' => 0.08, 'op' => 'B'],
            'st14' => ['fill' => [255, 255, 255], 'stroke' => [255, 255, 255], 'lw' => 0.05, 'op' => 'B*'],
        ];

        $pattern = '/<(circle|ellipse|polygon|path)\b([^>]*)\/?>/is';
        if (!preg_match_all($pattern, $svg, $matches, PREG_SET_ORDER)) {
            return false;
        }

        $k = $this->k;
        $hp = $this->h;

        foreach ($matches as $m) {
            $tag = strtolower($m[1]);
            $attrStr = $m[2];

            if (!preg_match('/\bclass="([^"]+)"/', $attrStr, $cm)) {
                continue;
            }
            $cls = trim($cm[1]);
            if (!isset($styles[$cls])) {
                continue;
            }
            $st = $styles[$cls];
            $this->SetFillColor($st['fill'][0], $st['fill'][1], $st['fill'][2]);
            if (isset($st['stroke'])) {
                $this->SetDrawColor($st['stroke'][0], $st['stroke'][1], $st['stroke'][2]);
                $this->SetLineWidth($st['lw'] ?? 0.08);
            }

            if ($tag === 'circle') {
                if (preg_match('/\bcx="([^"]+)"/', $attrStr, $cxm) &&
                    preg_match('/\bcy="([^"]+)"/', $attrStr, $cym) &&
                    preg_match('/\br="([^"]+)"/', $attrStr, $rm)) {
                    $px = $cx + ((float)$cxm[1] - $svgCx) * $scale;
                    $py = $cy + ((float)$cym[1] - $svgCy) * $scale;
                    $pr = (float)$rm[1] * $scale;
                    $this->drawVectorCircle($px, $py, $pr, 'F');
                }
            } elseif ($tag === 'ellipse') {
                if (preg_match('/\bcx="([^"]+)"/', $attrStr, $cxm) &&
                    preg_match('/\bcy="([^"]+)"/', $attrStr, $cym) &&
                    preg_match('/\brx="([^"]+)"/', $attrStr, $rxm)) {
                    $px = $cx + ((float)$cxm[1] - $svgCx) * $scale;
                    $py = $cy + ((float)$cym[1] - $svgCy) * $scale;
                    $pr = (float)$rxm[1] * $scale;
                    $this->drawVectorCircle($px, $py, $pr, isset($st['stroke']) ? 'FD' : 'F');
                }
            } elseif ($tag === 'polygon') {
                if (preg_match('/\bpoints="([^"]+)"/', $attrStr, $pm)) {
                    if (preg_match_all('/[-+]?(?:\d+\.\d*|\.\d+|\d+)/', $pm[1], $nums) && count($nums[0]) >= 4) {
                        $coords = $nums[0];
                        $cnt = count($coords);
                        for ($i = 0; $i + 1 < $cnt; $i += 2) {
                            $px = $cx + ((float)$coords[$i] - $svgCx) * $scale;
                            $py = $cy + ((float)$coords[$i + 1] - $svgCy) * $scale;
                            $cmd = ($i === 0) ? 'm' : 'l';
                            $this->_out(sprintf('%.2F %.2F %s', $px * $k, ($hp - $py) * $k, $cmd));
                        }
                        $this->_out('h ' . $st['op']);
                    }
                }
            } elseif ($tag === 'path') {
                if (preg_match('/\bd="([^"]+)"/s', $attrStr, $dm)) {
                    $this->emitSvgPathData($dm[1], $cx, $cy, $svgCx, $svgCy, $scale, $st['op']);
                }
            }
        }

        return true;
    }

    /**
     * Convierte un atributo d="..." de SVG (M, m, L, l, H, h, V, v, C, c, S, s, Z, z)
     * en operadores vectoriales PDF (m, l, c, h) y ejecuta el operador de pintado final.
     */
    function emitSvgPathData(string $d, float $cx, float $cy, float $svgCx, float $svgCy, float $scale, string $paintOp): void
    {
        if (!preg_match_all('/([MmLlHhVvCcSsZz])|([-+]?(?:\d+\.\d*|\.\d+|\d+)(?:[eE][-+]?\d+)?)/', $d, $tokens)) {
            return;
        }

        $items = $tokens[0];
        $n = count($items);
        $idx = 0;
        $cmd = '';
        $prevCmd = '';

        $curX = 0.0;
        $curY = 0.0;
        $startX = 0.0;
        $startY = 0.0;
        $lastCtrlX = 0.0;
        $lastCtrlY = 0.0;
        $hasSubpath = false;

        $k = $this->k;
        $hp = $this->h;

        $toPdfX = fn(float $sx): float => ($cx + ($sx - $svgCx) * $scale) * $k;
        $toPdfY = fn(float $sy): float => ($hp - ($cy + ($sy - $svgCy) * $scale)) * $k;

        while ($idx < $n) {
            $tok = $items[$idx];
            if (ctype_alpha($tok)) {
                $cmd = $tok;
                $idx++;
                if ($cmd === 'Z' || $cmd === 'z') {
                    $this->_out('h');
                    $curX = $startX;
                    $curY = $startY;
                    $prevCmd = $cmd;
                    continue;
                }
            }

            switch ($cmd) {
                case 'M':
                case 'm':
                    if ($idx + 1 >= $n) break 2;
                    $x = (float)$items[$idx++];
                    $y = (float)$items[$idx++];
                    if ($cmd === 'm') {
                        $curX += $x;
                        $curY += $y;
                    } else {
                        $curX = $x;
                        $curY = $y;
                    }
                    $startX = $curX;
                    $startY = $curY;
                    $this->_out(sprintf('%.2F %.2F m', $toPdfX($curX), $toPdfY($curY)));
                    $hasSubpath = true;
                    $prevCmd = $cmd;
                    $cmd = ($cmd === 'm') ? 'l' : 'L';
                    break;

                case 'L':
                case 'l':
                    if ($idx + 1 >= $n) break 2;
                    $x = (float)$items[$idx++];
                    $y = (float)$items[$idx++];
                    if ($cmd === 'l') {
                        $curX += $x;
                        $curY += $y;
                    } else {
                        $curX = $x;
                        $curY = $y;
                    }
                    $this->_out(sprintf('%.2F %.2F l', $toPdfX($curX), $toPdfY($curY)));
                    $prevCmd = $cmd;
                    break;

                case 'H':
                case 'h':
                    if ($idx >= $n) break 2;
                    $x = (float)$items[$idx++];
                    $curX = ($cmd === 'h') ? ($curX + $x) : $x;
                    $this->_out(sprintf('%.2F %.2F l', $toPdfX($curX), $toPdfY($curY)));
                    $prevCmd = $cmd;
                    break;

                case 'V':
                case 'v':
                    if ($idx >= $n) break 2;
                    $y = (float)$items[$idx++];
                    $curY = ($cmd === 'v') ? ($curY + $y) : $y;
                    $this->_out(sprintf('%.2F %.2F l', $toPdfX($curX), $toPdfY($curY)));
                    $prevCmd = $cmd;
                    break;

                case 'C':
                case 'c':
                    if ($idx + 5 >= $n) break 2;
                    $x1 = (float)$items[$idx++];
                    $y1 = (float)$items[$idx++];
                    $x2 = (float)$items[$idx++];
                    $y2 = (float)$items[$idx++];
                    $x  = (float)$items[$idx++];
                    $y  = (float)$items[$idx++];
                    if ($cmd === 'c') {
                        $x1 += $curX; $y1 += $curY;
                        $x2 += $curX; $y2 += $curY;
                        $x  += $curX; $y  += $curY;
                    }
                    $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
                        $toPdfX($x1), $toPdfY($y1),
                        $toPdfX($x2), $toPdfY($y2),
                        $toPdfX($x),  $toPdfY($y)
                    ));
                    $lastCtrlX = $x2;
                    $lastCtrlY = $y2;
                    $curX = $x;
                    $curY = $y;
                    $prevCmd = $cmd;
                    break;

                case 'S':
                case 's':
                    if ($idx + 3 >= $n) break 2;
                    $x2 = (float)$items[$idx++];
                    $y2 = (float)$items[$idx++];
                    $x  = (float)$items[$idx++];
                    $y  = (float)$items[$idx++];
                    if (in_array($prevCmd, ['C', 'c', 'S', 's'], true)) {
                        $x1 = 2.0 * $curX - $lastCtrlX;
                        $y1 = 2.0 * $curY - $lastCtrlY;
                    } else {
                        $x1 = $curX;
                        $y1 = $curY;
                    }
                    if ($cmd === 's') {
                        $x2 += $curX; $y2 += $curY;
                        $x  += $curX; $y  += $curY;
                    }
                    $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
                        $toPdfX($x1), $toPdfY($y1),
                        $toPdfX($x2), $toPdfY($y2),
                        $toPdfX($x),  $toPdfY($y)
                    ));
                    $lastCtrlX = $x2;
                    $lastCtrlY = $y2;
                    $curX = $x;
                    $curY = $y;
                    $prevCmd = $cmd;
                    break;

                default:
                    $idx++;
                    break;
            }
        }

        if ($hasSubpath) {
            $this->_out($paintOp);
        }
    }

    function splitTituloModerno(string $nombre, string $categoria): array
    {
        $raw = trim($nombre);
        if ($raw === '') {
            return ['Programa en:', 'Formación Profesional'];
        }

        // Detectar prefijos comunes sin importar mayúsculas/minúsculas
        $patrones = [
            '/^t[ée]cnico\s+parauniversitario\s+en[:\s]+(.+)$/iu' => 'Técnico en:',
            '/^carrera\s+t[ée]cnica\s+en[:\s]+(.+)$/iu'           => 'Técnico en:',
            '/^t[ée]cnico\s+en[:\s]+(.+)$/iu'                     => 'Técnico en:',
            '/^diplomado\s+en[:\s]+(.+)$/iu'                      => 'Diplomado en:',
            '/^certificaci[óo]n\s+en[:\s]+(.+)$/iu'               => 'Certificación en:',
            '/^curso\s+libre\s+de[:\s]+(.+)$/iu'                  => 'Curso Libre:',
            '/^curso\s+en[:\s]+(.+)$/iu'                          => 'Curso en:',
            '/^bachillerato\s+en[:\s]+(.+)$/iu'                   => 'Bachillerato en:',
            '/^licenciatura\s+en[:\s]+(.+)$/iu'                   => 'Licenciatura en:',
            '/^maestr[íi]a\s+en[:\s]+(.+)$/iu'                    => 'Maestría en:',
        ];

        foreach ($patrones as $regex => $label) {
            if (preg_match($regex, $raw, $m)) {
                $resto = trim($m[1]);
                // Si viene todo en mayúsculas, convertir a Title Case elegante preservando siglas como IA
                if (mb_strtoupper($resto, 'UTF-8') === $resto) {
                    $resto = $this->formatearTituloElegante($resto);
                }
                return [$label, $resto];
            }
        }

        $catLabel = 'Técnico en:';
        $catLower = mb_strtolower(trim($categoria), 'UTF-8');
        if (str_contains($catLower, 'diplomado')) {
            $catLabel = 'Diplomado en:';
        } elseif (str_contains($catLower, 'curso')) {
            $catLabel = 'Curso Libre:';
        } elseif (str_contains($catLower, 'certificaci')) {
            $catLabel = 'Certificación en:';
        } elseif (str_contains($catLower, 'bachillerato')) {
            $catLabel = 'Bachillerato en:';
        } elseif (str_contains($catLower, 'licenciatura')) {
            $catLabel = 'Licenciatura en:';
        } elseif (str_contains($catLower, 'maestr')) {
            $catLabel = 'Maestría en:';
        }

        $tituloFinal = (mb_strtoupper($raw, 'UTF-8') === $raw) ? $this->formatearTituloElegante($raw) : $raw;
        return [$catLabel, $tituloFinal];
    }

    function formatearTituloElegante(string $txt): string
    {
        $converted = mb_convert_case(mb_strtolower($txt, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        $reemplazos = [
            ' En ' => ' en ',
            ' De ' => ' de ',
            ' Del ' => ' del ',
            ' Con ' => ' con ',
            ' Y ' => ' y ',
            ' Para ' => ' para ',
            ' Ia' => ' IA',
            ' Ti' => ' TI',
            ' Ui' => ' UI',
            ' Ux' => ' UX',
        ];
        return strtr($converted, $reemplazos);
    }

    function drawHeadingWithSquare(float $y, string $titulo, array $squareRgb, array $textRgb, float $fontSize = 16.5): void
    {
        // Cuadro de color pegado al borde izquierdo (x=0, w=7, h=7.5)
        $this->SetFillColor($squareRgb[0], $squareRgb[1], $squareRgb[2]);
        $this->Rect(0, $y, 7, 7.5, 'F');

        // Texto del título al lado del cuadro
        $this->SetXY(9, $y - 0.3);
        $this->SetFont('Arial', 'B', $fontSize);
        $this->SetTextColor($textRgb[0], $textRgb[1], $textRgb[2]);
        $this->Cell(190, 8, $this->toPdf($titulo), 0, 1, 'L');
    }

    function drawVectorCircle(float $cx, float $cy, float $r, string $style = 'D'): void
    {
        $k = $this->k;
        $hp = $this->h;
        $c = 0.552284749831 * $r;

        $op = 'S';
        if ($style === 'F') {
            $op = 'f';
        } elseif ($style === 'FD' || $style === 'DF') {
            $op = 'B';
        }

        $this->_out(sprintf('%.2F %.2F m', $cx * $k, ($hp - ($cy - $r)) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($cx + $c) * $k, ($hp - ($cy - $r)) * $k,
            ($cx + $r) * $k, ($hp - ($cy - $c)) * $k,
            ($cx + $r) * $k, ($hp - $cy) * $k
        ));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($cx + $r) * $k, ($hp - ($cy + $c)) * $k,
            ($cx + $c) * $k, ($hp - ($cy + $r)) * $k,
            $cx * $k, ($hp - ($cy + $r)) * $k
        ));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($cx - $c) * $k, ($hp - ($cy + $r)) * $k,
            ($cx - $r) * $k, ($hp - ($cy + $c)) * $k,
            ($cx - $r) * $k, ($hp - $cy) * $k
        ));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c %s',
            ($cx - $r) * $k, ($hp - ($cy - $c)) * $k,
            ($cx - $c) * $k, ($hp - ($cy - $r)) * $k,
            $cx * $k, ($hp - ($cy - $r)) * $k,
            $op
        ));
    }

    function drawVectorRoundedRect(float $x, float $y, float $w, float $h, float $r, string $style = 'D'): void
    {
        $k = $this->k;
        $hp = $this->h;
        $myArc = 4 / 3 * (sqrt(2) - 1);

        $op = 'S';
        if ($style === 'F') {
            $op = 'f';
        } elseif ($style === 'FD' || $style === 'DF') {
            $op = 'B';
        }

        $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));
        $xc = $x + $w - $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($xc + $r * $myArc) * $k, ($hp - $y) * $k,
            ($x + $w) * $k, ($hp - ($yc - $r * $myArc)) * $k,
            ($x + $w) * $k, ($hp - $yc) * $k
        ));

        $xc = $x + $w - $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $yc) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x + $w) * $k, ($hp - ($yc + $r * $myArc)) * $k,
            ($xc + $r * $myArc) * $k, ($hp - ($y + $h)) * $k,
            $xc * $k, ($hp - ($y + $h)) * $k
        ));

        $xc = $x + $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - ($y + $h)) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($xc - $r * $myArc) * $k, ($hp - ($y + $h)) * $k,
            $x * $k, ($hp - ($yc + $r * $myArc)) * $k,
            $x * $k, ($hp - $yc) * $k
        ));

        $xc = $x + $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', $x * $k, ($hp - $yc) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c %s',
            $x * $k, ($hp - ($yc - $r * $myArc)) * $k,
            ($xc - $r * $myArc) * $k, ($hp - $y) * $k,
            $xc * $k, ($hp - $y) * $k,
            $op
        ));
    }

    function drawVectorPolygon(array $pts, string $style = 'F'): void
    {
        if (count($pts) < 3) {
            return;
        }
        $k = $this->k;
        $hp = $this->h;
        $op = ($style === 'F') ? 'f' : (($style === 'FD' || $style === 'DF') ? 'B' : 's');

        $this->_out(sprintf('%.2F %.2F m', $pts[0][0] * $k, ($hp - $pts[0][1]) * $k));
        for ($i = 1, $n = count($pts); $i < $n; $i++) {
            $this->_out(sprintf('%.2F %.2F l', $pts[$i][0] * $k, ($hp - $pts[$i][1]) * $k));
        }
        $this->_out($op);
    }

    function drawSocialIconVector(string $type, float $cx, float $cy, float $r, array $rgb, bool $fillCircle = true): void
    {
        // Círculo exterior vectorial con fondo blanco (opcional) y borde del color indicado
        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor($rgb[0], $rgb[1], $rgb[2]);
        $this->SetLineWidth(0.35);
        $this->drawVectorCircle($cx, $cy, $r, $fillCircle ? 'DF' : 'D');

        $this->SetFillColor($rgb[0], $rgb[1], $rgb[2]);
        $this->SetDrawColor($rgb[0], $rgb[1], $rgb[2]);

        switch ($type) {
            case 'facebook':
                $this->SetFont('Arial', 'B', 9.5);
                $this->SetTextColor($rgb[0], $rgb[1], $rgb[2]);
                $this->SetXY($cx - $r, $cy - 1.65);
                $this->Cell($r * 2, 3.5, $this->toPdf('f'), 0, 0, 'C');
                break;

            case 'instagram':
                $this->SetLineWidth(0.32);
                $this->drawVectorRoundedRect($cx - 1.5, $cy - 1.5, 3.0, 3.0, 0.72, 'D');
                $this->drawVectorCircle($cx, $cy, 0.72, 'D');
                $this->drawVectorCircle($cx + 0.85, $cy - 0.85, 0.2, 'F');
                break;

            case 'whatsapp':
                $this->SetLineWidth(0.3);
                $this->drawVectorCircle($cx, $cy - 0.08, 1.62, 'D');
                // Colita inferior izquierda de la burbuja
                $this->drawVectorPolygon([
                    [$cx - 1.1, $cy + 0.95],
                    [$cx - 1.6, $cy + 1.75],
                    [$cx - 0.55, $cy + 1.4],
                ], 'F');
                // Auricular interno
                $this->drawVectorCircle($cx - 0.48, $cy - 0.45, 0.32, 'F');
                $this->drawVectorCircle($cx + 0.48, $cy + 0.38, 0.32, 'F');
                $this->SetLineWidth(0.45);
                $this->Line($cx - 0.48, $cy - 0.25, $cx - 0.08, $cy + 0.25);
                $this->Line($cx - 0.08, $cy + 0.25, $cx + 0.45, $cy + 0.42);
                break;

            case 'phone':
                $this->drawVectorCircle($cx - 0.75, $cy - 0.65, 0.42, 'F');
                $this->drawVectorCircle($cx + 0.75, $cy + 0.65, 0.42, 'F');
                $this->SetLineWidth(0.58);
                $this->Line($cx - 0.75, $cy - 0.38, $cx - 0.15, $cy + 0.42);
                $this->Line($cx - 0.15, $cy + 0.42, $cx + 0.72, $cy + 0.72);
                break;

            case 'email':
                $this->SetLineWidth(0.32);
                $this->drawVectorRoundedRect($cx - 1.6, $cy - 1.05, 3.2, 2.1, 0.28, 'D');
                $this->Line($cx - 1.5, $cy - 0.95, $cx, $cy + 0.15);
                $this->Line($cx, $cy + 0.15, $cx + 1.5, $cy - 0.95);
                break;

            case 'youtube':
                $this->drawVectorRoundedRect($cx - 1.65, $cy - 1.12, 3.3, 2.24, 0.55, 'F');
                $this->SetFillColor(255, 255, 255);
                $this->drawVectorPolygon([
                    [$cx - 0.42, $cy - 0.58],
                    [$cx - 0.42, $cy + 0.58],
                    [$cx + 0.68, $cy],
                ], 'F');
                break;

            case 'tiktok':
                $this->SetLineWidth(0.42);
                $this->drawVectorCircle($cx - 0.35, $cy + 0.62, 0.65, 'D');
                $this->Line($cx + 0.3, $cy + 0.62, $cx + 0.3, $cy - 1.25);
                $this->Line($cx + 0.3, $cy - 1.25, $cx + 1.15, $cy - 0.65);
                break;
        }

        $this->SetLineWidth(0.2);
    }

    function drawVectorFooterCurve(): void
    {
        $k = $this->k;
        $hp = $this->h;

        // 1. Fondo blanco limpio sobre el área del pie de página para evitar bordes pixelados de PNGs
        $this->SetFillColor(255, 255, 255);
        $this->Rect(0, 280.8, 210, 16.2, 'F');

        // 2. Degradado inferior izquierdo (X = 0 a 152, Y = 294.2 a 297.0) de verde oscuro a verde esmeralda CEFI
        $steps = 60;
        $startX = 0.0;
        $endX = 152.0;
        $stepW = ($endX - $startX) / $steps;
        $r0 = 14; $g0 = 46;  $b0 = 28;
        $r1 = 0;  $g1 = 152; $b1 = 69;

        for ($i = 0; $i < $steps; $i++) {
            $t = $i / ($steps - 1);
            $r = (int)round($r0 + ($r1 - $r0) * $t);
            $g = (int)round($g0 + ($g1 - $g0) * $t);
            $b = (int)round($b0 + ($b1 - $b0) * $t);
            $this->SetFillColor($r, $g, $b);
            $this->Rect($startX + $i * $stepW, 294.2, $stepW + 0.35, 2.8, 'F');
        }

        // 3. Curva S vectorial nativa (Bézier cúbica) en el lado derecho (X = 149 a 210, Y = 281.4 a 297.0)
        $this->SetFillColor($r1, $g1, $b1);
        $x0 = 149.0; $y0 = 294.2;
        $cx1 = 157.5; $cy1 = 294.2;
        $cx2 = 159.5; $cy2 = 281.4;
        $x1 = 168.0; $y1 = 281.4;

        $this->_out(sprintf('%.2F %.2F m', $x0 * $k, ($hp - $y0) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            $cx1 * $k, ($hp - $cy1) * $k,
            $cx2 * $k, ($hp - $cy2) * $k,
            $x1 * $k,  ($hp - $y1) * $k
        ));
        $this->_out(sprintf('%.2F %.2F l', 210.0 * $k, ($hp - $y1) * $k));
        $this->_out(sprintf('%.2F %.2F l', 210.0 * $k, ($hp - 297.0) * $k));
        $this->_out(sprintf('%.2F %.2F l h f', $x0 * $k, ($hp - 297.0) * $k));
    }

    function Footer()
    {
        // Dibujar la curva verde y barra inferior de forma 100% vectorial para evitar cualquier pixelado
        $this->drawVectorFooterCurve();

        // Íconos vectoriales generados y enlaces activos sobre la franja blanca izquierda
        $y = 283.6;
        $iconColor = [85, 88, 92];
        $items = [
            ['type' => 'facebook',  'text' => 'CEFI.',           'w' => 21.5, 'url' => 'https://www.facebook.com/CEFI.'],
            ['type' => 'instagram', 'text' => 'cefi_cr',         'w' => 24.5, 'url' => 'https://www.instagram.com/cefi_cr/'],
            ['type' => 'phone',     'text' => '2711 1010',       'w' => 25.5, 'url' => 'tel:+50627111010'],
            ['type' => 'email',     'text' => 'info@ceficr.com', 'w' => 35.0, 'url' => 'mailto:info@ceficr.com'],
            ['type' => 'youtube',   'text' => '@ceficr',         'w' => 23.5, 'url' => 'https://www.youtube.com/@ceficr'],
            ['type' => 'tiktok',    'text' => '@cefi_cr',        'w' => 23.5, 'url' => 'https://www.tiktok.com/@cefi_cr'],
        ];

        $x = 6.5;
        foreach ($items as $it) {
            // Dibujar ícono vectorial circular
            $this->drawSocialIconVector($it['type'], $x + 2.8, $y + 2.6, 2.75, $iconColor);

            // Texto de la red social (+1 punto: 7.7pt)
            $this->SetTextColor(85, 88, 92);
            $this->SetFont('Arial', '', 7.7);
            $this->SetXY($x + 6.1, $y + 0.2);
            $this->Cell($it['w'] - 6.3, 5, $this->toPdf($it['text']), 0, 0, 'L');

            // Área clicable con hipervínculo en el PDF
            $this->Link($x, $y - 0.5, $it['w'], 6.5, $it['url']);

            $x += $it['w'];
        }

        // Ícono vectorial en blanco de WhatsApp + número de celular en blanco sobre la curva verde derecha (+1 punto: 8.5pt)
        $this->drawSocialIconVector('whatsapp', 173.8, 288.2, 2.75, [255, 255, 255], false);

        $this->SetXY(177.5, 285.7);
        $this->SetFont('Arial', 'B', 8.5);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(28.5, 5.2, $this->toPdf('+506 8992 9180'), 0, 0, 'L');

        // Enlace directo a WhatsApp en el área derecha
        $this->Link(169.5, 283.8, 35.5, 8.8, 'https://wa.me/50689929180');
    }

    function toPdf($txt): string
    {
        if ($txt === null) {
            return '';
        }
        $txt = str_replace('&nbsp;', ' ', (string)$txt);
        $txt = str_replace("\xc2\xa0", ' ', $txt);
        $txt = html_entity_decode($txt, ENT_QUOTES, 'UTF-8');
        $special = [
            "\xe2\x80\x93" => "-",
            "\xe2\x80\x94" => "-",
            "\xe2\x80\x98" => "'",
            "\xe2\x80\x99" => "'",
            "\xe2\x80\x9c" => '"',
            "\xe2\x80\x9d" => '"',
            "\xe2\x80\xa6" => "...",
            "•" => "\xb7",
            "●" => "\xb7",
            "▪" => "-",
            "₡" => "CRC ",
        ];
        $txt = strtr($txt, $special);
        $txt = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $txt);
        return @mb_convert_encoding($txt, 'ISO-8859-1', 'UTF-8') ?: '';
    }

    function cleanText(?string $html): string
    {
        if ($html === null) {
            return '';
        }
        $html = str_replace('&nbsp;', ' ', $html);
        $html = str_replace("\xc2\xa0", ' ', $html);
        $html = str_ireplace(['<br>', '<br/>', '<br />', '</p>', '</li>', '</div>'], "\n", $html);
        $html = str_ireplace('<li>', "• ", $html);
        $html = strip_tags($html);
        $html = str_replace("\r\n", "\n", $html);
        $html = str_replace("\r", "\n", $html);
        return trim(preg_replace('/\n{3,}/', "\n\n", $html));
    }

    /**
     * Convierte texto plano o HTML enriquecido (TinyMCE: <p>, <strong>, <b>, <ul>, <ol>, <li>, <br>)
     * en líneas estructuradas con indicadores de negrita, viñeta y numeración para el PDF.
     */
    function parseRichColumnLines(?string $html): array
    {
        if ($html === null || trim($html) === '') {
            return [];
        }

        $str = str_replace(['&nbsp;', "\xc2\xa0"], ' ', $html);

        // Convertir listas numeradas <ol><li>...</li></ol> a "1. ...", "2. ..."
        $str = preg_replace_callback('/<ol[^>]*>(.*?)<\/ol>/is', function ($m) {
            $idx = 1;
            return preg_replace_callback('/<li[^>]*>(.*?)<\/li>/is', function ($lm) use (&$idx) {
                return "\n[[B]]" . ($idx++) . ". " . trim($lm[1]) . "[[/B]]\n";
            }, $m[1]);
        }, $str);

        // Convertir listas con viñetas <li> a "• "
        $str = preg_replace('/<li[^>]*>/i', "\n• ", $str);

        // Marcar bloques en negrita (<strong>, <b>, <h1>..<h6>)
        $str = preg_replace('/<(strong|b|h[1-6])[^>]*>(.*?)<\/\1>/is', '[[B]]$2[[/B]]', $str);

        // Saltos de línea por etiquetas de bloque
        $str = str_ireplace(['<br>', '<br/>', '<br />', '</p>', '</li>', '</div>', '</ul>', '</ol>'], "\n", $str);
        $str = strip_tags($str);
        $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
        $str = str_replace(["\r\n", "\r"], "\n", $str);

        $rawLines = explode("\n", $str);
        $parsed = [];

        foreach ($rawLines as $rawLine) {
            $line = trim($rawLine);
            if ($line === '') {
                $parsed[] = ['empty' => true];
                continue;
            }

            $isBold = str_contains($line, '[[B]]');
            $line = str_replace(['[[B]]', '[[/B]]'], '', $line);
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $isBullet = str_starts_with($line, '•') || str_starts_with($line, '-') || str_starts_with($line, '*');
            $isNumbered = (bool)preg_match('/^\d+[\.\-\)]\s+/', $line);

            $parsed[] = [
                'empty'    => false,
                'text'     => $line,
                'bold'     => $isBold,
                'bullet'   => $isBullet,
                'numbered' => $isNumbered,
            ];
        }

        return $parsed;
    }
}

class ProgramaPdfService
{
    /**
     * Genera el PDF Oficial de 3 Páginas de un Programa Académico según el nuevo Diseño Institucional CEFI.
     *
     * @param int $id_programa
     * @param string $dest 'I' inline, 'D' download
     * @return \Illuminate\Http\Response
     */
    public static function generar(int $id_programa, string $dest = 'I')
    {
        \App\Http\Controllers\ProgramaController::ensureProgramasColumns();

        $programa = DB::table('programas')
            ->where('id_programa', $id_programa)
            ->first();

        if (!$programa) {
            abort(404, 'Programa académico no encontrado.');
        }

        $materias = DB::table('plan_estudios')
            ->where('id_programa', $id_programa)
            ->orderBy('id_plan', 'asc')
            ->get();

        $pdf = new ProgramaPDF('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false);
        $pdf->AliasNbPages();
        $pdf->SetMargins(9, 10, 9);

        $pdf->nombre_programa = (string)($programa->nombre_programa ?? '');
        $pdf->categoria = (string)($programa->categoria ?? '');
        $pdf->duracion = (string)($programa->duracion ?? '12 meses');
        $pdf->modalidad = (string)($programa->modalidad ?? 'Presencial');

        // 1. Imagen de Encabezado (personalizada del programa o plantilla global si existe)
        if (!empty($programa->imagen_encabezado) && file_exists(public_path($programa->imagen_encabezado))) {
            $pdf->header_path = public_path($programa->imagen_encabezado);
        } elseif (file_exists(public_path('imgs/cefi_header_programa.png'))) {
            $pdf->header_path = public_path('imgs/cefi_header_programa.png');
        }

        // 2. Imagen Principal (Hero central de Página 1)
        if (!empty($programa->imagen_principal) && file_exists(public_path($programa->imagen_principal))) {
            $pdf->hero_path = public_path($programa->imagen_principal);
        }

        // 3. Imagen Secundaria (Opcional)
        if (!empty($programa->imagen_secundaria) && file_exists(public_path($programa->imagen_secundaria))) {
            $pdf->secondary_path = public_path($programa->imagen_secundaria);
        }

        // 4. Imagen de Footer (personalizada del programa o plantilla global si existe)
        if (!empty($programa->imagen_footer) && file_exists(public_path($programa->imagen_footer))) {
            $pdf->footer_path = public_path($programa->imagen_footer);
        } elseif (file_exists(public_path('imgs/cefi_footer_programa.png'))) {
            $pdf->footer_path = public_path('imgs/cefi_footer_programa.png');
        }

        // =====================================================================
        // PÁGINA 1: HERO IMAGE + DESCRIPCIÓN DEL CURSO + OBJETIVO DEL CURSO
        // =====================================================================
        $pdf->AddPage();

        $currentY = $pdf->header_h - 0.4;

        // Si el programa tiene Imagen Principal (Hero), se dibuja de borde a borde debajo del encabezado
        if (!empty($pdf->hero_path) && file_exists($pdf->hero_path)) {
            $heroH = 82;
            try {
                @$pdf->Image($pdf->hero_path, 0, $currentY, 210, $heroH);
                $currentY += $heroH - 0.4;
            } catch (\Throwable $e) {
                // Continuar si el formato de imagen no es soportado por FPDF
            }
        } else {
            // Franja decorativa tecnológica compacta cuando aún no se ha subido imagen_principal
            $heroH = 42;
            $pdf->SetFillColor(18, 30, 48);
            $pdf->Rect(0, $currentY, 210, $heroH, 'F');

            // Cuadrícula decorativa estilo estudio digital
            $pdf->SetDrawColor(35, 55, 80);
            $pdf->SetLineWidth(0.2);
            for ($gx = 15; $gx < 210; $gx += 15) {
                $pdf->Line($gx, $currentY, $gx, $currentY + $heroH);
            }
            for ($gy = $currentY + 10; $gy < $currentY + $heroH; $gy += 10) {
                $pdf->Line(0, $gy, 210, $gy);
            }

            // Tarjetas de íconos decorativos a la derecha
            $pdf->SetFillColor(30, 58, 95);
            $pdf->Rect(142, $currentY + 6, 26, 13, 'F');
            $pdf->Rect(172, $currentY + 6, 26, 13, 'F');
            $pdf->Rect(142, $currentY + 22, 26, 13, 'F');
            $pdf->Rect(172, $currentY + 22, 26, 13, 'F');

            $pdf->SetTextColor(56, 189, 248);
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetXY(142, $currentY + 9.5);
            $pdf->Cell(26, 6, $pdf->toPdf('AI'), 0, 0, 'C');
            $pdf->SetXY(172, $currentY + 9.5);
            $pdf->Cell(26, 6, $pdf->toPdf('PRO'), 0, 0, 'C');
            $pdf->SetXY(142, $currentY + 25.5);
            $pdf->Cell(26, 6, $pdf->toPdf('LAB'), 0, 0, 'C');
            $pdf->SetXY(172, $currentY + 25.5);
            $pdf->Cell(26, 6, $pdf->toPdf('CEFI'), 0, 0, 'C');

            $currentY += $heroH;
        }

        // Bloque Azul Oscuro (#11619b): Descripción del curso con alto dinámico simétrico
        $descripcion = $pdf->cleanText($programa->informacion ?? '');
        if ($descripcion === '') {
            $descripcion = 'Sin descripción registrada para este programa académico.';
        }

        $approxLinesDesc = max(5, (int)ceil(mb_strlen($descripcion, 'UTF-8') / 95) + substr_count($descripcion, "\n"));
        $minNavyH = (!empty($pdf->hero_path)) ? 84 : 100;
        $navyBoxH = max($minNavyH, min(105, 32 + ($approxLinesDesc * 5.8)));

        $pdf->SetFillColor($pdf->azul_marino[0], $pdf->azul_marino[1], $pdf->azul_marino[2]);
        $pdf->Rect(0, $currentY, 210, $navyBoxH, 'F');

        // Título con cuadro verde lima a la izquierda
        $pdf->drawHeadingWithSquare($currentY + 9.5, 'Descripción del curso:', $pdf->verde_lima, [255, 255, 255], 17);

        // Texto de la descripción en blanco (10.8pt con interlineado editorial 5.6mm)
        $pdf->SetXY(9, $currentY + 21.5);
        $pdf->SetFont('Arial', '', 10.8);
        $pdf->SetTextColor(248, 250, 252);
        $pdf->MultiCell(191, 5.6, $pdf->toPdf($descripcion), 0, 'L');

        $currentY += $navyBoxH;

        // Bloque Inferior Fondo Blanco: Objetivo del curso
        $objetivo = $pdf->cleanText($programa->objetivo_curso ?? '');
        if ($objetivo === '') {
            $objetivo = 'Desarrollar en el estudiante las competencias esenciales y avanzadas del programa para responder a las necesidades del entorno profesional actual.';
        }

        $pdf->drawHeadingWithSquare($currentY + 10, 'Objetivo del curso:', $pdf->azul_hdr, $pdf->azul_marino, 17);

        $pdf->SetXY(9, $currentY + 22);
        $pdf->SetFont('Arial', '', 10.8);
        $pdf->SetTextColor(25, 35, 50);
        $pdf->MultiCell(191, 5.6, $pdf->toPdf($objetivo), 0, 'L');

        // =====================================================================
        // PÁGINA 2: OPCIONES LABORALES Y LO QUE OBTENDRÁS (2 COLUMNAS)
        // =====================================================================
        $pdf->AddPage();

        $colTopY = $pdf->header_h + 8;

        // --- COLUMNA IZQUIERDA: OPCIONES LABORALES ---
        $pdf->drawHeadingWithSquare($colTopY, 'Opciones laborales:', $pdf->verde_lima, $pdf->azul_marino, 16);

        $linesLeft = $pdf->parseRichColumnLines($programa->opciones_laborales ?? ($programa->oferta ?? ''));
        $yLeft = $colTopY + 11.5;
        $firstHeadingLeft = true;
        if (!empty($linesLeft)) {
            foreach ($linesLeft as $item) {
                if (!empty($item['empty'])) {
                    $yLeft += 0.9;
                    continue;
                }
                if ($yLeft > 274) {
                    break;
                }

                $line = $item['text'];
                if ($item['numbered'] || ($item['bold'] && !$item['bullet'])) {
                    if (!$firstHeadingLeft) {
                        $yLeft += 1.1;
                    }
                    $firstHeadingLeft = false;
                    $pdf->SetXY(9, $yLeft);
                    $pdf->SetFont('Arial', 'B', 10.2);
                    $pdf->SetTextColor($pdf->azul_hdr[0], $pdf->azul_hdr[1], $pdf->azul_hdr[2]);
                    $pdf->MultiCell(90, 4.8, $pdf->toPdf($line), 0, 'L');
                } elseif ($item['bullet']) {
                    $cleanBullet = ltrim($line, "•-* \t");
                    $pdf->SetFillColor($pdf->verde_cefi[0], $pdf->verde_cefi[1], $pdf->verde_cefi[2]);
                    $pdf->drawVectorCircle(10.5, $yLeft + 2.3, 0.7, 'F');

                    $pdf->SetXY(13.0, $yLeft);
                    $pdf->SetFont('Arial', $item['bold'] ? 'B' : '', 9.8);
                    $pdf->SetTextColor(30, 45, 65);
                    $pdf->MultiCell(86, 4.7, $pdf->toPdf($cleanBullet), 0, 'L');
                } else {
                    $pdf->SetXY(9, $yLeft);
                    $pdf->SetFont('Arial', '', 9.8);
                    $pdf->SetTextColor(30, 45, 65);
                    $pdf->MultiCell(90, 4.7, $pdf->toPdf($line), 0, 'L');
                }
                $yLeft = $pdf->GetY() + 0.7;
            }
        } else {
            $pdf->SetXY(9, $yLeft);
            $pdf->SetFont('Arial', 'I', 10.5);
            $pdf->SetTextColor(120, 130, 140);
            $pdf->MultiCell(90, 5, $pdf->toPdf('No se han especificado opciones laborales para este programa.'), 0, 'L');
        }

        // --- COLUMNA DERECHA: LO QUE OBTENDRÁS ---
        $pdf->SetFillColor($pdf->azul_hdr[0], $pdf->azul_hdr[1], $pdf->azul_hdr[2]);
        $pdf->Rect(106, $colTopY, 7, 7.5, 'F');
        $pdf->SetXY(115, $colTopY - 0.3);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor($pdf->azul_marino[0], $pdf->azul_marino[1], $pdf->azul_marino[2]);
        $pdf->Cell(90, 8, $pdf->toPdf('Lo que obtendrás:'), 0, 1, 'L');

        $linesRight = $pdf->parseRichColumnLines($programa->lo_que_obtendras ?? ($programa->perfil ?? ''));
        $yRight = $colTopY + 11.5;
        $firstHeadingRight = true;
        if (!empty($linesRight)) {
            foreach ($linesRight as $item) {
                if (!empty($item['empty'])) {
                    $yRight += 0.9;
                    continue;
                }
                if ($yRight > 274) {
                    break;
                }

                $line = $item['text'];
                $isIntro = (str_ends_with($line, ':') || str_ends_with($line, '.')) && !$item['bold'] && !$item['numbered'];

                if ($item['bullet']) {
                    $cleanBullet = ltrim($line, "•-* \t");
                    $pdf->SetFillColor($pdf->verde_cefi[0], $pdf->verde_cefi[1], $pdf->verde_cefi[2]);
                    $pdf->drawVectorCircle(107.5, $yRight + 2.3, 0.7, 'F');

                    $pdf->SetXY(110.2, $yRight);
                    $pdf->SetFont('Arial', $item['bold'] ? 'B' : '', 9.8);
                    $pdf->SetTextColor(30, 45, 65);
                    $pdf->MultiCell(91.3, 4.7, $pdf->toPdf($cleanBullet), 0, 'L');
                } elseif ($isIntro) {
                    $pdf->SetXY(106, $yRight);
                    $pdf->SetFont('Arial', '', 9.8);
                    $pdf->SetTextColor(30, 45, 65);
                    $pdf->MultiCell(95, 4.7, $pdf->toPdf($line), 0, 'L');
                } else {
                    if (!$firstHeadingRight) {
                        $yRight += 1.0;
                    }
                    $firstHeadingRight = false;
                    $pdf->SetXY(106, $yRight + 0.4);
                    $pdf->SetFont('Arial', 'B', 10.2);
                    $pdf->SetTextColor($pdf->azul_hdr[0], $pdf->azul_hdr[1], $pdf->azul_hdr[2]);
                    $pdf->MultiCell(95, 4.8, $pdf->toPdf($line), 0, 'L');
                }
                $yRight = $pdf->GetY() + 0.7;
            }
        } else {
            $pdf->SetXY(106, $yRight);
            $pdf->SetFont('Arial', 'I', 9.5);
            $pdf->SetTextColor(120, 130, 140);
            $pdf->MultiCell(95, 5, $pdf->toPdf('No se han registrado competencias de salida para este programa.'), 0, 'L');
        }

        // Línea divisoria vertical muy sutil entre ambas columnas de la Página 2
        $sepBottomY = min(273, max($yLeft, $yRight));
        if ($sepBottomY > $colTopY + 15) {
            $pdf->SetDrawColor(226, 232, 240);
            $pdf->SetLineWidth(0.25);
            $pdf->Line(102.5, $colTopY + 1, 102.5, $sepBottomY);
        }

        // =====================================================================
        // PÁGINA 3: DURACIÓN DEL PLAN, PLAN DE ESTUDIOS, DOCENTES Y REQUISITOS
        // =====================================================================
        $pdf->AddPage();

        // 1. Franja Azul Oscuro (#11619b): DURACIÓN DEL PLAN DE ESTUDIOS (repite lo del header sin costura)
        $durClean = rtrim(trim($pdf->duracion), '.');
        if ($durClean === '') {
            $durClean = '12 meses';
        }
        $modClean = rtrim(trim($pdf->modalidad), '.');
        if ($modClean === '') {
            $modClean = 'Presencial';
        }
        $textoHeaderDuracion = "Duración: {$durClean}  -  Modalidad: {$modClean}";

        $duracionPlan = $pdf->cleanText($programa->duracion_plan_estudios ?? '');
        if (
            $duracionPlan === '' ||
            stripos($duracionPlan, 'Leer y aceptar') !== false ||
            stripos($duracionPlan, 'formulario de matrícula') !== false ||
            stripos($duracionPlan, 'políticas institucionales') !== false
        ) {
            $duracionPlan = $textoHeaderDuracion;
        }

        $stripY = $pdf->header_h - 0.4;
        $stripH = 20.4;
        $pdf->SetFillColor($pdf->azul_marino[0], $pdf->azul_marino[1], $pdf->azul_marino[2]);
        $pdf->Rect(0, $stripY, 210, $stripH, 'F');

        // Acento verde lima a la izquierda de la franja
        $textOffsetY = (mb_strlen($duracionPlan, 'UTF-8') > 75) ? 4.4 : 7.0;
        $pdf->SetFillColor($pdf->verde_lima[0], $pdf->verde_lima[1], $pdf->verde_lima[2]);
        $pdf->Rect(0, $stripY + $textOffsetY, 7, 6.5, 'F');

        $pdf->SetXY(9, $stripY + $textOffsetY);
        $pdf->SetFont('Arial', 'B', 12.5);
        $pdf->SetTextColor(255, 255, 255);
        $labelW = $pdf->GetStringWidth($pdf->toPdf('DURACIÓN DEL PLAN DE ESTUDIOS: ')) + 2;
        $pdf->Cell($labelW, 6.5, $pdf->toPdf('DURACIÓN DEL PLAN DE ESTUDIOS: '), 0, 0, 'L');

        $pdf->SetFont('Arial', '', 10.8);
        $restW = 192 - $labelW;
        $words = explode(' ', str_replace("\n", ' ', $duracionPlan));
        $firstLine = '';
        $remainWords = [];
        $inFirst = true;
        foreach ($words as $w) {
            if ($inFirst && $pdf->GetStringWidth($pdf->toPdf($firstLine . ($firstLine ? ' ' : '') . $w)) < $restW) {
                $firstLine .= ($firstLine ? ' ' : '') . $w;
            } else {
                $inFirst = false;
                $remainWords[] = $w;
            }
        }
        $pdf->Cell($restW, 6.5, $pdf->toPdf($firstLine), 0, 1, 'L');
        if (!empty($remainWords)) {
            $pdf->SetXY(9, $stripY + $textOffsetY + 6.5);
            $pdf->MultiCell(192, 5, $pdf->toPdf(implode(' ', $remainWords)), 0, 'L');
        }

        // 2. Título PLAN DE ESTUDIOS
        $pdf->SetXY(10, 68.5);
        $pdf->SetFont('Arial', 'B', 16.5);
        $pdf->SetTextColor($pdf->azul_hdr[0], $pdf->azul_hdr[1], $pdf->azul_hdr[2]);
        $pdf->Cell(190, 7, $pdf->toPdf('PLAN DE ESTUDIOS'), 0, 1, 'C');

        $pdf->SetX(10);
        $pdf->SetFont('Arial', 'B', 11.5);
        $pdf->SetTextColor($pdf->azul_marino[0], $pdf->azul_marino[1], $pdf->azul_marino[2]);
        $pdf->Cell(190, 6, $pdf->toPdf(mb_strtoupper($pdf->nombre_programa, 'UTF-8')), 0, 1, 'C');

        // 3. Tabla de Cursos (Código | Nombre del Curso | Duración | Requisito) con franja azul a todo lo ancho y zebra striping
        $tableY = 85;
        $pdf->SetFillColor($pdf->teal_tabla[0], $pdf->teal_tabla[1], $pdf->teal_tabla[2]);
        $pdf->Rect(0, $tableY, 210, 9, 'F');

        $pdf->SetXY(11, $tableY);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 10.2);

        $pdf->Cell(38, 9, $pdf->toPdf('Código'), 0, 0, 'C', false);
        $pdf->Cell(78, 9, $pdf->toPdf('  Nombre del Curso'), 0, 0, 'L', false);
        $pdf->Cell(36, 9, $pdf->toPdf('Duración'), 0, 0, 'C', false);
        $pdf->Cell(36, 9, $pdf->toPdf('Requisito'), 0, 1, 'C', false);

        $rowY = $tableY + 9;
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->SetLineWidth(0.25);

        if ($materias->count() > 0) {
            $rowIdx = 0;
            foreach ($materias as $mat) {
                if ($rowY > 216) {
                    $pdf->AddPage();
                    $rowY = 48;
                }

                // Fondo alternado sutil (Zebra striping) en filas pares
                if ($rowIdx % 2 === 1) {
                    $pdf->SetFillColor(245, 249, 253);
                    $pdf->Rect(11, $rowY, 188, 10.5, 'F');
                }

                $codigo = trim((string)($mat->codigo ?? ''));
                $nombre = mb_substr(trim((string)($mat->materia ?? '')), 0, 48, 'UTF-8');
                $durCurso = trim((string)($mat->duracion ?? ''));
                if ($durCurso === '') {
                    $durCurso = !empty($mat->cuatrimestre) ? (string)$mat->cuatrimestre : '8 semanas';
                }
                $req = trim((string)($mat->requisitos ?? ''));
                if ($req === '' || strtolower($req) === 'sin requisitos') {
                    $req = 'N/A';
                }

                $pdf->SetXY(11, $rowY);
                $pdf->SetFont('Arial', 'B', 9.6);
                $pdf->SetTextColor($pdf->azul_marino[0], $pdf->azul_marino[1], $pdf->azul_marino[2]);
                $pdf->Cell(38, 10.5, $pdf->toPdf($codigo), 0, 0, 'C');

                $pdf->SetFont('Arial', '', 9.8);
                $pdf->Cell(78, 10.5, $pdf->toPdf('  ' . $nombre), 0, 0, 'L');
                $pdf->Cell(36, 10.5, $pdf->toPdf($durCurso), 0, 0, 'C');
                $pdf->Cell(36, 10.5, $pdf->toPdf($req), 0, 1, 'C');

                $rowY += 10.5;
                $pdf->Line(11, $rowY, 199, $rowY);
                $rowIdx++;
            }
        } else {
            $pdf->SetXY(11, $rowY + 2);
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->SetTextColor(120, 130, 140);
            $pdf->Cell(188, 10, $pdf->toPdf('No se han registrado cursos en el plan de estudios de este programa.'), 0, 1, 'C');
            $rowY += 12;
        }

        // 4. Sección Inferior Página 3: Perfil Docente y Requisitos de Ingreso con mayor presencia tipográfica
        $bottomY = max($rowY + 12, 222);
        if ($bottomY > 252) {
            $pdf->AddPage();
            $bottomY = 50;
        }

        $perfilDocentes = $pdf->cleanText($programa->perfil_docentes ?? '');
        if ($perfilDocentes === '') {
            $perfilDocentes = 'Los docentes que participan de este programa poseen un grado académico de licenciatura e ingenierías, especializados en su área profesional.';
        }

        $requisitosIngreso = $pdf->cleanText($programa->requisitos_ingreso ?? ($programa->detalles_programa ?? ''));
        if ($requisitosIngreso === '') {
            $requisitosIngreso = "1- Leer y aceptar las políticas institucionales.\n2- Completar el formulario de matrícula.\n3- Foto tamaño pasaporte física o digital.\n4- Copia de la cédula de identidad.";
        }

        // Columna Izquierda: Perfil de los docentes a cargo del programa
        $pdf->SetFillColor($pdf->verde_lima[0], $pdf->verde_lima[1], $pdf->verde_lima[2]);
        $pdf->Rect(0, $bottomY, 6.5, 6.2, 'F');
        $pdf->SetXY(9, $bottomY - 0.1);
        $pdf->SetFont('Arial', 'B', 11.5);
        $pdf->SetTextColor($pdf->azul_marino[0], $pdf->azul_marino[1], $pdf->azul_marino[2]);
        $pdf->Cell(91, 6.2, $pdf->toPdf('Perfil de los docentes a cargo del programa:'), 0, 1, 'L');

        $pdf->SetXY(9, $bottomY + 8);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(55, 65, 81);
        $pdf->MultiCell(90, 5.2, $pdf->toPdf($perfilDocentes), 0, 'L');
        $endLeftBottom = $pdf->GetY();

        // Columna Derecha: Requisitos de ingreso (bloque azul idéntico al bloque verde: 6.5 x 6.2)
        $pdf->SetFillColor($pdf->azul_hdr[0], $pdf->azul_hdr[1], $pdf->azul_hdr[2]);
        $pdf->Rect(106, $bottomY, 6.5, 6.2, 'F');
        $pdf->SetXY(115, $bottomY - 0.1);
        $pdf->SetFont('Arial', 'B', 11.5);
        $pdf->SetTextColor($pdf->azul_marino[0], $pdf->azul_marino[1], $pdf->azul_marino[2]);
        $pdf->Cell(88, 6.2, $pdf->toPdf('Requisitos de ingreso:'), 0, 1, 'L');

        $pdf->SetXY(115, $bottomY + 8);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(55, 65, 81);
        $pdf->MultiCell(88, 5.2, $pdf->toPdf($requisitosIngreso), 0, 'L');
        $endRightBottom = $pdf->GetY();

        // Línea divisoria vertical tenue entre Perfil Docente y Requisitos de Ingreso
        $pdf->SetDrawColor(226, 232, 240);
        $pdf->SetLineWidth(0.25);
        $pdf->Line(102.5, $bottomY, 102.5, min(275, max($endLeftBottom, $endRightBottom)));

        $filename = "programa_cefi_{$id_programa}.pdf";
        $pdfContent = $pdf->Output('S');

        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Length', (string)strlen($pdfContent))
            ->header('Content-Disposition', ($dest === 'D' ? 'attachment' : 'inline') . '; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }
}

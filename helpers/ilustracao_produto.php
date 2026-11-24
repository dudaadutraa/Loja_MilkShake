<?php
/**
 * SHAKE NATURE - ILUSTRAÇÃO DOS PRODUTOS
 *
 * Desenha um milkshake (SVG) de acordo com o NOME e a DESCRIÇÃO do produto:
 * a cor do shake, a calda e os enfeites de cima mudam conforme o sabor e os ingredientes.
 * Estilo de cardápio desenhado à mão: cores chapadas e contorno marrom, sem degradês.
 *
 * Uso: echo sn_milkshake_svg($produto);   // $produto com id, nome e descricao
 * Se o produto tiver foto enviada (coluna imagem), a foto é usada no lugar (ver sn_produto_img_url).
 */

/** Sabores reconhecidos: palavra-chave => [cor do shake, cor da calda ou null, enfeites]. A ordem importa (o primeiro que aparecer no nome manda). */
function sn_milkshake_sabores(): array
{
    return [
        'frutas vermelhas' => ['#C9577E', '#8E1F45', ['frutinhas']],
        'red velvet'       => ['#C2414B', '#7A1E26', ['farelo']],
        'doce de leite'    => ['#D9A563', '#A7621F', []],
        'pe de moleque'    => ['#C99A62', '#7A4A22', ['amendoim']],
        'morango'          => ['#F2A0B3', '#D23A57', ['morango']],
        'chocolate branco' => ['#F3E6CF', '#FFFFFF', ['raspas']],
        'nutella'          => ['#6E4128', '#3B1F10', ['avela']],
        'brigadeiro'       => ['#6B3F26', '#3B1F10', ['granulado']],
        'chocolate'        => ['#7A4A2E', '#3B1F10', ['raspas']],
        'cacau'            => ['#6B3F26', '#3B1F10', ['raspas']],
        'ovomaltine'       => ['#B9844F', '#6B3F1C', ['crocante']],
        'maltado'          => ['#B9844F', '#6B3F1C', ['crocante']],
        'kitkat'           => ['#9C6A44', '#4A2A16', ['wafer']],
        'kit kat'          => ['#9C6A44', '#4A2A16', ['wafer']],
        'oreo'             => ['#D8D2C8', '#2A211C', ['biscoito_preto']],
        'negresco'         => ['#D8D2C8', '#2A211C', ['biscoito_preto']],
        'cookies'          => ['#E2CDAA', '#6B3F1C', ['cookie']],
        'cookie'           => ['#E2CDAA', '#6B3F1C', ['cookie']],
        'biscoito'         => ['#E2CDAA', '#8B5A2B', ['cookie']],
        'cappuccino'       => ['#B88A63', '#5A341C', ['cafe']],
        'capuccino'        => ['#B88A63', '#5A341C', ['cafe']],
        'mocha'            => ['#8E6142', '#3B1F10', ['cafe']],
        'cafe'             => ['#A9784F', '#4A2A16', ['cafe']],
        'acai'             => ['#5E2B5E', '#3A133A', ['granola', 'banana']],
        'banana'           => ['#F4DD8C', '#B8792A', ['banana']],
        'maracuja'         => ['#F6C445', '#C77F0A', ['sementes']],
        'manga'            => ['#F7AE45', null, ['manga']],
        'abacaxi'          => ['#F5D86A', null, ['abacaxi']],
        'limao'            => ['#DDEBA0', null, ['limao']],
        'coco'             => ['#F6F2E8', null, ['coco']],
        'menta'            => ['#BDE5CC', '#3B1F10', ['menta', 'gotas']],
        'hortela'          => ['#BDE5CC', '#3B1F10', ['menta', 'gotas']],
        'pistache'         => ['#C3D69C', null, ['pistache']],
        'amendoim'         => ['#CFA46E', '#7A4A22', ['amendoim']],
        'pacoca'           => ['#CFA46E', '#7A4A22', ['amendoim']],
        'caramelo'         => ['#D49A55', '#A2581A', []],
        'uva'              => ['#8C5A9E', '#5A2A6B', ['uva']],
        'amora'            => ['#9B4A7A', '#5E1F45', ['frutinhas']],
        'mirtilo'          => ['#7E77B8', '#3E3577', ['frutinhas']],
        'blueberry'        => ['#7E77B8', '#3E3577', ['frutinhas']],
        'cereja'           => ['#E06C7E', '#9E1B32', []],
        'ninho'            => ['#F7EED8', null, ['po']],
        'leite em po'      => ['#F7EED8', null, ['po']],
        'baunilha'         => ['#F6E6B8', null, []],
        'creme'            => ['#F6E6B8', null, []],
        'iogurte'          => ['#F4F0F2', null, []],
    ];
}

/** Ingredientes extras citados na descrição que viram enfeite ou calda. */
function sn_milkshake_extras(): array
{
    return [
        'granulado colorido' => 'confete', 'confete' => 'confete', 'colorido' => 'confete',
        'granulado' => 'granulado', 'raspas' => 'raspas', 'cereja' => 'cereja',
        'morango' => 'morango', 'banana' => 'banana', 'granola' => 'granola',
        'castanha' => 'amendoim', 'amendoim' => 'amendoim', 'avela' => 'avela',
        'oreo' => 'biscoito_preto', 'cookie' => 'cookie', 'biscoito' => 'cookie',
        'wafer' => 'wafer', 'menta' => 'menta', 'coco' => 'coco', 'cafe' => 'cafe',
        'marshmallow' => 'marshmallow', 'canudo de chocolate' => 'wafer',
    ];
}

function sn_sem_acento(string $t): string
{
    $t = mb_strtolower($t, 'UTF-8');
    return strtr($t, ['á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'é' => 'e', 'ê' => 'e', 'í' => 'i',
                      'ó' => 'o', 'õ' => 'o', 'ô' => 'o', 'ú' => 'u', 'ü' => 'u', 'ç' => 'c']);
}

/** Lê nome + descrição e decide cor, calda e enfeites. */
function sn_milkshake_receita(array $p): array
{
    $nome = sn_sem_acento((string) ($p['nome'] ?? ''));
    $desc = sn_sem_acento((string) ($p['descricao'] ?? ''));
    $tudo = $nome . ' ' . $desc;

    $cor = '#F6E6B8';
    $calda = null;
    $enfeites = [];
    $achou = false;

    // 1º o sabor do nome; se o nome não disser, procura na descrição
    foreach ([$nome, $desc] as $texto) {
        $melhor = null;
        foreach (sn_milkshake_sabores() as $chave => $info) {
            $pos = strpos($texto, $chave);
            if ($pos !== false && ($melhor === null || $pos < $melhor[0])) {
                $melhor = [$pos, $info];
            }
        }
        if ($melhor) {
            [$cor, $calda, $enfeites] = $melhor[1];
            $achou = true;
            break;
        }
    }

    // calda citada na descrição ("calda de chocolate", "com mel"...)
    if (preg_match('/calda de (chocolate|morango|caramelo|doce de leite|frutas vermelhas|nutella)/', $tudo, $m)) {
        $calda = ['chocolate' => '#3B1F10', 'nutella' => '#3B1F10', 'morango' => '#D23A57', 'caramelo' => '#A2581A',
                  'doce de leite' => '#A7621F', 'frutas vermelhas' => '#8E1F45'][$m[1]];
    } elseif (strpos($tudo, ' mel') !== false || strpos($tudo, 'mel ') === 0) {
        $calda = $calda ?? '#E0A526';
    }

    foreach (sn_milkshake_extras() as $chave => $enfeite) {
        if (strpos($desc, $chave) !== false && !in_array($enfeite, $enfeites, true)) {
            $enfeites[] = $enfeite;
        }
    }
    if (strpos($tudo, 'colorido') !== false || strpos($tudo, 'confete') !== false) {
        $enfeites = array_values(array_diff($enfeites, ['granulado']));
    }
    if (!$achou && !$enfeites) {
        $enfeites = ['cereja'];
    }

    $semChantilly = strpos($tudo, 'sem chantilly') !== false;
    return ['cor' => $cor, 'calda' => $calda, 'enfeites' => array_slice($enfeites, 0, 3), 'chantilly' => !$semChantilly];
}

/** Clareia/escurece uma cor #RRGGBB (f > 0 clareia, f < 0 escurece). */
function sn_cor(string $hex, float $f): string
{
    $hex = ltrim($hex, '#');
    $rgb = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    foreach ($rgb as &$c) {
        $c = (int) round($f > 0 ? $c + (255 - $c) * $f : $c * (1 + $f));
    }
    return sprintf('#%02X%02X%02X', ...$rgb);
}

/** Desenha o SVG completo. */
function sn_milkshake_svg(array $p): string
{
    $r = sn_milkshake_receita($p);
    $id = (int) ($p['id'] ?? 0);
    $seed = crc32(($p['nome'] ?? '') . $id);
    $rnd = function (int $n, int $min, int $max) use ($seed): int {
        return $min + (int) ((($seed >> ($n % 24)) ^ ($seed * ($n + 7))) & 0x7fffffff) % ($max - $min + 1);
    };

    $linha = '#4A2E1C';
    $cor = $r['cor'];
    $fundos = ['#FFF3D6', '#FFE9CF', '#FDF0DC', '#FFEFD9'];
    $fundo = $fundos[$rnd(1, 0, 3)];
    $creme = $r['chantilly'] ? '#FFFDF6' : sn_cor($cor, .25);
    $copo = $rnd(2, 0, 1);                // 0 = taça clássica, 1 = copo alto
    $canudoAng = $rnd(3, -16, -6);
    $canudoCor = ['#E8670C', '#F9BE2C', '#D23A57'][$rnd(4, 0, 2)];

    $s = [];
    $s[] = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="30 14 340 340" role="img" aria-label="' . htmlspecialchars((string) ($p['nome'] ?? 'Milkshake'), ENT_QUOTES) . '">';
    $s[] = '<rect width="400" height="400" fill="' . $fundo . '"/>';
    // bolinhas de fundo, bem discretas
    for ($i = 0; $i < 7; $i++) {
        $s[] = sprintf('<circle cx="%d" cy="%d" r="%d" fill="%s" opacity=".55"/>', $rnd(10 + $i, 20, 380), $rnd(20 + $i, 20, 170), $rnd(30 + $i, 3, 7), sn_cor($fundo, -.06));
    }
    // mesa
    $s[] = '<path d="M0 352 Q200 344 400 352 L400 400 L0 400Z" fill="' . sn_cor($fundo, -.07) . '"/>';
    $s[] = '<ellipse cx="200" cy="350" rx="70" ry="9" fill="' . sn_cor($fundo, -.14) . '"/>';

    // canudo (atrás do creme)
    $s[] = sprintf('<g transform="rotate(%d 236 150)"><rect x="228" y="40" width="16" height="140" rx="3" fill="#FFFFFF" stroke="%s" stroke-width="3"/>', -$canudoAng, $linha);
    for ($y = 50; $y < 175; $y += 22) {
        $s[] = sprintf('<path d="M229.5 %d l13 -10 v9 l-13 10z" fill="%s"/>', $y + 10, $canudoCor);
    }
    $s[] = '</g>';

    if ($copo === 0) {
        // taça clássica de lanchonete
        $corpo = 'M128 158 L272 158 L252 268 Q246 292 222 296 L178 296 Q154 292 148 268 Z';
        $nivel = 'M131 172 L269 172 L251 266 Q245 288 222 291 L178 291 Q155 288 149 266 Z';
        $pe = '<path d="M186 296 L214 296 L210 322 L190 322Z" fill="#FFFFFF" stroke="' . $linha . '" stroke-width="3" stroke-linejoin="round"/>'
            . '<path d="M156 340 Q200 324 244 340 Q200 350 156 340Z" fill="#FFFFFF" stroke="' . $linha . '" stroke-width="3" stroke-linejoin="round"/>';
        $brilho = 'M146 180 L160 180 L152 262 L144 250Z';
        $boca = [128, 272, 158];
    } else {
        // copo alto
        $corpo = 'M134 150 L266 150 L254 330 Q252 342 238 342 L162 342 Q148 342 146 330 Z';
        $nivel = 'M137 166 L263 166 L252 326 Q250 336 238 336 L162 336 Q150 336 148 326 Z';
        $pe = '';
        $brilho = 'M150 176 L163 176 L158 318 L152 312Z';
        $boca = [134, 266, 150];
    }

    $s[] = '<path d="' . $corpo . '" fill="#FFFFFF" fill-opacity=".85"/>';
    $s[] = '<path d="' . $nivel . '" fill="' . $cor . '"/>';
    // listras do shake batido
    $s[] = '<path d="M150 200 Q200 210 252 198" stroke="' . sn_cor($cor, .25) . '" stroke-width="5" fill="none" stroke-linecap="round" opacity=".8"/>';
    $s[] = '<path d="M156 236 Q200 246 246 232" stroke="' . sn_cor($cor, -.08) . '" stroke-width="4" fill="none" stroke-linecap="round" opacity=".6"/>';
    $s[] = '<path d="' . $brilho . '" fill="#FFFFFF" opacity=".55"/>';
    $s[] = '<path d="' . $corpo . '" fill="none" stroke="' . $linha . '" stroke-width="3.2" stroke-linejoin="round"/>';
    $s[] = $pe;

    // escorrido da calda pela borda
    if ($r['calda']) {
        [$x1, $x2, $yb] = $boca;
        $s[] = sprintf('<path d="M%d %d Q%d %d %d %d Q%d %d %d %d Q%d %d %d %d Q%d %d %d %d L%d %d Z" fill="%s" stroke="%s" stroke-width="2"/>',
            $x1 + 6, $yb, $x1 + 14, $yb + 28, $x1 + 22, $yb + 6, $x1 + 40, $yb + 44, $x1 + 52, $yb + 8,
            $x2 - 50, $yb + 30, $x2 - 36, $yb + 6, $x2 - 18, $yb + 22, $x2 - 6, $yb, $x1 + 6, $yb,
            $r['calda'], $linha);
    }

    // borda do copo
    [$x1, $x2, $yb] = $boca;
    $s[] = sprintf('<rect x="%d" y="%d" width="%d" height="10" rx="5" fill="#FFFFFF" stroke="%s" stroke-width="3"/>', $x1 - 6, $yb - 8, $x2 - $x1 + 12, $linha);

    // chantilly / creme em camadas
    $c = $creme;
    $s[] = '<g stroke="' . $linha . '" stroke-width="3" stroke-linejoin="round" fill="' . $c . '">';
    $s[] = '<path d="M126 152 Q118 128 146 122 Q150 100 178 104 Q190 86 212 96 Q238 86 248 108 Q276 108 274 132 Q290 146 270 156 Z"/>';
    $s[] = '<path d="M152 118 Q150 92 178 90 Q186 70 208 76 Q232 70 236 92 Q258 98 248 116 Z"/>';
    $s[] = '<path d="M180 88 Q178 66 198 62 Q206 46 218 56 Q232 62 224 80 Q222 92 210 92 Z"/>';
    $s[] = '</g>';
    $s[] = '<path d="M150 136 Q170 128 186 136 M210 130 Q232 122 248 134 M176 104 Q196 96 214 104" stroke="' . sn_cor($c, -.12) . '" stroke-width="3" fill="none" stroke-linecap="round"/>';

    // calda por cima do creme
    if ($r['calda']) {
        $s[] = '<path d="M150 126 Q164 112 178 124 Q192 104 206 120 Q220 102 232 118 Q246 108 252 124" stroke="' . $r['calda'] . '" stroke-width="7" fill="none" stroke-linecap="round" stroke-linejoin="round"/>';
        $s[] = '<path d="M186 92 Q198 80 212 90" stroke="' . $r['calda'] . '" stroke-width="6" fill="none" stroke-linecap="round"/>';
    }

    // enfeites
    $temCereja = false;
    foreach ($r['enfeites'] as $i => $e) {
        $s[] = sn_milkshake_enfeite($e, $i, $linha, $rnd);
        $temCereja = $temCereja || $e === 'cereja';
    }

    $s[] = '</svg>';
    return implode('', $s);
}

/** Desenha um enfeite. $i é a posição (0 = topo, 1 = lado esquerdo, 2 = lado direito). */
function sn_milkshake_enfeite(string $tipo, int $i, string $linha, callable $rnd): string
{
    $pos = [[204, 56], [156, 108], [246, 112], [204, 56]][$i] ?? [204, 56];
    [$x, $y] = $pos;
    $L = 'stroke="' . $linha . '" stroke-width="2.6" stroke-linejoin="round"';
    $pontos = function (array $cores, int $n, int $r, array $area) use ($rnd): string {
        $o = '';
        for ($k = 0; $k < $n; $k++) {
            $cx = $rnd(100 + $k, $area[0], $area[2]);
            $cy = $rnd(140 + $k, $area[1], $area[3]);
            $o .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" rx="2" fill="%s" transform="rotate(%d %d %d)"/>',
                $cx, $cy, $r * 3, $r, $cores[$k % count($cores)], $rnd(160 + $k, 0, 170), $cx, $cy);
        }
        return $o;
    };

    switch ($tipo) {
        case 'morango':
            return sprintf('<g transform="translate(%d %d) rotate(%d)"><path d="M0 -20 Q20 -18 16 4 Q10 22 0 26 Q-10 22 -16 4 Q-20 -18 0 -20Z" fill="#E2405A" %s/>'
                . '<path d="M-12 -18 L-4 -26 L0 -18 L6 -27 L12 -17 Z" fill="#5E9C5A" %s/>'
                . '<g fill="#FCE3A8"><circle cx="-6" cy="-6" r="1.6"/><circle cx="6" cy="-4" r="1.6"/><circle cx="0" cy="6" r="1.6"/><circle cx="-8" cy="8" r="1.6"/><circle cx="8" cy="10" r="1.6"/></g></g>',
                $x, $y - 4, $i === 0 ? 8 : ($i === 1 ? -25 : 25), $L, $L);
        case 'cereja':
            return sprintf('<g transform="translate(%d %d)"><path d="M2 -10 Q8 -34 22 -40" fill="none" %s/><circle cx="0" cy="0" r="14" fill="#C8243A" %s/><circle cx="-5" cy="-5" r="3.5" fill="#FFFFFF" opacity=".7"/></g>',
                $x, $y - 6, $L, $L);
        case 'banana':
            return sprintf('<g transform="translate(%d %d)">'
                . '<g transform="translate(-14 4) rotate(-12)"><ellipse rx="15" ry="12" fill="#FAEBB0" %s/><circle r="4" fill="none" stroke="#D9B65C" stroke-width="2"/></g>'
                . '<g transform="translate(14 0) rotate(10)"><ellipse rx="15" ry="12" fill="#FAEBB0" %s/><circle r="4" fill="none" stroke="#D9B65C" stroke-width="2"/></g></g>',
                $x, $y, $L, $L);
        case 'manga':
            return sprintf('<g transform="translate(%d %d) rotate(-14)"><rect x="-14" y="-14" width="28" height="28" rx="5" fill="#F9A53A" %s/><rect x="-8" y="-8" width="12" height="12" rx="3" fill="#FCC56B"/></g>', $x, $y, $L);
        case 'abacaxi':
            return sprintf('<g transform="translate(%d %d)"><path d="M-22 10 L0 -22 L22 10 Z" fill="#F7D14C" %s/><path d="M-10 -6 L10 -6 M-15 2 L15 2" stroke="#D9A21C" stroke-width="2"/><path d="M-6 -22 L-10 -38 L0 -28 L6 -40 L8 -24 Z" fill="#5E9C5A" %s/></g>', $x, $y, $L, $L);
        case 'limao':
            return sprintf('<g transform="translate(%d %d) rotate(-20)"><circle r="20" fill="#B9D65A" %s/><circle r="15" fill="#E9F3B8"/><path d="M0 -15 V15 M-15 0 H15 M-10 -10 L10 10 M10 -10 L-10 10" stroke="#B9D65A" stroke-width="2"/></g>', $x, $y - 4, $L);
        case 'menta':
            return sprintf('<g transform="translate(%d %d)"><path d="M0 0 Q-24 -8 -20 -28 Q2 -24 0 0Z" fill="#5E9C5A" %s/><path d="M0 0 Q22 -6 22 -28 Q2 -26 0 0Z" fill="#6FB06A" %s/><path d="M0 0 L-12 -18 M0 0 L12 -18" stroke="#3E7A3B" stroke-width="2"/></g>', $x, $y + 2, $L, $L);
        case 'cookie':
            return sprintf('<g transform="translate(%d %d) rotate(-18)"><path d="M-22 -6 Q-20 -22 0 -22 Q20 -22 22 -4 Q20 8 0 8 Q-20 8 -22 -6Z" fill="#D9A15C" %s/><g fill="#4A2A16"><circle cx="-10" cy="-10" r="3"/><circle cx="6" cy="-14" r="2.6"/><circle cx="10" cy="-2" r="3"/><circle cx="-4" cy="0" r="2.4"/></g></g>', $x, $y, $L);
        case 'biscoito_preto':
            return sprintf('<g transform="translate(%d %d) rotate(-14)"><ellipse cx="0" cy="-6" rx="22" ry="8" fill="#2A211C" %s/><rect x="-21" y="-6" width="42" height="8" fill="#F7F3EA" stroke="%s" stroke-width="2.6"/><ellipse cx="0" cy="4" rx="22" ry="8" fill="#2A211C" %s/></g>', $x, $y, $L, $linha, $L);
        case 'wafer':
            return sprintf('<g transform="translate(%d %d) rotate(14)"><rect x="-8" y="-40" width="16" height="44" rx="2" fill="#B8783F" %s/><path d="M-8 -28 H8 M-8 -16 H8 M-8 -4 H8" stroke="#7A4A22" stroke-width="2"/></g>', $x, $y, $L);
        case 'cafe':
            $o = '';
            foreach ([[-14, 0, -20], [4, -6, 20], [18, 6, -10]] as [$dx, $dy, $rot]) {
                $o .= sprintf('<g transform="translate(%d %d) rotate(%d)"><ellipse rx="8" ry="11" fill="#5A341C" %s/><path d="M0 -9 Q-4 0 0 9" stroke="#2A160A" stroke-width="2" fill="none"/></g>', $x + $dx, $y + $dy, $rot, $L);
            }
            return $o;
        case 'avela':
        case 'amendoim':
        case 'pistache':
            $f = $tipo === 'pistache' ? '#9CBF5A' : ($tipo === 'avela' ? '#A8693A' : '#D9AE72');
            $o = '';
            foreach ([[-14, 2], [2, -8], [16, 4], [-2, 8]] as $k => [$dx, $dy]) {
                $o .= sprintf('<ellipse cx="%d" cy="%d" rx="7" ry="5.5" fill="%s" %s transform="rotate(%d %d %d)"/>', $x + $dx, $y + $dy, $f, $L, $k * 40, $x + $dx, $y + $dy);
            }
            return $o;
        case 'granola':
            $o = '';
            for ($k = 0; $k < 9; $k++) {
                $o .= sprintf('<rect x="%d" y="%d" width="9" height="6" rx="2" fill="%s" stroke="%s" stroke-width="1.5" transform="rotate(%d %d %d)"/>',
                    $x - 26 + $rnd(200 + $k, 0, 50), $y - 10 + $rnd(220 + $k, 0, 18), ['#C9924E', '#E2B977', '#A8693A'][$k % 3], $linha, $rnd(240 + $k, 0, 90), $x, $y);
            }
            return $o;
        case 'frutinhas':
        case 'uva':
            $f = $tipo === 'uva' ? '#6B3F7E' : '#5B4A9E';
            $o = '';
            foreach ([[-12, 2, '#B4234A'], [6, -6, $f], [16, 8, '#B4234A'], [-2, 10, $f]] as [$dx, $dy, $cc]) {
                $o .= sprintf('<circle cx="%d" cy="%d" r="9" fill="%s" %s/><circle cx="%d" cy="%d" r="2.4" fill="#FFFFFF" opacity=".6"/>', $x + $dx, $y + $dy, $tipo === 'uva' ? $f : $cc, $L, $x + $dx - 3, $y + $dy - 3);
            }
            return $o;
        case 'coco':
            $o = sprintf('<g transform="translate(%d %d) rotate(-12)"><path d="M-22 0 A22 22 0 0 0 22 0 Z" fill="#7A4A2A" %s/><path d="M-17 0 A17 17 0 0 0 17 0 Z" fill="#FFFFFF"/><path d="M-22 0 H22" %s/></g>', $x, $y + 2, $L, $L);
            for ($k = 0; $k < 14; $k++) {
                $o .= sprintf('<path d="M%d %d l7 -3" stroke="#E6DAC4" stroke-width="3" stroke-linecap="round"/><path d="M%d %d l7 -3" stroke="#BFAF93" stroke-width="1" stroke-linecap="round"/>',
                    150 + $rnd(300 + $k, 0, 100), 96 + $rnd(320 + $k, 0, 50), 150 + $rnd(300 + $k, 0, 100), 98 + $rnd(320 + $k, 0, 50));
            }
            return $o;
        case 'sementes':
            $o = '';
            for ($k = 0; $k < 12; $k++) {
                $o .= sprintf('<ellipse cx="%d" cy="%d" rx="3.5" ry="2.4" fill="#2A160A"/>', 154 + $rnd(340 + $k, 0, 96), 100 + $rnd(360 + $k, 0, 46));
            }
            return '<path d="M150 124 Q176 108 204 118 Q230 106 252 124" stroke="#F2A818" stroke-width="8" fill="none" stroke-linecap="round" opacity=".9"/>' . $o;
        case 'confete':
            return $pontos(['#E2405A', '#F9BE2C', '#5B8FD9', '#5E9C5A', '#E8670C', '#B062C2'], 18, 3, [148, 70, 250, 140]);
        case 'granulado':
            return $pontos(['#3B1F10', '#5A341C'], 18, 3, [148, 70, 250, 140]);
        case 'gotas':
            $o = '';
            for ($k = 0; $k < 8; $k++) {
                $o .= sprintf('<path d="M%1$d %2$d q4 -8 8 0 z" fill="#3B1F10"/>', 152 + $rnd(380 + $k, 0, 96), 106 + $rnd(400 + $k, 0, 36));
            }
            return $o;
        case 'raspas':
            $o = '';
            for ($k = 0; $k < 8; $k++) {
                $o .= sprintf('<path d="M%d %d q6 -6 12 0" stroke="#3B1F10" stroke-width="3.5" fill="none" stroke-linecap="round"/>', 150 + $rnd(420 + $k, 0, 92), 84 + $rnd(440 + $k, 0, 52));
            }
            return $o;
        case 'crocante':
        case 'farelo':
            $o = '';
            for ($k = 0; $k < 14; $k++) {
                $o .= sprintf('<circle cx="%d" cy="%d" r="%d" fill="%s"/>', 150 + $rnd(460 + $k, 0, 100), 84 + $rnd(480 + $k, 0, 56), $rnd(500 + $k, 2, 4), $tipo === 'farelo' ? '#9E2F38' : '#8E5A2E');
            }
            return $o;
        case 'po':
            $o = '';
            for ($k = 0; $k < 22; $k++) {
                $o .= sprintf('<circle cx="%d" cy="%d" r="1.8" fill="#E9DDBF"/>', 150 + $rnd(520 + $k, 0, 100), 84 + $rnd(540 + $k, 0, 56));
            }
            return $o;
        case 'marshmallow':
            return sprintf('<g transform="translate(%d %d)"><rect x="-22" y="-8" width="18" height="16" rx="5" fill="#FBD6E0" %s/><rect x="2" y="-12" width="18" height="16" rx="5" fill="#FFFFFF" %s/></g>', $x, $y, $L, $L);
    }
    return '';
}

/**
 * Endereço da imagem do produto: a foto enviada pelo painel (se houver)
 * ou a ilustração gerada automaticamente.
 */
function sn_produto_img_url(array $p): string
{
    if (!empty($p['imagem']) && is_file(RAIZ . '/public/uploads/produtos/' . basename((string) $p['imagem']))) {
        return BASE_URL . 'public/uploads/produtos/' . rawurlencode(basename((string) $p['imagem']));
    }
    // foto colocada direto na pasta com o número do produto: public/uploads/produtos/produto-ID.jpg (ou .png/.webp)
    foreach (['jpg', 'png', 'webp'] as $ext) {
        $arq = 'produto-' . (int) ($p['id'] ?? 0) . '.' . $ext;
        if (is_file(RAIZ . '/public/uploads/produtos/' . $arq)) {
            return BASE_URL . 'public/uploads/produtos/' . $arq . '?v=' . filemtime(RAIZ . '/public/uploads/produtos/' . $arq);
        }
    }
    // a versão muda quando o nome ou a descrição mudam (o navegador baixa a ilustração nova)
    $v = substr(md5(($p['nome'] ?? '') . '|' . ($p['descricao'] ?? '')), 0, 8);
    return sn_url(['controller' => 'loja', 'action' => 'imagem', 'id' => (int) ($p['id'] ?? 0), 'v' => $v]);
}

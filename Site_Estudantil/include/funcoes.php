<?php
// include/funcoes.php — Nexus Esports

function usuario_logado(): bool {
    return isset($_SESSION['usuario_id']);
}

function is_admin(): bool {
    return isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin';
}

function redirecionar(string $url): void {
    header("Location: $url");
    exit;
}

function sanitizar(string $texto): string {
    return htmlspecialchars(strip_tags(trim($texto)), ENT_QUOTES, 'UTF-8');
}

function resumo(string $texto, int $limite = 180): string {
    $texto = strip_tags($texto);
    if (mb_strlen($texto) <= $limite) return $texto;
    return mb_substr($texto, 0, $limite) . '…';
}

function formatar_data(string $data): string {
    $meses = ['jan','fev','mar','abr','mai','jun','jul','ago','set','out','nov','dez'];
    $dt = new DateTime($data);
    return $dt->format('d') . ' ' . $meses[(int)$dt->format('n') - 1] . '. ' . $dt->format('Y · H:i');
}

function formatar_data_curta(string $data): string {
    return (new DateTime($data))->format('d/m/Y');
}

function label_categoria(string $cat): string {
    $labels = [
        'vestibular'     => 'Vestibular',
        'academico'       => 'academico',
        'bolsas' => 'bolsas',
        'pesquisa' => 'pesquisa',
        'cultura'    => 'cultura',
    ];
    return $labels[$cat] ?? ucfirst($cat);
}

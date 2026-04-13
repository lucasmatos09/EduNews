<?php
// include/header.php — EduNews Portal Estudantil
$asset_root = '../assets';
?>
<!DOCTYPE html>
<html lang="pt-br" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? sanitizar($page_title) . ' — ' : '' ?>EduNews</title>
    <link rel="stylesheet" href="<?= $asset_root ?>/css/style.css">
    <script>
        (function(){
            var t = localStorage.getItem('edunews-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body>

<?php
$atual = basename($_SERVER['PHP_SELF'], '.php');
function nav_active(string $page): string {
    global $atual;
    return $atual === $page ? ' active' : '';
}
$is_admin_page = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$public_prefix = $is_admin_page ? '../public/' : '';
$admin_prefix  = $is_admin_page ? '' : '../admin/';
?>

<header>
    <!-- Topbar -->
    <div class="topbar">
        <span>📚 Portal de Notícias Estudantil — Informação para a Comunidade Acadêmica</span>
        <div class="topbar-right">
            <span><?= date('d/m/Y') ?></span>
            <?php if (usuario_logado()): ?>
                <span>Olá, <strong><?= sanitizar($_SESSION['usuario_nome']) ?></strong></span>
                <?php if (is_admin()): ?>
                    <span class="badge-admin">ADMIN</span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Logo + Search -->
    <div class="header-brand">
        <div class="header-brand-inner">
            <a href="<?= $public_prefix ?>index.php" class="logo">
                <span class="logo-name">Edu<em>News</em></span>
                <span class="logo-sub">portal de notícias estudantil</span>
            </a>

            <form class="search-form" action="<?= $public_prefix ?>index.php" method="GET" role="search">
                <input class="search-input" type="search" name="q"
                       placeholder="Buscar notícias, eventos, oportunidades..."
                       value="<?= isset($_GET['q']) ? sanitizar($_GET['q']) : '' ?>"
                       autocomplete="off" aria-label="Buscar">
                <button class="search-btn" type="submit">🔍</button>
            </form>

            <div class="header-actions">
                <button class="btn-theme" id="theme-btn" onclick="toggleTheme()" title="Alternar tema">☀️</button>
                <?php if (!usuario_logado()): ?>
                    <a href="<?= $public_prefix ?>cadastro.php" class="btn btn-primary btn-sm">Cadastrar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-inner">
            <ul class="navbar-links">
                <li><a href="<?= $public_prefix ?>index.php" class="<?= nav_active('index') ?>">Início</a></li>
                <li><a href="<?= $public_prefix ?>index.php?cat=vestibular">Vestibular <span class="live-badge">Novo</span></a></li>
                <li><a href="<?= $public_prefix ?>index.php?cat=academico">Acadêmico</a></li>
                <li><a href="<?= $public_prefix ?>index.php?cat=bolsas">Bolsas & Editais</a></li>
                <li><a href="<?= $public_prefix ?>index.php?cat=pesquisa">Pesquisa</a></li>
                <li><a href="<?= $public_prefix ?>index.php?cat=cultura">Cultura & Eventos</a></li>
            </ul>
            <div class="navbar-auth">
                <?php if (usuario_logado()): ?>
                    <a href="<?= $admin_prefix ?>dashboard.php">Dashboard</a>
                    <?php if (is_admin()): ?>
                        <a href="<?= $admin_prefix ?>gerenciar_usuarios.php" style="color:#fbbf24;font-weight:700">👥 Usuários</a>
                    <?php endif; ?>
                    <a href="<?= $admin_prefix ?>nova_noticia.php" style="color:var(--blue);font-weight:900">+ Notícia</a>
                    <a href="<?= $admin_prefix ?>logout.php">Sair</a>
                <?php else: ?>
                    <a href="<?= $public_prefix ?>login.php">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Ticker -->
    <div class="ticker">
        <span class="ticker-label">Destaques</span>
        <div class="ticker-track">
            <span class="ticker-scroll">
                📝 ENEM 2026: Inscrições abertas até 14 de maio — saiba como se inscrever
                <span class="ticker-sep">|</span>
                🎓 FIES e ProUni: Novas vagas disponíveis para o segundo semestre
                <span class="ticker-sep">|</span>
                🔬 Pesquisadoras brasileiras vencem prêmio internacional de inovação
                <span class="ticker-sep">|</span>
                📚 MEC anuncia ampliação do programa de livros didáticos gratuitos
                <span class="ticker-sep">|</span>
                🏆 Olimpíada Brasileira de Matemática: Inscrições abertas para alunos
                <span class="ticker-sep">|</span>
                💡 Startup estudantil do Brasil recebe investimento de R$ 5 milhões
                <span class="ticker-sep">|</span>
                🎨 Festival Universitário de Arte e Cultura: Submissões abertas
            </span>
        </div>
    </div>
</header>

<script>
function toggleTheme() {
    var html = document.documentElement;
    var next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('edunews-theme', next);
    document.getElementById('theme-btn').textContent = next === 'dark' ? '☀️' : '🌙';
}
(function(){
    var t = localStorage.getItem('edunews-theme') || 'dark';
    document.documentElement.setAttribute('data-theme', t);
    var btn = document.getElementById('theme-btn');
    if (btn) btn.textContent = t === 'dark' ? '☀️' : '🌙';
})();
</script>

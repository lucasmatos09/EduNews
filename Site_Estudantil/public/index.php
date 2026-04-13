<?php
// public/index.php — EduNews
session_start();
require_once __DIR__ . '/../include/conexao.php';
require_once __DIR__ . '/../include/funcoes.php';

$busca = trim($_GET['q']   ?? '');
$cat   = trim($_GET['cat'] ?? '');

if ($busca !== '') {
    $stmt = $pdo->prepare("
        SELECT n.*, u.nome AS autor_nome
        FROM noticias n JOIN usuarios u ON n.autor = u.id
        WHERE n.titulo LIKE ? OR n.noticia LIKE ?
        ORDER BY n.data DESC
    ");
    $like = '%' . $busca . '%';
    $stmt->execute([$like, $like]);
} elseif ($cat !== '') {
    $stmt = $pdo->prepare("
        SELECT n.*, u.nome AS autor_nome
        FROM noticias n JOIN usuarios u ON n.autor = u.id
        WHERE n.categoria = ?
        ORDER BY n.data DESC
    ");
    $stmt->execute([$cat]);
} else {
    $stmt = $pdo->query("
        SELECT n.*, u.nome AS autor_nome
        FROM noticias n JOIN usuarios u ON n.autor = u.id
        ORDER BY n.data DESC
    ");
}
$noticias = $stmt->fetchAll();

$page_title = $busca ? 'Busca: ' . $busca : ($cat ? label_categoria($cat) : 'Início');
include '../include/header.php';
?>

<?php if (!$busca && !$cat): ?>
<section class="hero-fullscreen" id="hero">
    <div class="hero-bg-grid"></div>
    <div class="hero-particles" id="particles"></div>
    <div class="hero-inner">
        <div class="hero-eyebrow">
            <span class="hero-live-dot"></span>
            Portal Oficial · Notícias Estudantis
        </div>
        <h1 class="hero-title">
            <span class="ht-nexus">EDU</span>
            <span class="ht-esports">NEWS</span>
        </h1>
        <p class="hero-sub">Informação, oportunidades e cultura acadêmica em um só lugar.<br>Cobertura completa de vestibulares, bolsas, pesquisa e vida universitária.</p>

        <div class="hero-actions">
            <?php if (usuario_logado()): ?>
                <a href="../admin/dashboard.php" class="hero-btn hero-btn-primary">
                    📊 Dashboard
                </a>
                <a href="../admin/nova_noticia.php" class="hero-btn hero-btn-ghost">
                    ✏️ Publicar Notícia
                </a>
            <?php else: ?>
                <a href="login.php" class="hero-btn hero-btn-primary">
                    🔑 Fazer Login
                </a>
                <a href="cadastro.php" class="hero-btn hero-btn-ghost">
                    📋 Cadastrar-se
                </a>
            <?php endif; ?>
        </div>

        <div class="hero-weather" id="weather-widget">
            <span class="weather-icon" id="w-icon">🌡️</span>
            <div class="weather-info">
                <span class="weather-temp" id="w-temp">--°C</span>
                <span class="weather-desc" id="w-desc">Carregando...</span>
                <span class="weather-city" id="w-city">📍 Detectando localização...</span>
            </div>
        </div>

        <a href="#noticias" class="hero-scroll-hint">
            <span class="scroll-arrow">↓</span>
            <span>Ver notícias</span>
        </a>
    </div>
</section>
<?php endif; ?>

<div class="container container-pad" id="noticias">

    <?php if ($busca || $cat): ?>
    <div class="cat-filter-bar">
        <a href="index.php" class="cat-pill">📰 Todas</a>
        <a href="index.php?cat=vestibular"   class="cat-pill<?= $cat==='vestibular'     ?' active':'' ?>">📝 Vestibular</a>
        <a href="index.php?cat=academico"    class="cat-pill<?= $cat==='academico'       ?' active':'' ?>">🎓 Acadêmico</a>
        <a href="index.php?cat=bolsas"       class="cat-pill<?= $cat==='bolsas' ?' active':'' ?>">💰 Bolsas</a>
        <a href="index.php?cat=pesquisa"     class="cat-pill<?= $cat==='pesquisa' ?' active':'' ?>">🔬 Pesquisa</a>
        <a href="index.php?cat=cultura"      class="cat-pill<?= $cat==='cultura'    ?' active':'' ?>">🎨 Cultura</a>
    </div>
    <?php endif; ?>

    <div class="section-head">
        <div class="section-head-left">
            <div class="section-head-bar"></div>
            <h2>
                <?php if ($busca): ?>Resultados para "<?= sanitizar($busca) ?>"
                <?php elseif ($cat): ?><?= label_categoria($cat) ?>
                <?php else: ?>Últimas Publicações
                <?php endif; ?>
            </h2>
        </div>
        <?php if (usuario_logado()): ?>
            <a href="../admin/nova_noticia.php" class="btn btn-primary btn-sm">+ Publicar</a>
        <?php endif; ?>
    </div>

    <?php if (empty($noticias)): ?>
        <div class="empty-state">
            <span class="ei"><?= $busca ? '🔍' : '📭' ?></span>
            <h3><?= $busca ? 'Nenhum resultado' : 'Nenhuma notícia nesta categoria' ?></h3>
            <p><?= $busca ? 'Tente outros termos.' : 'Seja o primeiro a publicar!' ?></p>
            <?php if ($cat || $busca): ?>
                <a href="index.php" class="btn btn-ghost mt-2">← Voltar ao início</a>
            <?php elseif (usuario_logado()): ?>
                <a href="../admin/nova_noticia.php" class="btn btn-primary mt-2">+ Nova Notícia</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="news-grid">
            <?php foreach ($noticias as $i => $n): ?>
            <article class="card<?= $i===0 && !$busca ? ' card-featured' : '' ?>">
                <div class="card-img">
                    <?php if ($n['imagem']): ?>
                        <img src="../assets/img/<?= sanitizar($n['imagem']) ?>"
                             alt="<?= sanitizar($n['titulo']) ?>"
                             loading="<?= $i<3?'eager':'lazy' ?>">
                    <?php else: ?>
                        <div class="card-img-placeholder">📰</div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($n['categoria']): ?>
                        <a href="index.php?cat=<?= urlencode($n['categoria']) ?>" class="card-tag">
                            <?= label_categoria($n['categoria']) ?>
                        </a>
                    <?php else: ?>
                        <span class="card-tag">📰 Geral</span>
                    <?php endif; ?>
                    <h2><a href="noticia.php?id=<?= $n['id'] ?>"><?= sanitizar($n['titulo']) ?></a></h2>
                    <p class="card-excerpt"><?= sanitizar(resumo($n['noticia'], $i===0&&!$busca?260:180)) ?></p>
                    <div class="card-meta">
                        <span class="card-meta-author">✍ <?= sanitizar($n['autor_nome']) ?></span>
                        <span><?= formatar_data_curta($n['data']) ?></span>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// ── Weather API (Open-Meteo, gratuita, sem chave) ─────────────────────
const W_ICONS = {0:'☀️',1:'🌤️',2:'⛅',3:'☁️',45:'🌫️',48:'🌫️',51:'🌦️',53:'🌦️',55:'🌧️',61:'🌧️',63:'🌧️',65:'🌧️',71:'❄️',73:'❄️',75:'❄️',80:'🌦️',81:'🌧️',82:'⛈️',95:'⛈️',96:'⛈️',99:'⛈️'};
const W_DESCS = {0:'Céu limpo',1:'Principalmente limpo',2:'Parcialmente nublado',3:'Nublado',45:'Névoa',48:'Névoa gelada',51:'Chuvisco fraco',53:'Chuvisco',55:'Chuvisco forte',61:'Chuva fraca',63:'Chuva moderada',65:'Chuva forte',71:'Neve fraca',73:'Neve',75:'Neve forte',80:'Pancadas de chuva',81:'Pancadas moderadas',82:'Pancadas fortes',95:'Tempestade',96:'Tempestade',99:'Tempestade forte'};

async function fetchWeather(lat, lon, city) {
    const r = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true&timezone=auto`);
    const d = await r.json();
    const cw = d.current_weather;
    const code = cw.weathercode;
    document.getElementById('w-icon').textContent = W_ICONS[code] ?? '🌡️';
    document.getElementById('w-temp').textContent = Math.round(cw.temperature) + '°C';
    document.getElementById('w-desc').textContent = W_DESCS[code] ?? 'N/D';
    document.getElementById('w-city').textContent = '📍 ' + city;
}

async function loadWeather() {
    try {
        const pos = await new Promise((res, rej) => navigator.geolocation.getCurrentPosition(res, rej, {timeout:5000}));
        const {latitude:lat, longitude:lon} = pos.coords;
        const geo  = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`);
        const loc  = await geo.json();
        const city = loc.address?.city || loc.address?.town || loc.address?.village || loc.address?.county || '—';
        await fetchWeather(lat, lon, city);
    } catch(e) {
        try {
            const ip = await fetch('https://ipapi.co/json/');
            const d  = await ip.json();
            await fetchWeather(d.latitude, d.longitude, d.city || 'Brasil');
        } catch(e2) {
            document.getElementById('w-desc').textContent = 'Indisponível';
            document.getElementById('w-city').textContent = '📍 —';
            document.getElementById('w-temp').textContent = '--°C';
        }
    }
}
loadWeather();

// ── Partículas ────────────────────────────────────────────────────────
(function(){
    const c = document.getElementById('particles');
    if (!c) return;
    for (let i = 0; i < 30; i++) {
        const p = document.createElement('span');
        p.className = 'particle';
        p.style.cssText = `left:${Math.random()*100}%;top:${Math.random()*100}%;width:${2+Math.random()*3}px;height:${2+Math.random()*3}px;animation-delay:${Math.random()*8}s;animation-duration:${5+Math.random()*8}s`;
        c.appendChild(p);
    }
})();
</script>

<?php include '../include/footer.php'; ?>

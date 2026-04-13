<?php
// public/noticia.php — FragZone
session_start();
require_once __DIR__ . '/../include/conexao.php';
require_once __DIR__ . '/../include/funcoes.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("
    SELECT n.*, u.nome AS autor_nome
    FROM noticias n JOIN usuarios u ON n.autor = u.id
    WHERE n.id = ?
");
$stmt->execute([$id]);
$n = $stmt->fetch();
if (!$n) redirecionar('index.php');

$page_title = $n['titulo'];
include '../include/header.php';
?>

<div class="container" style="padding-top:2.5rem; padding-bottom:4rem">
    <div class="article-wrap">

        <div class="article-tag">
            <?php if ($n['categoria']): ?>
                <a href="index.php?cat=<?= urlencode($n['categoria']) ?>" class="card-tag">
                    <?= label_categoria($n['categoria']) ?>
                </a>
            <?php else: ?>
                <span class="card-tag">📰 Geral</span>
            <?php endif; ?>
        </div>

        <h1 class="article-title"><?= sanitizar($n['titulo']) ?></h1>

        <div class="article-meta">
            <span>✍ <span class="meta-author"><?= sanitizar($n['autor_nome']) ?></span></span>
            <span>📅 <?= formatar_data($n['data']) ?></span>
            <?php if (usuario_logado() && ($_SESSION['usuario_id'] == $n['autor'] || is_admin())): ?>
                <span style="margin-left:auto; display:flex; gap:.5rem; flex-wrap:wrap">
                    <a href="../admin/editar_noticia.php?id=<?= $n['id'] ?>" class="btn btn-ghost btn-sm">✏️ Editar</a>
                    <a href="../admin/excluir_noticia.php?id=<?= $n['id'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Excluir esta notícia permanentemente?')">🗑️ Excluir</a>
                </span>
            <?php endif; ?>
        </div>

        <?php if ($n['imagem']): ?>
            <img src="../assets/img/<?= sanitizar($n['imagem']) ?>"
                 alt="<?= sanitizar($n['titulo']) ?>" class="article-img">
        <?php endif; ?>

        <div class="article-body">
            <?php foreach (explode("\n", sanitizar($n['noticia'])) as $p): ?>
                <?php if (trim($p) !== ''): ?>
                    <p><?= nl2br($p) ?></p>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div class="article-actions">
            <a href="index.php" class="btn btn-ghost">← Voltar ao Portal</a>
            <?php if ($n['categoria']): ?>
                <a href="index.php?cat=<?= urlencode($n['categoria']) ?>" class="btn btn-ghost">
                    Ver mais em <?= label_categoria($n['categoria']) ?>
                </a>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include '../include/footer.php'; ?>

<?php
// admin/excluir_noticia.php — EduNews
require_once __DIR__ . '/../include/verifica_login.php';
require_once __DIR__ . '/../include/conexao.php';
require_once __DIR__ . '/../include/funcoes.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Admin pode excluir qualquer notícia; jornalista só as suas
if (is_admin()) {
    $stmt = $pdo->prepare("SELECT * FROM noticias WHERE id = ?");
    $stmt->execute([$id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM noticias WHERE id = ? AND autor = ?");
    $stmt->execute([$id, $_SESSION['usuario_id']]);
}
$n = $stmt->fetch();

if ($n) {
    $pasta = __DIR__ . '/../assets/img/';
    if ($n['imagem'] && file_exists($pasta . $n['imagem'])) {
        unlink($pasta . $n['imagem']);
    }
    $pdo->prepare("DELETE FROM noticias WHERE id = ?")
        ->execute([$id]);
}

redirecionar('dashboard.php');

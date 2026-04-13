<?php
// include/footer.php — EduNews Portal Estudantil
$is_admin_page = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$public_prefix = $is_admin_page ? '../public/' : '';
$admin_prefix  = $is_admin_page ? '' : '../admin/';
?>
<footer>
    <div class="footer-inner">
        <div class="footer-brand">
            <a href="<?= $public_prefix ?>index.php" class="logo" style="text-decoration:none">
                <span class="logo-name">Edu<em>News</em></span>
            </a>
            <p>O portal de referência em notícias para a comunidade estudantil. Cobertura completa de vestibulares, bolsas, pesquisa acadêmica e vida universitária no Brasil.</p>
            <span class="footer-badge">📚 PHP · MySQL · Portal Estudantil</span>
        </div>

        <div class="footer-col">
            <h4>Editorias</h4>
            <ul>
                <li><a href="<?= $public_prefix ?>index.php">🏠 Início</a></li>
                <li><a href="<?= $public_prefix ?>index.php?cat=vestibular">📝 Vestibular</a></li>
                <li><a href="<?= $public_prefix ?>index.php?cat=academico">🎓 Acadêmico</a></li>
                <li><a href="<?= $public_prefix ?>index.php?cat=bolsas">💰 Bolsas & Editais</a></li>
                <li><a href="<?= $public_prefix ?>index.php?cat=pesquisa">🔬 Pesquisa</a></li>
                <li><a href="<?= $public_prefix ?>index.php?cat=cultura">🎨 Cultura & Eventos</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Portal</h4>
            <ul>
                <?php if (usuario_logado()): ?>
                    <li><a href="<?= $admin_prefix ?>dashboard.php">📊 Dashboard</a></li>
                    <li><a href="<?= $admin_prefix ?>nova_noticia.php">📝 Nova Notícia</a></li>
                    <?php if (is_admin()): ?>
                        <li><a href="<?= $admin_prefix ?>gerenciar_usuarios.php">👥 Gerenciar Usuários</a></li>
                    <?php endif; ?>
                    <li><a href="<?= $admin_prefix ?>logout.php">🚪 Sair</a></li>
                <?php else: ?>
                    <li><a href="<?= $public_prefix ?>login.php">🔑 Login</a></li>
                    <li><a href="<?= $public_prefix ?>cadastro.php">📋 Cadastro</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p class="footer-copy">&copy; <?= date('Y') ?> <strong>EduNews</strong> — Todos os direitos reservados.</p>
        <span class="footer-tech">PHP 8 · MySQL · Portal Estudantil</span>
    </div>
</footer>
</body>
</html>

<?php
// admin/gerenciar_usuarios.php — EduNews (somente admin)
require_once __DIR__ . '/../include/verifica_login.php';
require_once __DIR__ . '/../include/verifica_admin.php';
require_once __DIR__ . '/../include/conexao.php';
require_once __DIR__ . '/../include/funcoes.php';

$erro = ''; $sucesso = '';
$acao = $_GET['acao'] ?? '';
$uid  = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;

if ($acao === 'aprovar' && $uid) {
    $pdo->prepare("UPDATE usuarios SET ativo=1 WHERE id=? AND tipo!='admin'")->execute([$uid]);
    $sucesso = 'Acesso aprovado com sucesso!';
}
if ($acao === 'revogar' && $uid) {
    $pdo->prepare("UPDATE usuarios SET ativo=0 WHERE id=? AND tipo!='admin'")->execute([$uid]);
    $sucesso = 'Acesso revogado.';
}
if ($acao === 'promover' && $uid) {
    $pdo->prepare("UPDATE usuarios SET tipo='admin',ativo=1 WHERE id=?")->execute([$uid]);
    $sucesso = 'Usuário promovido a administrador!';
}
if ($acao === 'rebaixar' && $uid) {
    if ($uid === (int)$_SESSION['usuario_id']) { $erro = 'Você não pode rebaixar a si mesmo.'; }
    else { $pdo->prepare("UPDATE usuarios SET tipo='jornalista' WHERE id=?")->execute([$uid]); $sucesso = 'Usuário rebaixado para jornalista.'; }
}
if ($acao === 'excluir' && $uid) {
    if ($uid === (int)$_SESSION['usuario_id']) { $erro = 'Você não pode excluir a si mesmo aqui.'; }
    else {
        $pasta = __DIR__ . '/../assets/img/';
        $imgs = $pdo->prepare("SELECT imagem FROM noticias WHERE autor=? AND imagem IS NOT NULL");
        $imgs->execute([$uid]);
        foreach ($imgs->fetchAll() as $row) { if (file_exists($pasta.$row['imagem'])) unlink($pasta.$row['imagem']); }
        $pdo->prepare("DELETE FROM usuarios WHERE id=?")->execute([$uid]);
        $sucesso = 'Usuário excluído com sucesso.';
    }
}

// Criar usuário
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['acao']??'')==='criar') {
    $nome  = sanitizar($_POST['nome']  ?? '');
    $email = sanitizar($_POST['email'] ?? '');
    $senha = $_POST['senha']           ?? '';
    $tipo  = in_array($_POST['tipo']??'',['admin','jornalista']) ? $_POST['tipo'] : 'jornalista';
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    if (!$nome||!$email||!$senha) { $erro='Nome, e-mail e senha obrigatórios.'; }
    elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) { $erro='E-mail inválido.'; }
    elseif (strlen($senha)<6) { $erro='Senha mínimo 6 caracteres.'; }
    else {
        $chk=$pdo->prepare("SELECT id FROM usuarios WHERE email=?"); $chk->execute([$email]);
        if ($chk->fetch()) { $erro='E-mail já cadastrado.'; }
        else {
            $hash=password_hash($senha,PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO usuarios (nome,email,senha,tipo,ativo) VALUES (?,?,?,?,?)")->execute([$nome,$email,$hash,$tipo,$ativo]);
            $sucesso="Usuário \"$nome\" criado!";
        }
    }
}

// Editar usuário
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['acao']??'')==='editar') {
    $edit_uid=(int)($_POST['uid']??0);
    $nome=sanitizar($_POST['nome']??''); $email=sanitizar($_POST['email']??'');
    $senha=$_POST['senha']??'';
    $tipo=in_array($_POST['tipo']??'',['admin','jornalista'])?$_POST['tipo']:'jornalista';
    $ativo=isset($_POST['ativo'])?1:0;
    if ($edit_uid===(int)$_SESSION['usuario_id']) { $tipo='admin'; $ativo=1; }
    if (!$nome||!$email) { $erro='Nome e e-mail obrigatórios.'; }
    elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) { $erro='E-mail inválido.'; }
    elseif ($senha&&strlen($senha)<6) { $erro='Senha mínimo 6 caracteres.'; }
    else {
        $chk=$pdo->prepare("SELECT id FROM usuarios WHERE email=? AND id!=?"); $chk->execute([$email,$edit_uid]);
        if ($chk->fetch()) { $erro='E-mail já pertence a outra conta.'; }
        else {
            if ($senha) {
                $hash=password_hash($senha,PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE usuarios SET nome=?,email=?,senha=?,tipo=?,ativo=? WHERE id=?")->execute([$nome,$email,$hash,$tipo,$ativo,$edit_uid]);
            } else {
                $pdo->prepare("UPDATE usuarios SET nome=?,email=?,tipo=?,ativo=? WHERE id=?")->execute([$nome,$email,$tipo,$ativo,$edit_uid]);
            }
            $sucesso="Usuário \"$nome\" atualizado!";
        }
    }
}

$usuarios = $pdo->query("
    SELECT u.*, (SELECT COUNT(*) FROM noticias WHERE autor=u.id) AS total_noticias
    FROM usuarios u ORDER BY u.criado_em DESC
")->fetchAll();

$editando = null;
if (isset($_GET['editar']) && (int)$_GET['editar']>0) {
    $se=$pdo->prepare("SELECT * FROM usuarios WHERE id=?");
    $se->execute([(int)$_GET['editar']]);
    $editando=$se->fetch();
}

$total_users  = count($usuarios);
$pendentes    = count(array_filter($usuarios, fn($u) => !$u['ativo'] && $u['tipo']!=='admin'));

$page_title = 'Gerenciar Usuários';
include '../include/header.php';
?>

<div class="dash-page">
<div class="container">

    <div class="dash-stats">
        <div class="stat-card">
            <span class="stat-number"><?= $total_users ?></span>
            <span class="stat-label">Total de Usuários</span>
        </div>
        <div class="stat-card">
            <span class="stat-number" style="color:<?= $pendentes>0?'#e74c3c':'inherit' ?>"><?= $pendentes ?></span>
            <span class="stat-label">Aguardando Aprovação</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?= count(array_filter($usuarios,fn($u)=>$u['tipo']==='admin')) ?></span>
            <span class="stat-label">Administradores</span>
        </div>
    </div>

    <div class="dash-layout">
        <aside class="dash-sidebar">
            <div class="dash-profile-box">
                <div class="dash-avatar">👑</div>
                <div class="dash-profile-name"><?= sanitizar($_SESSION['usuario_nome']) ?></div>
                <div class="dash-profile-role">Administrador</div>
            </div>
            <nav class="dash-nav">
                <a href="dashboard.php" class="dash-nav-item">📊 Dashboard</a>
                <a href="gerenciar_usuarios.php" class="dash-nav-item active">👥 Usuários</a>
                <a href="nova_noticia.php" class="dash-nav-item">✏️ Nova Notícia</a>
                <a href="../public/index.php" class="dash-nav-item">🏠 Ver Portal</a>
                <div class="dash-nav-sep"></div>
                <a href="logout.php" class="dash-nav-item danger">🚪 Sair</a>
            </nav>
        </aside>

        <main>
            <?php if ($erro):   ?><div class="alert alert-error"   style="margin-bottom:1.25rem">⚠ <?= $erro ?></div><?php endif; ?>
            <?php if ($sucesso):?><div class="alert alert-success" style="margin-bottom:1.25rem">✔ <?= $sucesso ?></div><?php endif; ?>

            <!-- Criar usuário -->
            <div class="dash-main-card" style="margin-bottom:1.5rem">
                <div class="dash-main-header">
                    <h2>➕ Criar Novo Usuário</h2>
                    <button class="btn btn-primary btn-sm" onclick="toggleForm('form-criar')">+ Criar usuário</button>
                </div>
                <div class="dash-main-body" id="form-criar" style="display:none">
                    <form method="POST">
                        <input type="hidden" name="acao" value="criar">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nome completo *</label>
                                <input class="form-control" type="text" name="nome" required placeholder="Nome do usuário">
                            </div>
                            <div class="form-group">
                                <label class="form-label">E-mail *</label>
                                <input class="form-control" type="email" name="email" required placeholder="email@exemplo.com">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Senha *</label>
                                <input class="form-control" type="password" name="senha" required placeholder="Mínimo 6 caracteres">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tipo de conta</label>
                                <select class="form-control" name="tipo">
                                    <option value="jornalista">🖊️ Jornalista</option>
                                    <option value="admin">👑 Administrador</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" style="display:flex;align-items:center;gap:.5rem">
                            <input type="checkbox" name="ativo" id="ativo_criar" value="1" checked style="width:auto;accent-color:var(--red)">
                            <label for="ativo_criar" class="form-label" style="margin:0;cursor:pointer">Conta já ativada (pode fazer login imediatamente)</label>
                        </div>
                        <div class="flex gap-2 flex-wrap mt-2">
                            <button type="submit" class="btn btn-primary">Criar usuário</button>
                            <button type="button" class="btn btn-ghost" onclick="toggleForm('form-criar')">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Editar usuário -->
            <?php if ($editando): ?>
            <div class="dash-main-card" style="margin-bottom:1.5rem;border:2px solid var(--red)">
                <div class="dash-main-header">
                    <h2>✏️ Editando: <?= sanitizar($editando['nome']) ?></h2>
                    <a href="gerenciar_usuarios.php" class="btn btn-ghost btn-sm">Cancelar</a>
                </div>
                <div class="dash-main-body">
                    <form method="POST">
                        <input type="hidden" name="acao" value="editar">
                        <input type="hidden" name="uid" value="<?= $editando['id'] ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nome completo *</label>
                                <input class="form-control" type="text" name="nome" required value="<?= sanitizar($editando['nome']) ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">E-mail *</label>
                                <input class="form-control" type="email" name="email" required value="<?= sanitizar($editando['email']) ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nova senha</label>
                                <input class="form-control" type="password" name="senha" placeholder="Deixe vazio para não alterar">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tipo de conta</label>
                                <select class="form-control" name="tipo" <?= $editando['id']===(int)$_SESSION['usuario_id']?'disabled':'' ?>>
                                    <option value="jornalista" <?= $editando['tipo']==='jornalista'?'selected':'' ?>>🖊️ Jornalista</option>
                                    <option value="admin"      <?= $editando['tipo']==='admin'     ?'selected':'' ?>>👑 Administrador</option>
                                </select>
                                <?php if ($editando['id']===(int)$_SESSION['usuario_id']): ?>
                                    <input type="hidden" name="tipo" value="admin">
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group" style="display:flex;align-items:center;gap:.5rem">
                            <input type="checkbox" name="ativo" id="ativo_edit" value="1"
                                   <?= $editando['ativo']?'checked':'' ?>
                                   <?= $editando['id']===(int)$_SESSION['usuario_id']?'disabled':'' ?>
                                   style="width:auto;accent-color:var(--red)">
                            <label for="ativo_edit" class="form-label" style="margin:0;cursor:pointer">Conta ativa (permite login)</label>
                        </div>
                        <div class="flex gap-2 flex-wrap mt-2">
                            <button type="submit" class="btn btn-primary">Salvar alterações</button>
                            <a href="gerenciar_usuarios.php" class="btn btn-ghost">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Lista de usuários -->
            <div class="dash-main-card">
                <div class="dash-main-header"><h2>👥 Todos os Usuários</h2></div>
                <div class="dash-main-body">
                    <?php if (empty($usuarios)): ?>
                        <div class="empty-state"><span class="empty-icon">👤</span><h3>Nenhum usuário</h3></div>
                    <?php else: ?>
                    <div class="users-list">
                        <?php foreach ($usuarios as $u): ?>
                        <div class="user-card <?= !$u['ativo']&&$u['tipo']!=='admin'?'user-card--pending':'' ?>">

                            <!-- Avatar + info -->
                            <div class="user-card-left">
                                <div class="user-avatar"><?= mb_strtoupper(mb_substr($u['nome'],0,1)) ?></div>
                                <div class="user-info">
                                    <div class="user-name">
                                        <?= sanitizar($u['nome']) ?>
                                        <?php if ($u['id']===(int)$_SESSION['usuario_id']): ?>
                                            <span class="tag-you">você</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="user-email"><?= sanitizar($u['email']) ?></div>
                                    <div class="user-meta">
                                        <?php if ($u['tipo']==='admin'): ?>
                                            <span class="tag-tipo admin">👑 Admin</span>
                                        <?php else: ?>
                                            <span class="tag-tipo jornalista">🖊️ Jornalista</span>
                                        <?php endif; ?>
                                        <?php if ($u['tipo']==='admin'): ?>
                                            <span class="tag-status ativo">● Ativo</span>
                                        <?php elseif ($u['ativo']): ?>
                                            <span class="tag-status ativo">● Aprovado</span>
                                        <?php else: ?>
                                            <span class="tag-status pendente">● Pendente</span>
                                        <?php endif; ?>
                                        <span class="tag-news"><?= $u['total_noticias'] ?> notícia<?= $u['total_noticias']!=1?'s':'' ?></span>
                                        <span class="tag-date">Desde <?= formatar_data_curta($u['criado_em']) ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Ações -->
                            <?php if ($u['id']!==(int)$_SESSION['usuario_id']): ?>
                            <div class="user-card-actions">

                                <!-- Editar -->
                                <a href="gerenciar_usuarios.php?editar=<?= $u['id'] ?>"
                                   class="uact-btn uact-edit" title="Editar usuário">
                                    <span class="uact-icon">✏️</span>
                                    <span class="uact-label">Editar</span>
                                </a>

                                <!-- Aprovar / Revogar -->
                                <?php if ($u['tipo']!=='admin'): ?>
                                    <?php if (!$u['ativo']): ?>
                                    <a href="gerenciar_usuarios.php?acao=aprovar&uid=<?= $u['id'] ?>"
                                       class="uact-btn uact-approve"
                                       title="Aprovar acesso"
                                       onclick="return confirm('Aprovar acesso de <?= sanitizar($u['nome']) ?>?')">
                                        <span class="uact-icon">✅</span>
                                        <span class="uact-label">Aprovar</span>
                                    </a>
                                    <?php else: ?>
                                    <a href="gerenciar_usuarios.php?acao=revogar&uid=<?= $u['id'] ?>"
                                       class="uact-btn uact-revoke"
                                       title="Revogar acesso"
                                       onclick="return confirm('Revogar acesso de <?= sanitizar($u['nome']) ?>?')">
                                        <span class="uact-icon">🚫</span>
                                        <span class="uact-label">Revogar</span>
                                    </a>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- Promover / Rebaixar -->
                                <?php if ($u['tipo']!=='admin'): ?>
                                <a href="gerenciar_usuarios.php?acao=promover&uid=<?= $u['id'] ?>"
                                   class="uact-btn uact-promote"
                                   title="Promover a admin"
                                   onclick="return confirm('Promover <?= sanitizar($u['nome']) ?> a administrador?')">
                                    <span class="uact-icon">👑</span>
                                    <span class="uact-label">Promover</span>
                                </a>
                                <?php else: ?>
                                <a href="gerenciar_usuarios.php?acao=rebaixar&uid=<?= $u['id'] ?>"
                                   class="uact-btn uact-demote"
                                   title="Rebaixar para jornalista"
                                   onclick="return confirm('Rebaixar <?= sanitizar($u['nome']) ?> para jornalista?')">
                                    <span class="uact-icon">🖊️</span>
                                    <span class="uact-label">Rebaixar</span>
                                </a>
                                <?php endif; ?>

                                <!-- Excluir -->
                                <a href="gerenciar_usuarios.php?acao=excluir&uid=<?= $u['id'] ?>"
                                   class="uact-btn uact-delete"
                                   title="Excluir usuário"
                                   onclick="return confirm('Excluir permanentemente <?= sanitizar($u['nome']) ?> e todas as suas notícias?')">
                                    <span class="uact-icon">🗑️</span>
                                    <span class="uact-label">Excluir</span>
                                </a>
                            </div>
                            <?php else: ?>
                            <div class="user-card-actions">
                                <span style="font-size:.78rem;color:var(--text-3)">Sua conta</span>
                            </div>
                            <?php endif; ?>

                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>
</div>
</div>

<script>
function toggleForm(id) {
    var el = document.getElementById(id);
    if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
<?php if ($erro && ($_POST['acao']??'')==='criar'): ?>
document.addEventListener('DOMContentLoaded', function(){ toggleForm('form-criar'); });
<?php endif; ?>
</script>

<?php include '../include/footer.php'; ?>

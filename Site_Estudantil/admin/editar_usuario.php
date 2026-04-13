<?php
// editar_usuario.php
require_once 'verifica_login.php';
require_once 'conexao.php';
require_once 'funcoes.php';

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = sanitizar($_POST['nome'] ?? '');
    $email = sanitizar($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (!$nome || !$email) {
        $erro = 'Nome e e-mail são obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } else {
        // Verifica se e-mail pertence a outro usuário
        $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $check->execute([$email, $_SESSION['usuario_id']]);
        if ($check->fetch()) {
            $erro = 'Este e-mail já está sendo usado por outra conta.';
        } else {
            if ($senha) {
                if (strlen($senha) < 6) {
                    $erro = 'Senha deve ter no mínimo 6 caracteres.';
                } else {
                    $hash = password_hash($senha, PASSWORD_DEFAULT);
                    $upd = $pdo->prepare("UPDATE usuarios SET nome=?, email=?, senha=? WHERE id=?");
                    $upd->execute([$nome, $email, $hash, $_SESSION['usuario_id']]);
                }
            } else {
                $upd = $pdo->prepare("UPDATE usuarios SET nome=?, email=? WHERE id=?");
                $upd->execute([$nome, $email, $_SESSION['usuario_id']]);
            }

            if (!$erro) {
                $_SESSION['usuario_nome'] = $nome;
                $sucesso = 'Dados atualizados com sucesso!';
                // Recarrega usuário
                $stmt->execute([$_SESSION['usuario_id']]);
                $usuario = $stmt->fetch();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta — GamesPress</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="logo">📚 <span>Acadêmico</span>Press</div>
    <nav>
        <a href="dashboard.php">← Painel</a>
        <a href="logout.php" class="btn btn-secondary">Sair</a>
    </nav>
</header>

<div class="form-box">
    <h2>👤 Minha Conta</h2>

    <?php if ($erro): ?>
        <div class="alert alert-error"><?= $erro ?></div>
    <?php endif; ?>
    <?php if ($sucesso): ?>
        <div class="alert alert-success"><?= $sucesso ?></div>
    <?php endif; ?>

    <form method="POST" action="editar_usuario.php">
        <div class="form-group">
            <label>Nome</label>
            <input type="text" name="nome" required value="<?= sanitizar($usuario['nome']) ?>">
        </div>
        <div class="form-group">
            <label>E-mail</label>
            <input type="email" name="email" required value="<?= sanitizar($usuario['email']) ?>">
        </div>
        <div class="form-group">
            <label>Nova Senha (deixe em branco para não alterar)</label>
            <input type="password" name="senha" placeholder="Nova senha (mín. 6 caracteres)">
        </div>
        <div style="display:flex; gap:1rem; flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="excluir_usuario.php"
               class="btn btn-danger"
               onclick="return confirm('Tem certeza? Esta ação excluirá sua conta e todas as suas notícias permanentemente.')">
               🗑️ Excluir Conta
            </a>
        </div>
    </form>
</div>

<footer>
    <p>© <?= date('Y') ?> <span>GamesPress</span></p>
</footer>

</body>
</html>

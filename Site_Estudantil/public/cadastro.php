<?php
// public/cadastro.php — EduNews
session_start();
require_once __DIR__ . '/../include/conexao.php';
require_once __DIR__ . '/../include/funcoes.php';

if (usuario_logado()) redirecionar('../admin/dashboard.php');

$erro = ''; $sucesso = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = sanitizar($_POST['nome']  ?? '');
    $email = sanitizar($_POST['email'] ?? '');
    $senha = $_POST['senha']            ?? '';
    $conf  = $_POST['confirmar_senha']  ?? '';

    if (!$nome || !$email || !$senha || !$conf) {
        $erro = 'Preencha todos os campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha precisa ter no mínimo 6 caracteres.';
    } elseif ($senha !== $conf) {
        $erro = 'As senhas não coincidem.';
    } else {
        $chk = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $chk->execute([$email]);
        if ($chk->fetch()) {
            $erro = 'Este e-mail já está cadastrado.';
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            // ativo = 0 por padrão: aguarda aprovação do admin
            $ins = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo, ativo) VALUES (?,?,?,'jornalista',0)");
            $ins->execute([$nome, $email, $hash]);
            $sucesso = 'Conta criada! Aguarde o administrador aprovar seu acesso antes de fazer login.';
        }
    }
}

$page_title = 'Criar Conta';
include '../include/header.php';
?>

<div class="form-page">
    <div class="form-card">
        <div class="form-card-header">
            <h2>Criar conta</h2>
            <p>Junte-se à equipe de jornalistas do EduNews</p>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-error">⚠ <?= $erro ?></div>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <div class="alert alert-success">✔ <?= $sucesso ?></div>
        <?php else: ?>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Nome completo</label>
                <input class="form-control" type="text" name="nome" placeholder="Seu nome" required
                       value="<?= isset($_POST['nome']) ? sanitizar($_POST['nome']) : '' ?>">
            </div>
            <div class="form-group">
                <label class="form-label">E-mail</label>
                <input class="form-control" type="email" name="email" placeholder="seu@email.com" required
                       value="<?= isset($_POST['email']) ? sanitizar($_POST['email']) : '' ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Senha</label>
                <input class="form-control" type="password" name="senha" placeholder="Mínimo 6 caracteres" required>
            </div>
            <div class="form-group">
                <label class="form-label">Confirmar senha</label>
                <input class="form-control" type="password" name="confirmar_senha" placeholder="Repita a senha" required>
            </div>
            <button type="submit" class="btn btn-primary w-full mt-2">Criar conta</button>
        </form>

        <div class="alert alert-info mt-3" style="font-size:.85rem">
            ℹ️ Após criar a conta, um administrador precisará aprovar o seu acesso antes que você consiga fazer login.
        </div>
        <?php endif; ?>

        <p class="text-muted text-center mt-3">
            Já tem conta? <a href="login.php">Fazer login</a>
        </p>
    </div>
</div>

<?php include '../include/footer.php'; ?>

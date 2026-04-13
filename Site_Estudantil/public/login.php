<?php
// public/login.php — EduNews
session_start();
require_once __DIR__ . '/../include/conexao.php';
require_once __DIR__ . '/../include/funcoes.php';

if (usuario_logado()) redirecionar('../admin/dashboard.php');

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizar($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (!$email || !$senha) {
        $erro = 'Preencha e-mail e senha.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $u = $stmt->fetch();

        if (!$u) {
            $erro = 'E-mail ou senha incorretos.';
        } elseif (!password_verify($senha, $u['senha'])) {
            $erro = 'E-mail ou senha incorretos.';
        } elseif ($u['tipo'] !== 'admin' && !(bool)$u['ativo']) {
            // Conta aguardando aprovação do admin
            $erro = '⏳ Sua conta ainda não foi aprovada pelo administrador. Aguarde a liberação.';
        } else {
            $_SESSION['usuario_id']   = $u['id'];
            $_SESSION['usuario_nome'] = $u['nome'];
            $_SESSION['usuario_tipo'] = $u['tipo'];
            redirecionar('../admin/dashboard.php');
        }
    }
}

$page_title = 'Login';
include '../include/header.php';
?>

<div class="form-page">
    <div class="form-card">
        <div class="form-card-header">
            <h2>Entrar no portal</h2>
            <p>Acesse o painel de jornalista do EduNews</p>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-error">⚠ <?= $erro ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label" for="email">E-mail</label>
                <input class="form-control" type="email" id="email" name="email"
                       placeholder="seu@email.com" required
                       value="<?= isset($_POST['email']) ? sanitizar($_POST['email']) : '' ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="senha">Senha</label>
                <input class="form-control" type="password" id="senha" name="senha"
                       placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary w-full mt-2">Entrar</button>
        </form>

        <div class="alert alert-info mt-3" style="font-size:.85rem">
            ℹ️ Após criar sua conta, aguarde a aprovação do administrador para acessar o painel.
        </div>

        <p class="text-muted text-center mt-3">
            Não tem conta? <a href="cadastro.php">Criar conta gratuita</a>
        </p>
    </div>
</div>

<?php include '../include/footer.php'; ?>

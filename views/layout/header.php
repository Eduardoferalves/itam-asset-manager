<?php
$usuario = $_SESSION['usuario'] ?? null;
$modulo = $_GET['modulo'] ?? '';
$acao = $_GET['acao'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITAM - Sistema de Gestão de Ativos de TI</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts & Custom CSS (Vínculo Crítico após Bootstrap) -->
    <link href="css/custom.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<?php if ($usuario): ?>
<!-- Navbar Premium com Efeito de Vidro (Glassmorphism) -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-glass mb-4 shadow">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="?modulo=ativos&acao=listagem">
            <i class="bi bi-cpu-fill me-2 fs-3 text-info"></i>
            <span>ITAM.Asset</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center py-2 px-3 <?= ($modulo === 'ativos') ? 'active text-info fw-bold' : '' ?>" href="?modulo=ativos&acao=listagem">
                        <i class="bi bi-laptop me-2"></i> Ativos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center py-2 px-3 <?= ($modulo === 'manutencao') ? 'active text-info fw-bold' : '' ?>" href="?modulo=manutencao&acao=cadastro">
                        <i class="bi bi-tools me-2"></i> Manutenções
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center py-2 px-3 <?= ($modulo === 'relatorio') ? 'active text-info fw-bold' : '' ?>" href="?modulo=relatorio&acao=index">
                        <i class="bi bi-bar-chart-line me-2"></i> Custos e Relatórios
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center navbar-user-section">
                <span class="navbar-text text-light me-3 small">
                    <i class="bi bi-person-circle me-1 text-info"></i>
                    <?= htmlspecialchars((string)$usuario['nome'], ENT_QUOTES, 'UTF-8') ?>
                </span>
                <a href="?modulo=auth&acao=logout" class="btn btn-sm btn-secondary-neon text-danger border-danger-subtle d-flex align-items-center px-3 py-1">
                    <i class="bi bi-box-arrow-right me-1"></i> Sair
                </a>
            </div>
        </div>
    </div>
</nav>
<?php endif; ?>

<div class="container <?= ($modulo === 'auth' && $acao === 'login') ? '' : 'pb-5' ?>">

<!-- Toast de Notificações Flash Flutuante -->
<?php if (isset($flash) && $flash): ?>
    <div class="flash-toast flash-toast-<?= htmlspecialchars((string)$flash['type'], ENT_QUOTES, 'UTF-8') ?>" id="flashToast">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <?php if ($flash['type'] === 'success'): ?>
                    <i class="bi bi-check-circle-fill text-success fs-4 me-2"></i>
                <?php else: ?>
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-4 me-2"></i>
                <?php endif; ?>
                <div>
                    <strong class="text-white"><?= ($flash['type'] === 'success') ? 'Sucesso' : 'Atenção' ?></strong>
                    <div class="text-light opacity-75 small"><?= htmlspecialchars((string)$flash['message'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white ms-3" onclick="document.getElementById('flashToast').style.display='none'"></button>
        </div>
    </div>
    <script>
        // Fechamento automático em 5 segundos
        setTimeout(function() {
            var toast = document.getElementById('flashToast');
            if (toast) {
                toast.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(function() { toast.remove(); }, 500);
            }
        }, 5000);
    </script>
<?php endif; ?>

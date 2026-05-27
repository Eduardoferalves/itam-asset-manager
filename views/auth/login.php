<?php
$email_val = $old_input['email'] ?? '';
?>
<div class="login-container">
    <div class="row w-100 justify-content-center px-3">
        <div class="col-12 col-sm-8 col-md-6 col-lg-4">
            <div class="card card-glass p-4 border-glass shadow-lg">
                <div class="text-center mb-4">
                    <div class="d-inline-block bg-info bg-opacity-10 p-3 rounded-circle mb-3">
                        <i class="bi bi-cpu-fill text-info fs-1"></i>
                    </div>
                    <h2 class="text-white mb-1">ITAM System</h2>
                    <p class="text-secondary small">Gestão de Ativos de TI Simplificada</p>
                </div>
                
                <form action="?modulo=auth&acao=autenticar" method="POST">
                    <!-- Injeção Crítica de Token CSRF -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label text-secondary small">Endereço de E-mail</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-glass text-secondary">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" name="email" id="email" class="form-control form-control-glass" value="<?= htmlspecialchars($email_val) ?>" placeholder="nome@exemplo.com" required autocomplete="email">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="senha" class="form-label text-secondary small">Senha de Acesso</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-glass text-secondary">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" name="senha" id="senha" class="form-control form-control-glass" placeholder="Sua senha secreta" required autocomplete="current-password">
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary-neon py-2">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Autenticar
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="text-center mt-3">
                <p class="text-secondary small">Acesso restrito para administradores credenciados.</p>
                <div class="text-info bg-info bg-opacity-10 rounded px-2 py-1 d-inline-block small">
                    <i class="bi bi-info-circle me-1"></i> Demo: <strong>admin@itam.com</strong> / <strong>admin123</strong>
                </div>
            </div>
        </div>
    </div>
</div>

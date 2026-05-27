<?php
$isEdit = isset($ativo) && $ativo !== null;
$actionUrl = $isEdit ? '?modulo=ativos&acao=atualizar' : '?modulo=ativos&acao=salvar';
$titulo = $isEdit ? 'Editar Ativo de TI' : 'Cadastrar Novo Ativo';
$subtitulo = $isEdit ? 'Modifique as especificações do ativo selecionado' : 'Registre um novo ativo tecnológico no inventário corporativo';

// Fallbacks para repopular o formulário (UX: old_input tem precedência sobre os dados do banco em caso de erro)
$patrimonio_val = $old_input['patrimonio'] ?? ($ativo['patrimonio'] ?? '');
$status_val = $old_input['status'] ?? ($ativo['status'] ?? '');
$data_aquisicao_val = $old_input['data_aquisicao'] ?? ($ativo['data_aquisicao'] ?? '');
$id_categoria_val = $old_input['id_categoria'] ?? ($ativo['id_categoria'] ?? '');
$id_departamento_val = $old_input['id_departamento'] ?? ($ativo['id_departamento'] ?? '');
$id_fornecedor_val = $old_input['id_fornecedor'] ?? ($ativo['id_fornecedor'] ?? '');
?>

<div class="row mb-4">
    <div class="col-12 col-md-8 mx-auto">
        <a href="?modulo=ativos&acao=listagem" class="btn btn-secondary-neon btn-sm mb-3">
            <i class="bi bi-arrow-left me-1"></i> Voltar para a Listagem
        </a>
        <h1 class="text-white mb-1"><i class="bi bi-laptop me-2 text-info"></i><?= $titulo ?></h1>
        <p class="text-secondary"><?= $subtitulo ?></p>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-8 mx-auto">
        <div class="card card-glass p-4 border-glass shadow-lg">
            <form action="<?= $actionUrl ?>" method="POST">
                <!-- Token CSRF Obrigatório em todos os formulários -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                
                <?php if ($isEdit): ?>
                    <!-- ID do Ativo para Atualização -->
                    <input type="hidden" name="id_ativo" value="<?= $ativo['id_ativo'] ?>">
                <?php endif; ?>
                
                <div class="row g-3 mb-4">
                    <!-- Código do Patrimônio -->
                    <div class="col-12 col-sm-6">
                        <label for="patrimonio" class="form-label text-secondary small">Código de Patrimônio <span class="text-danger">*</span></label>
                        <input type="text" name="patrimonio" id="patrimonio" class="form-control form-control-glass" 
                               value="<?= htmlspecialchars($patrimonio_val) ?>" placeholder="Ex: NOTE-045, DESK-102" required>
                        <div class="form-text text-secondary opacity-75 small-text">Código identificador exclusivo do ativo.</div>
                    </div>
                    
                    <!-- Status do Ativo -->
                    <div class="col-12 col-sm-6">
                        <label for="status" class="form-label text-secondary small">Status Inicial <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select form-select-glass" required>
                            <option value="Ativo" <?= ($status_val === 'Ativo') ? 'selected' : '' ?>>Ativo</option>
                            <option value="Inativo" <?= ($status_val === 'Inativo') ? 'selected' : '' ?>>Inativo</option>
                            <option value="Em Manutenção" <?= ($status_val === 'Em Manutenção') ? 'selected' : '' ?>>Em Manutenção</option>
                        </select>
                        <div class="form-text text-secondary opacity-75 small-text">Defina a condição operacional do ativo.</div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <!-- Categoria -->
                    <div class="col-12 col-sm-6">
                        <label for="id_categoria" class="form-label text-secondary small">Categoria do Ativo</label>
                        <select name="id_categoria" id="id_categoria" class="form-select form-select-glass">
                            <option value="">Selecione uma Categoria...</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id_categoria'] ?>" <?= ($id_categoria_val == $cat['id_categoria']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['descricao']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-secondary opacity-75 small-text">Notebook, Monitor, Desktop, etc.</div>
                    </div>
                    
                    <!-- Data de Aquisição -->
                    <div class="col-12 col-sm-6">
                        <label for="data_aquisicao" class="form-label text-secondary small">Data de Aquisição <span class="text-danger">*</span></label>
                        <input type="date" name="data_aquisicao" id="data_aquisicao" class="form-control form-control-glass" 
                               value="<?= htmlspecialchars($data_aquisicao_val) ?>" required>
                        <div class="form-text text-secondary opacity-75 small-text">Data em que o ativo foi adquirido.</div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <!-- Departamento Destinatário -->
                    <div class="col-12 col-sm-6">
                        <label for="id_departamento" class="form-label text-secondary small">Departamento Responsável</label>
                        <select name="id_departamento" id="id_departamento" class="form-select form-select-glass">
                            <option value="">Selecione um Departamento...</option>
                            <?php foreach ($departamentos as $dep): ?>
                                <option value="<?= $dep['id_departamento'] ?>" <?= ($id_departamento_val == $dep['id_departamento']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dep['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-secondary opacity-75 small-text">Setor no qual o ativo será alocado.</div>
                    </div>

                    <!-- Fornecedor Adquirido -->
                    <div class="col-12 col-sm-6">
                        <label for="id_fornecedor" class="form-label text-secondary small">Fornecedor do Equipamento</label>
                        <select name="id_fornecedor" id="id_fornecedor" class="form-select form-select-glass">
                            <option value="">Selecione um Fornecedor...</option>
                            <?php foreach ($fornecedores as $forn): ?>
                                <option value="<?= $forn['id_fornecedor'] ?>" <?= ($id_fornecedor_val == $forn['id_fornecedor']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($forn['nome_empresa']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-secondary opacity-75 small-text">Origem de aquisição do equipamento.</div>
                    </div>
                </div>

                <hr class="border-glass mb-4">

                <div class="d-flex justify-content-end gap-3">
                    <a href="?modulo=ativos&acao=listagem" class="btn btn-secondary-neon px-4">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary-neon px-4">
                        <i class="bi bi-save me-1"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

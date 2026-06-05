<?php
// Fallbacks para repopular o formulário (UX: old_input tem precedência)
$modo_leitura = $modo_leitura ?? false;
$manutencao = $manutencao ?? null;

$id_ativo_val = $old_input['id_ativo'] ?? ($manutencao['id_ativo'] ?? $id_ativo_selecionado);
$custo_val = $old_input['custo'] ?? ($manutencao['custo'] ?? '');
$descricao_val = $old_input['descricao'] ?? ($manutencao['descricao'] ?? '');

$readonly_attr = $modo_leitura ? 'readonly' : '';
?>
<div class="row mb-4">
    <div class="col-12 col-md-8 mx-auto">
        <a href="?modulo=manutencao&acao=listagem" class="btn btn-secondary-neon btn-sm mb-3">
            <i class="bi bi-arrow-left me-1"></i> Voltar para a Listagem
        </a>
        <h1 class="text-white mb-1"><i class="bi bi-tools me-2 text-info"></i><?= $modo_leitura ? 'Detalhes da Manutenção' : 'Registrar Manutenção' ?></h1>
        <p class="text-secondary">
            <?= $modo_leitura ? 'Histórico financeiro e técnico desta manutenção (Registro Imutável)' : 'Lançamento de custos e reparos técnicos sobre ativos patrimoniais' ?>
        </p>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-8 mx-auto">
        <div class="card card-glass p-4 border-glass shadow-lg">
            <form id="form-manutencao" action="?modulo=manutencao&acao=salvar" method="POST">
                <!-- Token CSRF Obrigatório -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="mb-4">
                    <label for="id_ativo" class="form-label text-secondary small">Selecione o Ativo de TI <span class="text-danger">*</span></label>
                    <?php if ($modo_leitura): ?>
                        <?php
                        $patrimonio_selecionado = 'Ativo não encontrado';
                        foreach ($ativos as $ativo) {
                            if ($ativo['id_ativo'] == $id_ativo_val) {
                                $patrimonio_selecionado = $ativo['patrimonio'] . ' (Status Atual: ' . $ativo['status'] . ')';
                                break;
                            }
                        }
                        ?>
                        <input type="text" id="id_ativo_display" class="form-control form-control-glass" value="<?= htmlspecialchars((string)$patrimonio_selecionado, ENT_QUOTES, 'UTF-8') ?>" readonly>
                        <input type="hidden" name="id_ativo" value="<?= htmlspecialchars((string)$id_ativo_val, ENT_QUOTES, 'UTF-8') ?>">
                    <?php else: ?>
                        <select name="id_ativo" id="id_ativo" class="form-select form-select-glass" required>
                            <option value="">Selecione um ativo pelo patrimônio...</option>
                            <?php foreach ($ativos as $ativo): ?>
                                <option value="<?= htmlspecialchars((string)$ativo['id_ativo'], ENT_QUOTES, 'UTF-8') ?>" <?= ($id_ativo_val == $ativo['id_ativo']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string)$ativo['patrimonio'], ENT_QUOTES, 'UTF-8') ?> (Status Atual: <?= htmlspecialchars((string)$ativo['status'], ENT_QUOTES, 'UTF-8') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-secondary opacity-75 small-text">O ativo será marcado automaticamente como "Em Manutenção" após o registro.</div>
                    <?php endif; ?>
                </div>

                <div class="row g-3 mb-4">
                    <!-- Custo da Manutenção -->
                    <div class="col-12">
                        <label for="custo" class="form-label text-secondary small">Custo Total (R$) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-glass text-secondary">R$</span>
                            <input type="number" name="custo" id="custo" class="form-control form-control-glass" 
                                   value="<?= htmlspecialchars((string)$custo_val, ENT_QUOTES, 'UTF-8') ?>" placeholder="0,00" step="0.01" min="0.00" required <?= $readonly_attr ?>>
                        </div>
                        <div class="form-text text-secondary opacity-75 small-text">Informe o valor total gasto com peças ou serviços terceirizados.</div>
                    </div>
                </div>

                <!-- Descrição do Reparo -->
                <div class="mb-4">
                    <label for="descricao" class="form-label text-secondary small">Descrição Técnica da Manutenção <span class="text-danger">*</span></label>
                    <textarea name="descricao" id="descricao" class="form-control form-control-glass" rows="4" 
                              placeholder="Descreva detalhadamente o defeito constatado, peças substituídas e procedimentos realizados..." required <?= $readonly_attr ?>><?= htmlspecialchars((string)$descricao_val, ENT_QUOTES, 'UTF-8') ?></textarea>
                    <div class="form-text text-secondary opacity-75 small-text">Mínimo de informações para fins de auditoria e histórico do ativo.</div>
                </div>

                <hr class="border-glass mb-4">

                <div class="d-flex justify-content-end gap-3">
                    <a href="?modulo=manutencao&acao=listagem" class="btn btn-secondary-neon px-4">
                        <?= $modo_leitura ? 'Voltar' : 'Cancelar' ?>
                    </a>
                    <?php if (!$modo_leitura): ?>
                    <button type="button" class="btn btn-primary-neon px-4" data-bs-toggle="modal" data-bs-target="#confirmModal" data-bs-backdrop="static">
                        <i class="bi bi-check-lg me-1"></i> Gravar Manutenção
                    </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Defensivo de Confirmação (Movido para fora do container para evitar bugs de backdrop z-index) -->
<?php if (!$modo_leitura): ?>
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-glass shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white" id="confirmModalLabel">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Confirmação de Registro
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-light">
                <p>Por motivos de auditoria, manutenções são registros financeiros imutáveis. Verifique o valor e a descrição.</p> 
                <p class="small text-danger"><i class="bi bi-info-circle me-1"></i> <strong>Não será possível editar ou excluir este registro após a gravação.</strong></p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary-neon" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="form-manutencao" class="btn btn-primary-neon">Confirmar Gravação</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

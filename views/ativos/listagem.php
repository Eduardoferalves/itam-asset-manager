<?php
// Contar totais para o painel de estatísticas
$countAtivo = 0;
$countInativo = 0;
$countManutencao = 0;
foreach ($ativos as $a) {
    if ($a['status'] === 'Ativo') $countAtivo++;
    elseif ($a['status'] === 'Inativo') $countInativo++;
    elseif ($a['status'] === 'Em Manutenção') $countManutencao++;
}
$countTotal = count($ativos);
?>

<div class="row align-items-center mb-4">
    <div class="col-12 col-md-6">
        <h1 class="text-white mb-1"><i class="bi bi-laptop me-2 text-info"></i>Gestão de Ativos</h1>
        <p class="text-secondary mb-0">Controle e rastreabilidade de ativos tecnológicos corporativos</p>
    </div>
    <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
        <a href="?modulo=ativos&acao=cadastro" class="btn btn-primary-neon px-4 py-2">
            <i class="bi bi-plus-lg me-1"></i> Novo Ativo
        </a>
    </div>
</div>

<!-- Grid de Painéis Rápidos (WOW Design Cards) -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card card-glass p-3 d-flex flex-row align-items-center">
            <div class="bg-info bg-opacity-10 p-3 rounded-3 text-info me-3">
                <i class="bi bi-laptop fs-3"></i>
            </div>
            <div>
                <h4 class="text-white mb-0"><?= htmlspecialchars((string)$countTotal, ENT_QUOTES, 'UTF-8') ?></h4>
                <span class="text-secondary small">Total Registrado</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card card-glass p-3 d-flex flex-row align-items-center">
            <div class="bg-success bg-opacity-10 p-3 rounded-3 text-success me-3">
                <i class="bi bi-check-circle fs-3"></i>
            </div>
            <div>
                <h4 class="text-white mb-0"><?= htmlspecialchars((string)$countAtivo, ENT_QUOTES, 'UTF-8') ?></h4>
                <span class="text-secondary small">Em Operação</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card card-glass p-3 d-flex flex-row align-items-center">
            <div class="bg-warning bg-opacity-10 p-3 rounded-3 text-warning me-3">
                <i class="bi bi-cone-striped fs-3"></i>
            </div>
            <div>
                <h4 class="text-white mb-0"><?= htmlspecialchars((string)$countManutencao, ENT_QUOTES, 'UTF-8') ?></h4>
                <span class="text-secondary small">Em Manutenção</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card card-glass p-3 d-flex flex-row align-items-center">
            <div class="bg-danger bg-opacity-10 p-3 rounded-3 text-danger me-3">
                <i class="bi bi-x-circle fs-3"></i>
            </div>
            <div>
                <h4 class="text-white mb-0"><?= htmlspecialchars((string)$countInativo, ENT_QUOTES, 'UTF-8') ?></h4>
                <span class="text-secondary small">Inativos</span>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de Busca (Card Glass) -->
<div class="card card-glass border-glass p-4 mb-4">
    <h5 class="text-white mb-3"><i class="bi bi-funnel me-1 text-info"></i> Filtros de Pesquisa</h5>
    <form method="GET" action="index.php" class="row g-3">
        <input type="hidden" name="modulo" value="ativos">
        <input type="hidden" name="acao" value="listagem">
        
        <div class="col-12 col-md-3">
            <label for="patrimonio" class="form-label text-secondary small">Código do Patrimônio</label>
            <input type="text" name="patrimonio" id="patrimonio" class="form-control form-control-glass" 
                   value="<?= htmlspecialchars((string)($filtros['patrimonio'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex: NOTE-001">
        </div>
        
        <div class="col-12 col-md-3">
            <label for="id_categoria" class="form-label text-secondary small">Categoria</label>
            <select name="id_categoria" id="id_categoria" class="form-select form-select-glass">
                <option value="">Todas as Categorias</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= htmlspecialchars((string)$cat['id_categoria'], ENT_QUOTES, 'UTF-8') ?>" <?= (isset($filtros['id_categoria']) && $filtros['id_categoria'] == $cat['id_categoria']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string)$cat['descricao'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="col-12 col-md-3">
            <label for="status" class="form-label text-secondary small">Status do Ativo</label>
            <select name="status" id="status" class="form-select form-select-glass">
                <option value="">Todos os Status</option>
                <option value="Ativo" <?= (isset($filtros['status']) && $filtros['status'] === 'Ativo') ? 'selected' : '' ?>>Ativo</option>
                <option value="Inativo" <?= (isset($filtros['status']) && $filtros['status'] === 'Inativo') ? 'selected' : '' ?>>Inativo</option>
                <option value="Em Manutenção" <?= (isset($filtros['status']) && $filtros['status'] === 'Em Manutenção') ? 'selected' : '' ?>>Em Manutenção</option>
            </select>
        </div>
        
        <div class="col-12 col-md-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary-neon flex-grow-1 py-2">
                <i class="bi bi-search me-1"></i> Filtrar
            </button>
            <a href="?modulo=ativos&acao=listagem" class="btn btn-secondary-neon py-2 px-3" title="Limpar Filtros">
                <i class="bi bi-trash"></i>
            </a>
        </div>
    </form>
</div>

<!-- Listagem Tabela de Ativos -->
<div class="card card-glass border-glass p-4">
    <?php if (empty($ativos)): ?>
        <div class="text-center py-5">
            <i class="bi bi-search text-secondary fs-1 mb-3"></i>
            <h4 class="text-secondary">Nenhum ativo localizado</h4>
            <p class="text-secondary opacity-75 small">Tente ajustar seus termos de filtros de busca.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-glass align-middle w-100">
                <thead>
                    <tr>
                        <th style="width: 15%">Patrimônio</th>
                        <th style="width: 18%">Categoria</th>
                        <th style="width: 22%">Departamento</th>
                        <th style="width: 20%">Fornecedor</th>
                        <th style="width: 12%">Data Aquisição</th>
                        <th style="width: 13%">Status</th>
                        <th style="width: 15%" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ativos as $ativo): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars((string)$ativo['patrimonio'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)($ativo['categoria_nome'] ?? 'Sem Categoria'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)($ativo['departamento_nome'] ?? 'Sem Departamento'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)($ativo['fornecedor_nome'] ?? 'Sem Fornecedor'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="small text-secondary"><?= date('d/m/Y', strtotime($ativo['data_aquisicao'])) ?></td>
                            <td>
                                <?php
                                $statusClass = '';
                                if ($ativo['status'] === 'Ativo') $statusClass = 'badge-status-ativo';
                                elseif ($ativo['status'] === 'Inativo') $statusClass = 'badge-status-inativo';
                                elseif ($ativo['status'] === 'Em Manutenção') $statusClass = 'badge-status-manutencao';
                                ?>
                                <span class="badge-status <?= htmlspecialchars((string)$statusClass, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars((string)$ativo['status'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="?modulo=ativos&acao=editar&id=<?= htmlspecialchars((string)$ativo['id_ativo'], ENT_QUOTES, 'UTF-8') ?>" 
                                       class="btn btn-sm btn-secondary-neon text-info" title="Editar Ativo">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="?modulo=manutencao&acao=cadastro&id_ativo=<?= htmlspecialchars((string)$ativo['id_ativo'], ENT_QUOTES, 'UTF-8') ?>" 
                                       class="btn btn-sm btn-secondary-neon text-warning" title="Registrar Manutenção">
                                        <i class="bi bi-tools"></i>
                                    </a>
                                    <!-- Botão de exclusão seguro que dispara o modal -->
                                    <button type="button" 
                                            class="btn btn-sm btn-secondary-neon text-danger border-danger-subtle" 
                                            title="Excluir Ativo"
                                            onclick="confirmDelete(<?= htmlspecialchars((string)$ativo['id_ativo'], ENT_QUOTES, 'UTF-8') ?>, '<?= htmlspecialchars((string)$ativo['patrimonio'], ENT_QUOTES, 'UTF-8') ?>')">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de Confirmação de Exclusão com Design Premium -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-glass shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white" id="deleteModalLabel">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i> Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-light">
                <p>Você tem certeza que deseja remover o ativo <strong class="text-info" id="deleteAssetCode"></strong> do sistema?</p>
                <p class="small text-danger"><i class="bi bi-info-circle me-1"></i> Atenção: Esta ação não poderá ser desfeita e falhará se houverem registros de manutenção pendentes para este ativo.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary-neon" data-bs-dismiss="modal">Cancelar</button>
                <form action="?modulo=ativos&acao=excluir" method="POST" class="d-inline">
                    <!-- Token CSRF Obrigatório no modal de exclusão -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="id" id="deleteAssetId" value="">
                    <button type="submit" class="btn btn-danger px-4 border-0" style="background: var(--color-danger);">
                        <i class="bi bi-trash me-1"></i> Confirmar Exclusão
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let deleteModal = null;
function confirmDelete(id, patrimonio) {
    document.getElementById('deleteAssetId').value = id;
    document.getElementById('deleteAssetCode').innerText = patrimonio;
    
    if (!deleteModal) {
        deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    }
    deleteModal.show();
}
</script>

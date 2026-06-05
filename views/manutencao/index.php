<?php
$patrimonio = $filtros['patrimonio'] ?? '';
$ordem_custo = $filtros['ordem_custo'] ?? '';
$ordem_data = $filtros['ordem_data'] ?? '';
?>

<div class="row align-items-center mb-4">
    <div class="col-12 col-md-6">
        <h1 class="text-white mb-1"><i class="bi bi-tools me-2 text-info"></i>Manutenções</h1>
        <p class="text-secondary mb-0">Histórico financeiro e técnico de reparos em ativos</p>
    </div>
    <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
        <a href="?modulo=manutencao&acao=cadastro" class="btn btn-primary-neon px-4 py-2">
            <i class="bi bi-plus-lg me-1"></i> Nova Manutenção
        </a>
    </div>
</div>

<!-- Filtros de Busca (Card Glass) -->
<div class="card card-glass border-glass p-4 mb-4">
    <h5 class="text-white mb-3"><i class="bi bi-funnel me-1 text-info"></i> Filtros de Pesquisa</h5>
    <form method="GET" action="index.php" class="row g-3">
        <input type="hidden" name="modulo" value="manutencao">
        <input type="hidden" name="acao" value="listagem">
        
        <div class="col-12 col-md-4">
            <label for="patrimonio" class="form-label text-secondary small">Filtrar por Patrimônio</label>
            <input type="text" name="patrimonio" id="patrimonio" class="form-control form-control-glass" 
                   value="<?= htmlspecialchars((string)$patrimonio, ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex: NOT-001">
        </div>
        
        <div class="col-12 col-md-3">
            <label for="ordem_data" class="form-label text-secondary small">Ordenar por Data</label>
            <select name="ordem_data" id="ordem_data" class="form-select form-select-glass">
                <option value="">Padrão (Mais recentes)</option>
                <option value="DESC" <?= $ordem_data === 'DESC' ? 'selected' : '' ?>>Mais recentes primeiro</option>
                <option value="ASC" <?= $ordem_data === 'ASC' ? 'selected' : '' ?>>Mais antigas primeiro</option>
            </select>
        </div>
        
        <div class="col-12 col-md-3">
            <label for="ordem_custo" class="form-label text-secondary small">Ordenar por Custo</label>
            <select name="ordem_custo" id="ordem_custo" class="form-select form-select-glass">
                <option value="">Sem ordenação de custo</option>
                <option value="DESC" <?= $ordem_custo === 'DESC' ? 'selected' : '' ?>>Maior custo</option>
                <option value="ASC" <?= $ordem_custo === 'ASC' ? 'selected' : '' ?>>Menor custo</option>
            </select>
        </div>
        
        <div class="col-12 col-md-2 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary-neon flex-grow-1 py-2">
                <i class="bi bi-search me-1"></i> Filtrar
            </button>
            <a href="?modulo=manutencao&acao=listagem" class="btn btn-secondary-neon py-2 px-3" title="Limpar Filtros">
                <i class="bi bi-trash"></i>
            </a>
        </div>
    </form>
</div>

<!-- Listagem Tabela de Manutenções -->
<div class="card card-glass border-glass p-4">
    <?php if (empty($manutencoes)): ?>
        <div class="text-center py-5">
            <i class="bi bi-search text-secondary fs-1 mb-3"></i>
            <h4 class="text-secondary">Nenhuma manutenção localizada</h4>
            <p class="text-secondary opacity-75 small">Tente ajustar seus termos de filtros de busca.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-glass align-middle w-100">
                <thead>
                    <tr>
                        <th style="width: 20%">Patrimônio</th>
                        <th style="width: 15%">Data</th>
                        <th style="width: 15%">Custo (R$)</th>
                        <th style="width: 40%">Descrição</th>
                        <th style="width: 10%" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($manutencoes as $manutencao): ?>
                        <tr>
                            <td class="fw-bold text-white"><?= htmlspecialchars((string)$manutencao['patrimonio'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="small text-secondary"><?= date('d/m/Y H:i', strtotime($manutencao['data_reg'])) ?></td>
                            <td class="text-success fw-bold">R$ <?= number_format($manutencao['custo'], 2, ',', '.') ?></td>
                            <td class="text-secondary">
                                <?php
                                $descricao = htmlspecialchars($manutencao['descricao'], ENT_QUOTES, 'UTF-8');
                                echo strlen($descricao) > 50 ? substr($descricao, 0, 47) . '...' : $descricao;
                                ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="?modulo=manutencao&acao=show&id=<?= htmlspecialchars((string)$manutencao['id_manutencao'], ENT_QUOTES, 'UTF-8') ?>" 
                                       class="btn btn-sm btn-secondary-neon text-info" title="Ver Detalhes">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
// Calcular estatísticas rápidas
$custoTotalConsolidado = 0.00;
$totalManutencoes = 0;
$ativoMaisCaro = 'Nenhum';
$maiorCusto = 0.00;

if (!empty($custos)) {
    // Como a query já retorna ordenada por custo descrescente, o primeiro é o mais custoso
    $ativoMaisCaro = $custos[0]['patrimonio'];
    $maiorCusto = (float)$custos[0]['custo_total'];
    
    foreach ($custos as $c) {
        $custoTotalConsolidado += (float)$c['custo_total'];
        $totalManutencoes += (int)$c['qtd_manutencoes'];
    }
}
?>

<div class="row align-items-center mb-4">
    <div class="col-12 col-md-6">
        <h1 class="text-white mb-1"><i class="bi bi-bar-chart-line me-2 text-info"></i>Custos e Relatórios</h1>
        <p class="text-secondary mb-0">Consolidado financeiro de manutenções acumuladas por ativo de TI</p>
    </div>
    <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
        <a href="?modulo=relatorio&acao=exportar" class="btn btn-primary-neon px-4 py-2">
            <i class="bi bi-file-pdf me-1 text-danger-subtle fw-bold"></i> Exportar Relatório PDF
        </a>
    </div>
</div>

<!-- Grid de Métricas Financeiras -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card card-glass p-3 d-flex flex-row align-items-center">
            <div class="bg-danger bg-opacity-10 p-3 rounded-3 text-danger me-3">
                <i class="bi bi-cash-coin fs-3"></i>
            </div>
            <div>
                <h4 class="text-white mb-0">R$ <?= number_format($custoTotalConsolidado, 2, ',', '.') ?></h4>
                <span class="text-secondary small">Custo Acumulado Geral</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card card-glass p-3 d-flex flex-row align-items-center">
            <div class="bg-warning bg-opacity-10 p-3 rounded-3 text-warning me-3">
                <i class="bi bi-tools fs-3"></i>
            </div>
            <div>
                <h4 class="text-white mb-0"><?= $totalManutencoes ?></h4>
                <span class="text-secondary small">Total de Intervenções</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card card-glass p-3 d-flex flex-row align-items-center">
            <div class="bg-info bg-opacity-10 p-3 rounded-3 text-info me-3">
                <i class="bi bi-exclamation-octagon fs-3"></i>
            </div>
            <div>
                <h4 class="text-white mb-0"><?= htmlspecialchars($ativoMaisCaro) ?></h4>
                <span class="text-secondary small">Ativo Mais Custoso (R$ <?= number_format($maiorCusto, 2, ',', '.') ?>)</span>
            </div>
        </div>
    </div>
</div>

<!-- Painel Consolidado do Grid -->
<div class="card card-glass border-glass p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="text-white mb-0"><i class="bi bi-table me-1 text-info"></i> Detalhamento de Custos por Ativo</h5>
    </div>
    
    <?php if (empty($custos)): ?>
        <div class="text-center py-5">
            <i class="bi bi-bar-chart text-secondary fs-1 mb-3"></i>
            <h4 class="text-secondary">Nenhum dado financeiro disponível</h4>
            <p class="text-secondary opacity-75 small">Cadastre ativos e registre manutenções para habilitar o relatório.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-glass align-middle w-100">
                <thead>
                    <tr>
                        <th style="width: 25%">Patrimônio</th>
                        <th style="width: 35%">Departamento Alocado</th>
                        <th style="width: 20%" class="text-center">Quantidade de Manutenções</th>
                        <th style="width: 20%" class="text-end">Custo Total Acumulado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($custos as $linha): ?>
                        <tr>
                            <td class="fw-bold text-white"><?= htmlspecialchars($linha['patrimonio']) ?></td>
                            <td><?= htmlspecialchars($linha['departamento_nome']) ?></td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-1">
                                    <?= htmlspecialchars($linha['qtd_manutencoes']) ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold text-info">
                                R$ <?= number_format($linha['custo_total'], 2, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

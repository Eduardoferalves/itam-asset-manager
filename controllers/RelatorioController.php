<?php
/**
 * Controller de Relatórios e Exportações
 */
class RelatorioController {
    private $db;
    private $manutencaoModel;

    public function __construct() {
        // Bloqueio rígido direto no Controller
        if (!isset($_SESSION['usuario'])) {
            header("Location: ?modulo=auth&acao=login");
            exit;
        }

        $this->db = Conexao::getConexao();
        // Mantenha as instâncias dos models que já existem aí
        $this->manutencaoModel = new ManutencaoModel($this->db);
    }

    /**
     * Exibe o painel de relatórios com custos acumulados por ativo
     */
    public function index() {
        $custos = $this->manutencaoModel->obterCustosAgrupados();
        return [
            'view' => 'relatorio/index',
            'dados' => [
                'custos' => $custos
            ]
        ];
    }

    /**
     * Exporta o relatório consolidado de custos para PDF usando a FPDF
     * REGRA CRÍTICA DE BUFFER: Não inclui header.php nem footer.php e encerra estritamente com output/exit
     */
    public function exportar() {
        // Limpar qualquer buffer anterior para evitar corrupção de binários no PDF
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Importar FPDF 1.86 baixada de forma limpa na lib
        require_once 'lib/fpdf/fpdf.php';

        // Buscar dados do relatório
        $dados = $this->manutencaoModel->obterCustosAgrupados();

        // 1. Criar instância FPDF (Orientação P - Retrato, mm, Formato A4)
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();
        
        // 2. Cabeçalho do Documento (Design Minimalista e Limpo)
        $pdf->SetFont('Helvetica', 'B', 16);
        $pdf->SetTextColor(15, 23, 42); // Slate 900
        $pdf->Cell(190, 10, utf8_decode('SISTEMA ITAM - GESTÃO DE ATIVOS DE TI'), 0, 1, 'C');
        
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(100, 116, 139); // Slate 500
        $pdf->Cell(190, 6, utf8_decode('Relatório Consolidado de Custos de Manutenção por Ativo'), 0, 1, 'C');
        
        // Data de emissão
        $dataEmissao = date('d/m/Y H:i:s');
        $pdf->Cell(190, 6, utf8_decode("Emitido em: {$dataEmissao}"), 0, 1, 'C');
        
        $pdf->Ln(8);
        
        // 3. Linha divisória estética
        $pdf->SetDrawColor(226, 232, 240); // Slate 200
        $pdf->SetLineWidth(0.5);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(6);

        // 4. Cabeçalho da Tabela
        // Dimensões Obrigatórias: Patrimônio (40mm), Departamento (60mm), Qtd Manutenções (40mm), Custo Total (50mm, Alinhamento R)
        $pdf->SetFillColor(241, 245, 249); // Slate 100
        $pdf->SetTextColor(51, 65, 85); // Slate 700
        $pdf->SetDrawColor(203, 213, 225); // Slate 300
        $pdf->SetLineWidth(0.2);
        
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(40, 9, utf8_decode(' Patrimônio'), 1, 0, 'L', true);
        $pdf->Cell(60, 9, utf8_decode(' Departamento'), 1, 0, 'L', true);
        $pdf->Cell(40, 9, utf8_decode(' Qtd. Manutenções'), 1, 0, 'C', true);
        $pdf->Cell(50, 9, utf8_decode('Custo Total '), 1, 1, 'R', true);

        // 5. Linhas da Tabela
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(15, 23, 42); // Slate 900
        
        $totalGeralCustos = 0.00;
        $totalGeralManutencoes = 0;
        $fill = false; // Alternância de cores de linha
        
        foreach ($dados as $linha) {
            $totalGeralCustos += (float)$linha['custo_total'];
            $totalGeralManutencoes += (int)$linha['qtd_manutencoes'];
            
            // Formatando valores para exibição no PDF
            $patrimonio = ' ' . $linha['patrimonio'];
            $departamento = ' ' . $linha['departamento_nome'];
            $qtd = $linha['qtd_manutencoes'];
            $custo = 'R$ ' . number_format($linha['custo_total'], 2, ',', '.') . ' ';
            
            // Alternar background leve
            $pdf->SetFillColor(248, 250, 252); // Slate 50
            
            $pdf->Cell(40, 8, utf8_decode($patrimonio), 1, 0, 'L', $fill);
            $pdf->Cell(60, 8, utf8_decode($departamento), 1, 0, 'L', $fill);
            $pdf->Cell(40, 8, $qtd, 1, 0, 'C', $fill);
            $pdf->Cell(50, 8, utf8_decode($custo), 1, 1, 'R', $fill);
            
            $fill = !$fill;
        }

        // 6. Linha de Totais Consolidados
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetFillColor(226, 232, 240); // Slate 200
        $pdf->Cell(100, 9, utf8_decode(' TOTAL CONSOLIDADO'), 1, 0, 'L', true);
        $pdf->Cell(40, 9, $totalGeralManutencoes, 1, 0, 'C', true);
        
        $totalCustoFormatado = 'R$ ' . number_format($totalGeralCustos, 2, ',', '.') . ' ';
        $pdf->Cell(50, 9, utf8_decode($totalCustoFormatado), 1, 1, 'R', true);

        // 7. Rodapé de Assinatura e Controle
        $pdf->Ln(15);
        $pdf->SetFont('Helvetica', 'I', 8);
        $pdf->SetTextColor(148, 163, 184); // Slate 400
        $pdf->Cell(190, 4, utf8_decode('Documento gerado automaticamente pelo Sistema de Gestão de Ativos ITAM.'), 0, 1, 'C');
        $pdf->Cell(190, 4, utf8_decode('Padrão MVC Customizado (Vanilla PHP) | Banco de Dados MySQL (PDO)'), 0, 1, 'C');

        // 8. Encerramento Estrito conforme Regra Crítica
        $pdf->Output('relatorio.pdf', 'D');
        exit;
    }
}

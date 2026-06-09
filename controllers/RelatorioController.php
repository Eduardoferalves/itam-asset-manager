<?php
/**
 * Controller de Relatórios e Exportações (Camada de Controle - MVC).
 * 
 * [ARQUITETURA] Especializado na extração de dados tabulares e na interoperabilidade
 * de conversão visual de tabelas consolidadas para relatórios fixos em binário (PDF).
 */
class RelatorioController {
    /** @var PDO Conexão singleton com o banco. */
    private $db;
    /** @var ManutencaoModel Dependência para o processamento de consolidação matemática. */
    private $manutencaoModel;

    public function __construct() {
        // [SEGURANÇA] Evita exposição do balancete financeiro / relatórios de TI
        // para eventuais usuários deslogados / convidados não autorizados.
        if (!isset($_SESSION['usuario'])) {
            header("Location: ?modulo=auth&acao=login");
            exit;
        }

        $this->db = Conexao::getConexao();
        $this->manutencaoModel = new ManutencaoModel($this->db);
    }

    /**
     * Interface matriz do portal analítico para visualizar os KPI de custos de inventário.
     * 
     * @return array Assinatura contendo o diretório da exibição com o recordset aninhado.
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
     * Compila em tempo de execução um documento PDF da visão global formatada utilizando a lib FPDF.
     * 
     * [ARQUITETURA] Interrompe o processo natural do sistema, evitando renderizações HTML convencionais
     * para permitir o transbordo estrito de pacotes header PDF puro para o navegador do cliente.
     */
    public function exportar() {
        // [REGRA DE NEGÓCIO] Expurgar previamente buffers abertos durante inicializações do PHP.
        // O envio indesejado do "BOM" ou quebras de linhas acidentais em cabeçalhos
        // corromperia definitivamente o parse do binário, inutilizando a saída final gerada para download.
        if (ob_get_level()) {
            ob_end_clean();
        }

        require_once 'lib/fpdf/fpdf.php';

        $dados = $this->manutencaoModel->obterCustosAgrupados();

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();
        
        $pdf->SetFont('Helvetica', 'B', 16);
        $pdf->SetTextColor(15, 23, 42); 
        $pdf->Cell(190, 10, utf8_decode('SISTEMA ITAM - GESTÃO DE ATIVOS DE TI'), 0, 1, 'C');
        
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(100, 116, 139); 
        $pdf->Cell(190, 6, utf8_decode('Relatório Consolidado de Custos de Manutenção por Ativo'), 0, 1, 'C');
        
        $dataEmissao = date('d/m/Y H:i:s');
        $pdf->Cell(190, 6, utf8_decode("Emitido em: {$dataEmissao}"), 0, 1, 'C');
        
        $pdf->Ln(8);
        
        $pdf->SetDrawColor(226, 232, 240); 
        $pdf->SetLineWidth(0.5);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(6);

        $pdf->SetFillColor(241, 245, 249); 
        $pdf->SetTextColor(51, 65, 85); 
        $pdf->SetDrawColor(203, 213, 225); 
        $pdf->SetLineWidth(0.2);
        
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(40, 9, utf8_decode(' Patrimônio'), 1, 0, 'L', true);
        $pdf->Cell(60, 9, utf8_decode(' Departamento'), 1, 0, 'L', true);
        $pdf->Cell(40, 9, utf8_decode(' Qtd. Manutenções'), 1, 0, 'C', true);
        $pdf->Cell(50, 9, utf8_decode('Custo Total '), 1, 1, 'R', true);

        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(15, 23, 42); 
        
        $totalGeralCustos = 0.00;
        $totalGeralManutencoes = 0;
        $fill = false; 
        
        foreach ($dados as $linha) {
            $totalGeralCustos += (float)$linha['custo_total'];
            $totalGeralManutencoes += (int)$linha['qtd_manutencoes'];
            
            $patrimonio = ' ' . $linha['patrimonio'];
            $departamento = ' ' . $linha['departamento_nome'];
            $qtd = $linha['qtd_manutencoes'];
            $custo = 'R$ ' . number_format($linha['custo_total'], 2, ',', '.') . ' ';
            
            $pdf->SetFillColor(248, 250, 252); 
            
            $pdf->Cell(40, 8, utf8_decode($patrimonio), 1, 0, 'L', $fill);
            $pdf->Cell(60, 8, utf8_decode($departamento), 1, 0, 'L', $fill);
            $pdf->Cell(40, 8, $qtd, 1, 0, 'C', $fill);
            $pdf->Cell(50, 8, utf8_decode($custo), 1, 1, 'R', $fill);
            
            $fill = !$fill;
        }

        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetFillColor(226, 232, 240); 
        $pdf->Cell(100, 9, utf8_decode(' TOTAL CONSOLIDADO'), 1, 0, 'L', true);
        $pdf->Cell(40, 9, $totalGeralManutencoes, 1, 0, 'C', true);
        
        $totalCustoFormatado = 'R$ ' . number_format($totalGeralCustos, 2, ',', '.') . ' ';
        $pdf->Cell(50, 9, utf8_decode($totalCustoFormatado), 1, 1, 'R', true);

        $pdf->Ln(15);
        $pdf->SetFont('Helvetica', 'I', 8);
        $pdf->SetTextColor(148, 163, 184); 
        $pdf->Cell(190, 4, utf8_decode('Documento gerado automaticamente pelo Sistema de Gestão de Ativos ITAM.'), 0, 1, 'C');
        $pdf->Cell(190, 4, utf8_decode('Padrão MVC Customizado (Vanilla PHP) | Banco de Dados MySQL (PDO)'), 0, 1, 'C');

        // [REGRA DE NEGÓCIO] Finalização terminal da esteira lógica de saída.
        // A propriedade exit em conjunto com a injeção do FPDF impossibilita o carregamento dos layout.php no index.php.
        $pdf->Output('relatorio.pdf', 'D');
        exit;
    }
}

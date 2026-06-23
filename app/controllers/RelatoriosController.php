<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../config/conexao.php';

class RelatoriosController extends BaseController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexao::getConexao();
    }

    public function index(): void
    {
        // Bloqueia acesso sem login
        if (!isset($_SESSION['user']) || !isset($_SESSION['proprietario_id'])) {
            $this->redirect('login');
        }

        $modulos       = $this->getModulos();
        $modulosParaJS = $this->modulosParaJS($modulos);
        require_once __DIR__ . '/../views/relatorios.php';
    }

    public function export(): void
    {
        if (!isset($_SESSION['user']) || !isset($_SESSION['proprietario_id'])) {
            $this->respondErro('json', 'Usuário não autenticado.');
            return;
        }

        $modulo  = $_POST['modulo']  ?? '';
        $colunas = $_POST['colunas'] ?? [];
        $formato = $_POST['formato'] ?? 'csv';

        $modulos = $this->getModulos();

        if (!isset($modulos[$modulo]) || empty($colunas) || !is_array($colunas)) {
            $this->respondErro($formato, 'Parâmetros de exportação inválidos.');
            return;
        }

        $def = $modulos[$modulo];

        $colunasValidas = [];
        foreach ($colunas as $col) {
            if (isset($def['colunas'][$col])) {
                $colunasValidas[$col] = $def['colunas'][$col];
            }
        }

        if (empty($colunasValidas)) {
            $this->respondErro($formato, 'Nenhuma coluna válida selecionada.');
            return;
        }

        $data = $this->fetchData($def);

        if ($formato === 'json') {
            $result = [];
            foreach ($data as $row) {
                $filteredRow = [];
                foreach (array_keys($colunasValidas) as $col) {
                    $filteredRow[$col] = $row[$col] ?? '';
                }
                $result[] = $filteredRow;
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['colunas' => $colunasValidas, 'dados' => $result], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $filename = 'relatorio_' . $modulo . '_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF"); // BOM para Excel reconhecer UTF-8

        fputcsv($out, array_values($colunasValidas), ';');

        foreach ($data as $row) {
            $line = [];
            foreach (array_keys($colunasValidas) as $col) {
                $line[] = $row[$col] ?? '';
            }
            fputcsv($out, $line, ';');
        }

        fclose($out);
        exit;
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function respondErro(string $formato, string $msg): void
    {
        if ($formato === 'json') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => $msg]);
            exit;
        }
        $this->setToast('error', 'Erro', $msg);
        $this->redirect('relatorios');
    }

    private function modulosParaJS(array $modulos): array
    {
        $out = [];
        foreach ($modulos as $key => $m) {
            $out[$key] = [
                'label'    => $m['label'],
                'descricao'=> $m['descricao'],
                'icone'    => $m['icone'],
                'cor'      => $m['cor'],
                'colunas'  => $m['colunas'],
            ];
        }
        return $out;
    }

    private function fetchData(array $def): array
    {
        $stmt = $this->db->prepare($def['query']);
        if (!empty($def['params'])) {
            $stmt->bind_param($def['types'], ...$def['params']);
        }
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    private function getModulos(): array
    {
        // Puxa direto da sessão, sem query extra
        $pid  = (int) $_SESSION['proprietario_id'];
        $tipo = $_SESSION['tipo'] ?? 'peao';

        $modulos = [
            'propriedades' => [
                'label'    => 'Propriedades',
                'descricao'=> 'Propriedades rurais cadastradas',
                'icone'    => 'casa',
                'cor'      => 'verde',
                'colunas'  => [
                    'nome'           => 'Nome',
                    'localizacao'    => 'Localização',
                    'municipio'      => 'Município',
                    'estado'         => 'Estado',
                    'area_total'     => 'Área Total (ha)',
                    'area_produtiva' => 'Área Produtiva (ha)',
                ],
                'query'  => 'SELECT nome, localizacao, municipio, estado, area_total, area_produtiva
                             FROM propriedade WHERE proprietario_id = ?',
                'params' => [$pid],
                'types'  => 'i',
            ],
            'talhoes' => [
                'label'    => 'Talhões',
                'descricao'=> 'Talhões vinculados às propriedades',
                'icone'    => 'campo',
                'cor'      => 'dourado',
                'colunas'  => [
                    'talhao'                   => 'Talhão',
                    'propriedade'              => 'Propriedade',
                    'area_hectare'             => 'Área (ha)',
                    'status'                   => 'Status',
                    'tipo_posse'               => 'Tipo de Posse',
                    'custo_arrendamento_sacas' => 'Custo Arrendamento (sc)',
                ],
                'query'  => 'SELECT t.nome AS talhao, pr.nome AS propriedade,
                                    t.area_hectare, t.status, t.tipo_posse,
                                    t.custo_arrendamento_sacas
                             FROM talhoes t
                             JOIN propriedade pr ON t.propriedade_id = pr.id
                             WHERE pr.proprietario_id = ?',
                'params' => [$pid],
                'types'  => 'i',
            ],
            'insumos' => [
                'label'    => 'Insumos',
                'descricao'=> 'Produtos e insumos cadastrados',
                'icone'    => 'insumo',
                'cor'      => 'azul',
                'colunas'  => [
                    'nome'            => 'Nome',
                    'marca'           => 'Marca',
                    'tipo'            => 'Tipo',
                    'unidade_medida'  => 'Unidade',
                    'valor_por_dose'  => 'Valor por Dose (R$)',
                    'data_referencia' => 'Data Referência',
                ],
                'query'  => 'SELECT p.nome, p.marca, p.tipo, p.unidade_medida,
                                    i.valor_por_dose, i.data_referencia
                             FROM insumo i
                             JOIN produto p ON i.produto_id = p.id',
                'params' => [],
                'types'  => '',
            ],
            'estoque_insumos' => [
                'label'    => 'Estoque de Insumos',
                'descricao'=> 'Estoque de insumos por propriedade',
                'icone'    => 'estoque',
                'cor'      => 'laranja',
                'colunas'  => [
                    'propriedade' => 'Propriedade',
                    'insumo'      => 'Insumo',
                    'tipo'        => 'Tipo',
                    'quantidade'  => 'Quantidade',
                    'unidade'     => 'Unidade',
                ],
                'query'  => 'SELECT pr.nome AS propriedade, p.nome AS insumo, p.tipo,
                                    ei.quantidade, p.unidade_medida AS unidade
                             FROM estoque_insumos ei
                             JOIN insumo i        ON ei.insumo_id      = i.id
                             JOIN produto p       ON i.produto_id      = p.id
                             JOIN propriedade pr  ON ei.propriedade_id = pr.id
                             WHERE pr.proprietario_id = ?',
                'params' => [$pid],
                'types'  => 'i',
            ],
            'silos' => [
                'label'    => 'Silos',
                'descricao'=> 'Silos de armazenagem cadastrados',
                'icone'    => 'silo',
                'cor'      => 'roxo',
                'colunas'  => [
                    'nome'          => 'Silo',
                    'propriedade'   => 'Propriedade',
                    'cultura'       => 'Cultura',
                    'quantidade_kg' => 'Qtd. Armazenada (kg)',
                    'capacidade_kg' => 'Capacidade (kg)',
                ],
                'query'  => 'SELECT s.nome, pr.nome AS propriedade, s.cultura,
                                    s.quantidade_kg, s.capacidade_kg
                             FROM silo s
                             JOIN propriedade pr ON s.propriedade_id = pr.id
                             WHERE pr.proprietario_id = ?',
                'params' => [$pid],
                'types'  => 'i',
            ],
            'safras' => [
                'label'    => 'Safras',
                'descricao'=> 'Safras registradas por talhão',
                'icone'    => 'safra',
                'cor'      => 'verde',
                'colunas'  => [
                    'talhao'      => 'Talhão',
                    'propriedade' => 'Propriedade',
                    'cultura'     => 'Cultura',
                    'variedade'   => 'Variedade',
                    'data_inicio' => 'Data Início',
                    'data_fim'    => 'Data Fim',
                ],
                'query'  => 'SELECT t.nome AS talhao, pr.nome AS propriedade,
                                    c.nome AS cultura, c.variedade,
                                    s.data_inicio, s.data_fim
                             FROM safra s
                             JOIN talhoes t       ON s.talhao_id       = t.id
                             JOIN propriedade pr  ON t.propriedade_id  = pr.id
                             JOIN cultura c       ON s.cultura_id      = c.id
                             WHERE pr.proprietario_id = ?',
                'params' => [$pid],
                'types'  => 'i',
            ],
            'culturas' => [
                'label'    => 'Culturas',
                'descricao'=> 'Culturas e variedades cadastradas',
                'icone'    => 'planta',
                'cor'      => 'verde',
                'colunas'  => [
                    'nome'      => 'Nome',
                    'variedade' => 'Variedade',
                ],
                'query'  => 'SELECT nome, variedade FROM cultura',
                'params' => [],
                'types'  => '',
            ],
            'produtos' => [
                'label'    => 'Produtos',
                'descricao'=> 'Produtos cadastrados no sistema',
                'icone'    => 'produto',
                'cor'      => 'azul',
                'colunas'  => [
                    'nome'           => 'Nome',
                    'marca'          => 'Marca',
                    'tipo'           => 'Tipo',
                    'unidade_medida' => 'Unidade',
                    'descricao'      => 'Descrição',
                ],
                'query'  => 'SELECT nome, marca, tipo, unidade_medida, descricao FROM produto',
                'params' => [],
                'types'  => '',
            ],
            'operacoes_agricolas' => [
                'label'    => 'Operações Agrícolas',
                'descricao'=> 'Operações de campo registradas',
                'icone'    => 'operacao',
                'cor'      => 'dourado',
                'colunas'  => [
                    'tipo_operacao'     => 'Tipo',
                    'data_operacao'     => 'Data',
                    'descricao'         => 'Descrição',
                    'insumo'            => 'Insumo',
                    'talhao'            => 'Talhão',
                    'peao'              => 'Responsável',
                    'quantidade_insumo' => 'Qtd. Insumo',
                    'custos_operacao'   => 'Custo (R$)',
                ],
                'query'  => 'SELECT oa.tipo_operacao, oa.data_operacao, oa.descricao,
                                    p.nome AS insumo, t.nome AS talhao,
                                    pe.nome AS peao,
                                    oa.quantidade_insumo, oa.custos_operacao
                             FROM operacoes_agricolas oa
                             LEFT JOIN insumo i     ON oa.insumo_id = i.id
                             LEFT JOIN produto p    ON i.produto_id = p.id
                             LEFT JOIN peao pe      ON oa.peao_id   = pe.id
                             JOIN talhoes t         ON oa.talhao_id = t.id
                             JOIN propriedade pr    ON t.propriedade_id = pr.id
                             WHERE pr.proprietario_id = ?',
                'params' => [$pid],
                'types'  => 'i',
            ],
            'operacoes_financeiras' => [
                'label'    => 'Operações Financeiras',
                'descricao'=> 'Compras e vendas registradas',
                'icone'    => 'financeiro',
                'cor'      => 'azul',
                'colunas'  => [
                    'tipo_operacao'  => 'Tipo',
                    'data'           => 'Data',
                    'produto'        => 'Produto',
                    'quantidade'     => 'Quantidade',
                    'valor_operacao' => 'Valor (R$)',
                    'descricao'      => 'Descrição',
                ],
                // Ajustado de 'opf.data' para 'opf.data_operacao AS data'
                'query'  => 'SELECT opf.tipo_operacao, opf.data_operacao AS data, p.nome AS produto,
                                    opf.quantidade, opf.valor_operacao, opf.descricao
                             FROM operacoes_financeiras opf
                             LEFT JOIN produto p  ON opf.produto_id = p.id
                             JOIN safra s         ON opf.safra_id   = s.id
                             JOIN talhoes t       ON s.talhao_id    = t.id
                             JOIN propriedade pr  ON t.propriedade_id = pr.id
                             WHERE pr.proprietario_id = ?',
                'params' => [$pid],
                'types'  => 'i',
            ],
        ];

        if ($tipo === 'proprietario') {
            $modulos['equipe'] = [
                'label'    => 'Equipe',
                'descricao'=> 'Membros da equipe de campo',
                'icone'    => 'equipe',
                'cor'      => 'laranja',
                'colunas'  => [
                    'nome'     => 'Nome',
                    'cpf_cnpj' => 'CPF',
                    'telefone' => 'Telefone',
                    'email'    => 'E-mail',
                ],
                'query'  => 'SELECT nome, cpf_cnpj, telefone, email
                             FROM peao WHERE proprietario_id = ?',
                'params' => [$pid],
                'types'  => 'i',
            ];
        }

        return $modulos;
    }
}
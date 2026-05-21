<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../config/conexao.php';

class ClimaController extends BaseController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexao::getConexao();
    }

    public function index(): void
    {
        $user = $_SESSION['user'];
        $tipo = $_SESSION['tipo'];

        $h        = (int) date('H');
        $saudacao = $h < 12 ? 'Bom dia' : ($h < 18 ? 'Boa tarde' : 'Boa noite');

        $iniciais = strtoupper(substr($user['nome'], 0, 1));
        if (str_contains($user['nome'], ' ')) {
            $partes   = explode(' ', $user['nome']);
            $iniciais = strtoupper($partes[0][0] . end($partes)[0]);
        }

        $pagina_atual = 'clima';

        $toast = null;
        if (isset($_SESSION['toast'])) {
            $toast = $_SESSION['toast'];
            unset($_SESSION['toast']);
        }

        $propriedades        = $this->getPropList();
        $propId              = (int) ($_GET['prop'] ?? 0);
        $propriedade         = $this->resolverProp($propriedades, $propId);
        $clima               = $propriedade ? $this->fetchClima($propriedade['municipio']) : null;

        require_once __DIR__ . '/../views/clima.php';
    }

    // Endpoint JSON consumido pelo widget do dashboard — não expõe a chave
    public function weather(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $propriedades = $this->getPropList();
        $propId       = (int) ($_GET['prop'] ?? 0);
        $prop         = $this->resolverProp($propriedades, $propId);

        if (!$prop) {
            echo json_encode(['error' => 'Nenhuma propriedade cadastrada']);
            return;
        }

        $data = $this->fetchClima($prop['municipio']);
        echo $data
            ? json_encode($data)
            : json_encode(['error' => 'Não foi possível obter dados climáticos']);
    }

    private function fetchClima(string $municipio): ?array
    {
        $apiKey = $this->getApiKey();
        if (!$apiKey) return null;

        $url = sprintf(
            'https://api.openweathermap.org/data/2.5/weather?q=%s,BR&appid=%s&lang=pt_br&units=metric',
            urlencode($municipio),
            urlencode($apiKey)
        );

        $ctx  = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true]]);
        $resp = @file_get_contents($url, false, $ctx);
        if ($resp === false) return null;

        $data = json_decode($resp, true);
        return (is_array($data) && ($data['cod'] ?? 0) === 200) ? $data : null;
    }

    private function getPropList(): array
    {
        $pid  = $this->getProprietarioId();
        $stmt = $this->db->prepare(
            'SELECT id, nome, municipio, estado FROM propriedade WHERE proprietario_id = ? ORDER BY nome'
        );
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    // Retorna a propriedade do $id se pertencer ao usuário, senão a primeira da lista
    private function resolverProp(array $lista, int $id): ?array
    {
        if ($id > 0) {
            foreach ($lista as $p) {
                if ((int) $p['id'] === $id) return $p;
            }
        }
        return $lista[0] ?? null;
    }

    private function getProprietarioId(): int
    {
        if (($_SESSION['tipo'] ?? '') === 'proprietario') {
            return (int) $_SESSION['user']['id'];
        }
        $stmt = $this->db->prepare('SELECT proprietario_id FROM peao WHERE id = ?');
        $uid  = (int) $_SESSION['user']['id'];
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $row  = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int) ($row['proprietario_id'] ?? 0);
    }

    // .env tem formato incomum: "APICLIMA_KEY: = valor" — rtrim remove o ":" que sobra após trim()
    private function getApiKey(): string
    {
        foreach ($_ENV as $k => $v) {
            if (rtrim(trim($k), ':') === 'APICLIMA_KEY') {
                return trim($v);
            }
        }
        return '';
    }
}

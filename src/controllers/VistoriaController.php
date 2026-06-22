<?php

declare(strict_types=1);

require_once RAIZ . '/src/repository/VistoriaRepository.php';
require_once RAIZ . '/src/repository/UsuarioRepository.php';
require_once RAIZ . '/src/repository/UnidadeRepository.php';

class VistoriaController
{
    private VistoriaRepository $repo;
    private UsuarioRepository  $usuarioRepo;
    private UnidadeRepository  $unidadeRepo;

    public function __construct()
    {
        $pdo               = Conexao::obter();
        $this->repo        = new VistoriaRepository($pdo);
        $this->usuarioRepo = new UsuarioRepository($pdo);
        $this->unidadeRepo = new UnidadeRepository($pdo);
    }

    public function listar(): void
    {
        $tipoPesquisa    = $_GET['tipo']   ?? null;
        $statusPesquisa  = $_GET['status'] ?? null;
        $vistorias       = $this->repo->listarTodas($tipoPesquisa, $statusPesquisa);
        $validadesProximas = $this->repo->listarValidadesProximas();
        $mensagem        = Sessao::lerFlash('sucesso');
        $erroMensagem    = Sessao::lerFlash('erro');
        require_once RAIZ . '/views/admin/vistorias/lista.php';
    }

    public function formulario(): void
    {
        $vistoria     = null;
        $responsaveis = array_merge(
            $this->usuarioRepo->listarPorPerfil('sindico'),
            $this->usuarioRepo->listarPorPerfil('subsindico'),
        );
        $unidades     = $this->unidadeRepo->listarAtivas();
        $prestadoras  = $this->carregarPrestadoras();

        if (!empty($_GET['id'])) {
            $vistoria = $this->repo->buscarPorId((int) $_GET['id']);
        }

        require_once RAIZ . '/views/admin/vistorias/formulario.php';
    }

    public function salvar(): void
    {
        $id = $this->repo->salvar($_POST);
        Sessao::flash('sucesso', 'Vistoria salva com sucesso.');
        Roteador::redirecionar("vistorias/{$id}");
    }

    public function ver(): void
    {
        $id       = (int) ($_GET['id'] ?? 0);
        $vistoria = $this->repo->buscarPorId($id);
        if (!$vistoria) { Roteador::redirecionar('vistorias'); return; }

        $mensagem     = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');
        require_once RAIZ . '/views/admin/vistorias/detalhe.php';
    }

    public function excluir(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $this->repo->excluir($id);
        Sessao::flash('sucesso', 'Vistoria removida.');
        Roteador::redirecionar('vistorias');
    }

    public function adicionarAnexo(): void
    {
        $vistoriaId = (int) ($_POST['vistoria_id'] ?? 0);
        $tipo       = $_POST['tipo_anexo'] ?? 'documento';

        if (empty($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
            Sessao::flash('erro', 'Selecione um arquivo válido.');
            Roteador::redirecionar("vistorias/{$vistoriaId}");
            return;
        }

        try {
            $caminho      = $this->salvarArquivo($tipo, $_FILES['arquivo']);
            $nomeOriginal = basename($_FILES['arquivo']['name']);
            $this->repo->salvarAnexo($vistoriaId, $tipo, $caminho, $nomeOriginal);
            Sessao::flash('sucesso', 'Documento anexado com sucesso.');
        } catch (RuntimeException|InvalidArgumentException $e) {
            Sessao::flash('erro', $e->getMessage());
        }

        Roteador::redirecionar("vistorias/{$vistoriaId}");
    }

    public function removerAnexo(): void
    {
        $anexoId    = (int) ($_GET['anexo_id']    ?? 0);
        $vistoriaId = (int) ($_GET['vistoria_id'] ?? 0);

        $caminho = $this->repo->removerAnexo($anexoId);
        if ($caminho) {
            $config = require RAIZ . '/config/app.php';
            $f = $config['pasta_uploads'] . '/' . $caminho;
            if (file_exists($f)) unlink($f);
        }

        Sessao::flash('sucesso', 'Anexo removido.');
        Roteador::redirecionar("vistorias/{$vistoriaId}");
    }

    private function salvarArquivo(string $tipo, array $arquivo): string
    {
        $config    = require RAIZ . '/config/app.php';
        $extensao  = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $permitidas = array_merge(
            $config['extensoes_imagem'],
            $config['extensoes_documento'],
        );

        if (!in_array($extensao, $permitidas, true)) {
            throw new InvalidArgumentException("Extensão .{$extensao} não permitida.");
        }
        if ($arquivo['size'] > $config['tamanho_maximo_upload']) {
            throw new InvalidArgumentException('Arquivo muito grande. Máximo 10 MB.');
        }

        $subpasta    = 'vistorias/' . $tipo . 's';
        $nomeArquivo = $tipo . '_' . uniqid('', true) . '.' . $extensao;
        $destino     = $config['pasta_uploads'] . '/' . $subpasta . '/' . $nomeArquivo;

        if (!is_dir(dirname($destino))) {
            mkdir(dirname($destino), 0755, true);
        }
        if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
            throw new RuntimeException('Falha ao salvar o arquivo.');
        }

        return $subpasta . '/' . $nomeArquivo;
    }

    private function carregarPrestadoras(): array
    {
        try {
            $stmt = Conexao::obter()->query('SELECT id, nome FROM prestadoras ORDER BY nome');
            return $stmt->fetchAll();
        } catch (\PDOException) {
            return [];
        }
    }
}

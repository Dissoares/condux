<?php

declare(strict_types=1);

require_once RAIZ . '/src/repository/ComunicadoRepository.php';
require_once RAIZ . '/src/repository/PushSubscriptionRepository.php';
require_once RAIZ . '/src/services/PushNotificationService.php';

class ComunicadoController
{
    private ComunicadoRepository    $repo;
    private PushNotificationService $push;
    private bool $ehAdmin;

    public function __construct()
    {
        $conexao       = Conexao::obter();
        $this->repo    = new ComunicadoRepository($conexao);
        $this->push    = new PushNotificationService(new PushSubscriptionRepository($conexao));
        $this->ehAdmin = in_array(Sessao::perfilAtual(), ['sindico', 'subsindico'], true);
    }

    public function listarAdmin(): void
    {
        $this->exigirAdmin();
        $comunicados  = $this->repo->listarTodos();
        $mensagem     = Sessao::lerFlash('sucesso');
        $erroMensagem = Sessao::lerFlash('erro');
        require RAIZ . '/views/admin/comunicados/lista.php';
    }

    public function formulario(): void
    {
        $this->exigirAdmin();
        $id          = (int) ($_GET['id'] ?? 0);
        $comunicado  = $id > 0 ? $this->repo->buscarPorId($id) : null;
        $erroMensagem = Sessao::lerFlash('erro');
        require RAIZ . '/views/admin/comunicados/formulario.php';
    }

    public function salvar(): void
    {
        $this->exigirAdmin();
        $id     = (int) ($_POST['id'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');

        if ($titulo === '') {
            Sessao::flash('erro', 'Título é obrigatório.');
            Roteador::redirecionar($id > 0 ? "/comunicados/{$id}/editar" : '/comunicados/novo');
        }

        $comunicado = new Comunicado(
            id:             $id > 0 ? $id : null,
            titulo:         $titulo,
            conteudo:       trim($_POST['conteudo'] ?? ''),
            tipo:           trim($_POST['tipo']     ?? 'aviso'),
            publicadoPor:   $id > 0 ? null : Sessao::usuarioAtual()['id'],
            dataPublicacao: trim($_POST['data_publicacao'] ?? date('Y-m-d')),
            dataExpiracao:  trim($_POST['data_expiracao']  ?? '') ?: null,
            ativo:          isset($_POST['ativo']),
        );

        $this->repo->salvar($comunicado);

        // Dispara push apenas ao criar novo comunicado ativo com data de hoje ou passada
        if ($id === 0 && $comunicado->ativo && $comunicado->dataPublicacao <= date('Y-m-d')) {
            $this->push->enviarParaTodos([
                'title' => 'Condux — ' . $comunicado->rotulo(),
                'body'  => $comunicado->titulo,
                'url'   => '/comunicados',
                'tag'   => 'comunicado',
            ]);
        }

        Sessao::flash('sucesso', $id > 0 ? 'Comunicado atualizado.' : 'Comunicado publicado.');
        Roteador::redirecionar('/comunicados');
    }

    public function excluir(): void
    {
        $this->exigirAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $this->repo->excluir($id);
        Sessao::flash('sucesso', 'Comunicado removido.');
        Roteador::redirecionar('/comunicados');
    }

    /** Página de comunicados para o morador */
    public function listarMorador(): void
    {
        $comunicados = $this->repo->listarAtivos();
        $tituloPagina = 'Comunicados';
        require RAIZ . '/views/morador/comunicados.php';
    }

    private function exigirAdmin(): void
    {
        if (!$this->ehAdmin) {
            http_response_code(403);
            echo 'Acesso não autorizado.';
            exit;
        }
    }
}

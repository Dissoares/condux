<?php

declare(strict_types=1);

require_once RAIZ . '/src/repository/TicketRepository.php';
require_once RAIZ . '/src/repository/UsuarioRepository.php';
require_once RAIZ . '/src/services/EmailService.php';

class TicketController
{
    private TicketRepository  $repo;
    private bool              $ehAdmin;
    private array             $usuario;

    public function __construct()
    {
        $this->usuario  = Sessao::usuarioAtual();
        $this->ehAdmin  = in_array($this->usuario['perfil'] ?? '', ['sindico', 'subsindico'], true);
        $this->repo     = new TicketRepository(Conexao::obter());
    }

    /* ── Admin: lista todos ─────────────────────────────── */
    public function listarAdmin(): void
    {
        $status    = $_GET['status']    ?? '';
        $categoria = $_GET['categoria'] ?? '';
        $tickets   = $this->repo->listarTodos($status, $categoria);
        $mensagem  = Sessao::lerFlash('sucesso');
        require RAIZ . '/views/admin/tickets/lista.php';
    }

    /* ── Morador: lista próprios ────────────────────────── */
    public function listarMorador(): void
    {
        $tickets  = $this->repo->listarPorUsuario((int) $this->usuario['id']);
        $mensagem = Sessao::lerFlash('sucesso');
        require RAIZ . '/views/morador/tickets/lista.php';
    }

    /* ── Morador: formulário novo ticket ────────────────── */
    public function formulario(): void
    {
        $erroMensagem = Sessao::lerFlash('erro');
        require RAIZ . '/views/morador/tickets/novo.php';
    }

    /* ── Morador: salva novo ticket ─────────────────────── */
    public function salvar(): void
    {
        $titulo     = trim($_POST['titulo']     ?? '');
        $descricao  = trim($_POST['descricao']  ?? '');
        $categoria  = $_POST['categoria']  ?? 'outro';
        $prioridade = $_POST['prioridade'] ?? 'normal';

        if (!$titulo || !$descricao) {
            Sessao::flash('erro', 'Título e descrição são obrigatórios.');
            Roteador::redirecionar('/tickets/novo');
            return;
        }

        $categorias  = array_keys(Ticket::$rotuloCategorias);
        $prioridades = array_keys(Ticket::$rotuloPrioridade);
        if (!in_array($categoria, $categorias, true))   $categoria  = 'outro';
        if (!in_array($prioridade, $prioridades, true)) $prioridade = 'normal';

        $id = $this->repo->criar(
            (int) $this->usuario['id'],
            $titulo,
            $descricao,
            $categoria,
            $prioridade
        );

        Sessao::flash('sucesso', 'Ticket #' . $id . ' aberto com sucesso.');
        Roteador::redirecionar('/tickets/' . $id);
    }

    /* ── Ver detalhe (admin e morador) ─────────────────── */
    public function ver(): void
    {
        $id     = (int) ($_GET['id'] ?? 0);
        $ticket = $this->repo->buscarPorId($id);

        if (!$ticket) { http_response_code(404); echo '404'; return; }

        // Morador só pode ver o próprio ticket
        if (!$this->ehAdmin && $ticket->usuarioId !== (int) $this->usuario['id']) {
            http_response_code(403); return;
        }

        $incluirInternas = $this->ehAdmin;
        $mensagens       = $this->repo->mensagens($id, $incluirInternas);
        $mensagem        = Sessao::lerFlash('sucesso');
        $erroMensagem    = Sessao::lerFlash('erro');

        if ($this->ehAdmin) {
            $userRepo    = new UsuarioRepository(Conexao::obter());
            $admins      = $userRepo->listarPorPerfil('sindico') + $userRepo->listarPorPerfil('subsindico');
            require RAIZ . '/views/admin/tickets/detalhe.php';
        } else {
            require RAIZ . '/views/morador/tickets/detalhe.php';
        }
    }

    /* ── Responder (admin e morador) ────────────────────── */
    public function responder(): void
    {
        $id       = (int) ($_POST['ticket_id'] ?? 0);
        $texto    = trim($_POST['mensagem'] ?? '');
        $interno  = $this->ehAdmin && !empty($_POST['interno']);
        $ticket   = $this->repo->buscarPorId($id);

        if (!$ticket || !$texto) {
            Roteador::redirecionar('/tickets/' . $id);
            return;
        }
        if (!$this->ehAdmin && $ticket->usuarioId !== (int) $this->usuario['id']) {
            http_response_code(403); return;
        }

        $this->repo->adicionarMensagem($id, (int) $this->usuario['id'], $texto, $interno);

        // Notifica por e-mail: admin respondeu → avisa o morador
        if ($this->ehAdmin && !$interno) {
            $dono = (new UsuarioRepository(Conexao::obter()))->buscarPorId($ticket->usuarioId);
            if ($dono && $dono->email) {
                (new EmailService())->ticketRespondido($dono->email, $dono->nome, $ticket->titulo, $id);
            }
        }

        // Admin: atualiza status se informado
        if ($this->ehAdmin && !empty($_POST['status'])) {
            $statusValidos = array_keys(Ticket::$rotuloStatus);
            $novoStatus    = $_POST['status'];
            if (in_array($novoStatus, $statusValidos, true)) {
                $this->repo->atualizarStatus($id, $novoStatus, (int) $this->usuario['id']);
            }
        }

        Sessao::flash('sucesso', 'Resposta enviada.');
        Roteador::redirecionar('/tickets/' . $id);
    }

    /* ── Admin: alterar status direto ──────────────────── */
    public function alterarStatus(): void
    {
        if (!$this->ehAdmin) { http_response_code(403); return; }
        $id     = (int) ($_POST['ticket_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if ($id && array_key_exists($status, Ticket::$rotuloStatus)) {
            $this->repo->atualizarStatus($id, $status, (int) $this->usuario['id']);
        }
        Roteador::redirecionar('/tickets/' . $id);
    }
}

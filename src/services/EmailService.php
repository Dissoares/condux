<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailException;

require_once RAIZ . '/vendor/autoload.php';
require_once RAIZ . '/src/repository/ConfiguracaoRepository.php';

class EmailService
{
    private array $cfg;
    private bool $ativo;

    public function __construct()
    {
        $repo      = new ConfiguracaoRepository(Conexao::obter());
        $this->cfg = $repo->todas();
        $this->ativo = ($this->cfg['email_ativo'] ?? '0') === '1'
                       && !empty($this->cfg['email_smtp_host'])
                       && !empty($this->cfg['email_smtp_usuario']);
    }

    public function ativo(): bool { return $this->ativo; }

    /** Envia um e-mail HTML. Retorna true em sucesso, false em falha silenciosa. */
    public function enviar(string $paraEmail, string $paraNome, string $assunto, string $html): bool
    {
        if (!$this->ativo) return false;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $this->cfg['email_smtp_host'];
            $mail->Port       = (int) ($this->cfg['email_smtp_porta'] ?? 587);
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->cfg['email_smtp_usuario'];
            $mail->Password   = $this->cfg['email_smtp_senha'] ?? '';
            $seg = $this->cfg['email_smtp_seguranca'] ?? 'tls';
            if ($seg === 'ssl') $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            elseif ($seg === 'tls') $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            else $mail->SMTPAutoTLS = false;

            $mail->setFrom(
                $this->cfg['email_remetente_email'] ?? $this->cfg['email_smtp_usuario'],
                $this->cfg['email_remetente_nome']  ?? ($this->cfg['app_nome'] ?? 'Condux')
            );
            $mail->addAddress($paraEmail, $paraNome);
            $mail->CharSet  = 'UTF-8';
            $mail->isHTML(true);
            $mail->Subject  = $assunto;
            $mail->Body     = $this->template($assunto, $html);
            $mail->AltBody  = strip_tags($html);
            $mail->send();
            return true;
        } catch (MailException) {
            return false;
        }
    }

    // ── Notificações específicas ──────────────────────────────────────────

    public function ticketRespondido(string $email, string $nome, string $titulo, int $ticketId): bool
    {
        $link = url("tickets/{$ticketId}");
        return $this->enviar($email, $nome,
            "Seu ticket foi respondido — #{$ticketId}",
            "<p>Olá, <strong>" . htmlspecialchars($nome) . "</strong>!</p>
             <p>Seu ticket <strong>#{$ticketId} — " . htmlspecialchars($titulo) . "</strong> recebeu uma nova resposta da equipe.</p>
             " . $this->botao('Ver resposta', $link)
        );
    }

    public function taxaCondominialAberta(string $email, string $nome, string $competencia, float $valor, string $vencimento): bool
    {
        $mesAno  = $this->formatarCompetencia($competencia);
        $vencFmt = date('d/m/Y', strtotime($vencimento));
        $link    = url('minhas-taxas');
        return $this->enviar($email, $nome,
            "Taxa condominial de {$mesAno} disponível",
            "<p>Olá, <strong>" . htmlspecialchars($nome) . "</strong>!</p>
             <p>A taxa condominial referente a <strong>{$mesAno}</strong> já está disponível.</p>
             <table style='border-collapse:collapse;margin:16px 0;'>
               <tr><td style='padding:4px 12px 4px 0;color:#6b7280;'>Valor</td><td><strong>R$ " . number_format($valor,2,',','.') . "</strong></td></tr>
               <tr><td style='padding:4px 12px 4px 0;color:#6b7280;'>Vencimento</td><td><strong>{$vencFmt}</strong></td></tr>
             </table>
             " . $this->botao('Ver taxa', $link)
        );
    }

    public function taxaExtraAberta(string $email, string $nome, string $nomeParcela, float $valor, string $vencimento): bool
    {
        $vencFmt = date('d/m/Y', strtotime($vencimento));
        $link    = url('minhas-taxas');
        return $this->enviar($email, $nome,
            "Nova parcela disponível — " . $nomeParcela,
            "<p>Olá, <strong>" . htmlspecialchars($nome) . "</strong>!</p>
             <p>A parcela <strong>" . htmlspecialchars($nomeParcela) . "</strong> já está disponível para pagamento.</p>
             <table style='border-collapse:collapse;margin:16px 0;'>
               <tr><td style='padding:4px 12px 4px 0;color:#6b7280;'>Valor</td><td><strong>R$ " . number_format($valor,2,',','.') . "</strong></td></tr>
               <tr><td style='padding:4px 12px 4px 0;color:#6b7280;'>Vencimento</td><td><strong>{$vencFmt}</strong></td></tr>
             </table>
             " . $this->botao('Ver parcela', $link)
        );
    }

    public function pagamentoAprovado(string $email, string $nome, string $competencia, float $valor): bool
    {
        $mesAno = $this->formatarCompetencia($competencia);
        $link   = url('minhas-taxas');
        return $this->enviar($email, $nome,
            "Pagamento aprovado — {$mesAno}",
            "<p>Olá, <strong>" . htmlspecialchars($nome) . "</strong>!</p>
             <p>O pagamento da taxa condominial de <strong>{$mesAno}</strong> no valor de
             <strong>R$ " . number_format($valor,2,',','.') . "</strong> foi <span style='color:#16a34a;font-weight:700;'>aprovado</span>.</p>
             " . $this->botao('Ver extrato', $link)
        );
    }

    public function taxaCondominialVencida(string $email, string $nome, string $competencia, float $valor, int $diasAtraso): bool
    {
        $mesAno = $this->formatarCompetencia($competencia);
        $link   = url('minhas-taxas');
        return $this->enviar($email, $nome,
            "⚠️ Taxa condominial de {$mesAno} em atraso",
            "<p>Olá, <strong>" . htmlspecialchars($nome) . "</strong>!</p>
             <p style='color:#dc2626;'>A taxa condominial de <strong>{$mesAno}</strong> ainda não foi paga e está
             em atraso há <strong>{$diasAtraso} dia" . ($diasAtraso > 1 ? 's' : '') . "</strong>.</p>
             <p>Valor: <strong>R$ " . number_format($valor,2,',','.') . "</strong></p>
             <p>Regularize o quanto antes para evitar multas.</p>
             " . $this->botao('Regularizar agora', $link)
        );
    }

    public function taxaExtraVencida(string $email, string $nome, string $nomeParcela, float $valor, int $diasAtraso): bool
    {
        $link = url('minhas-taxas');
        return $this->enviar($email, $nome,
            "⚠️ Parcela em atraso — " . $nomeParcela,
            "<p>Olá, <strong>" . htmlspecialchars($nome) . "</strong>!</p>
             <p style='color:#dc2626;'>A parcela <strong>" . htmlspecialchars($nomeParcela) . "</strong> ainda não foi paga e está
             em atraso há <strong>{$diasAtraso} dia" . ($diasAtraso > 1 ? 's' : '') . "</strong>.</p>
             <p>Valor: <strong>R$ " . number_format($valor,2,',','.') . "</strong></p>
             " . $this->botao('Regularizar agora', $link)
        );
    }

    public function recuperarSenha(string $email, string $nome, string $token): bool
    {
        $link = url("redefinir-senha?token={$token}");
        return $this->enviar($email, $nome,
            "Redefinir senha",
            "<p>Olá, <strong>" . htmlspecialchars($nome) . "</strong>!</p>
             <p>Recebemos uma solicitação para redefinir a senha da sua conta.</p>
             <p>Clique no botão abaixo (válido por <strong>2 horas</strong>):</p>
             " . $this->botao('Redefinir senha', $link) . "
             <p style='color:#6b7280;font-size:.8rem;margin-top:24px;'>
               Se você não solicitou a redefinição, ignore este e-mail.
             </p>"
        );
    }

    // ── Helpers privados ──────────────────────────────────────────────────

    private function botao(string $texto, string $link): string
    {
        $cor = $this->cfg['cor_primaria'] ?? '#1a3c5e';
        return "<p><a href='{$link}' style='display:inline-block;padding:10px 24px;background:{$cor};color:#fff;
                text-decoration:none;border-radius:6px;font-weight:600;font-size:.9rem;'>{$texto}</a></p>";
    }

    private function formatarCompetencia(string $competencia): string
    {
        $meses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho',
                  'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
        [$ano, $mes] = explode('-', $competencia);
        return ($meses[(int)$mes] ?? $mes) . '/' . $ano;
    }

    private function template(string $titulo, string $conteudo): string
    {
        $appNome  = htmlspecialchars($this->cfg['app_nome']  ?? 'Condux');
        $corPrim  = $this->cfg['cor_primaria'] ?? '#1a3c5e';
        $corEsc   = $this->cfg['cor_escura']   ?? '#0f2540';
        return <<<HTML
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
        <title>{$titulo}</title></head>
        <body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,sans-serif;">
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:32px 16px;">
            <tr><td align="center">
              <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);">
                <!-- Header -->
                <tr><td style="background:linear-gradient(90deg,{$corEsc},{$corPrim});padding:28px 32px;">
                  <span style="color:#fff;font-size:1.25rem;font-weight:800;">{$appNome}</span>
                </td></tr>
                <!-- Body -->
                <tr><td style="padding:32px;color:#111827;font-size:.95rem;line-height:1.6;">
                  {$conteudo}
                </td></tr>
                <!-- Footer -->
                <tr><td style="padding:20px 32px;background:#f9fafb;border-top:1px solid #e5e7eb;color:#6b7280;font-size:.78rem;text-align:center;">
                  {$appNome} — Sistema de gestão de condomínio<br>
                  Este é um e-mail automático, não responda.
                </td></tr>
              </table>
            </td></tr>
          </table>
        </body></html>
        HTML;
    }
}

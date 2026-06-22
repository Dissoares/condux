<?php

declare(strict_types=1);

return [
    'nome'         => 'Condux',
    'versao'       => '1.0.0',
    'ambiente'     => $_ENV['APP_AMBIENTE'] ?? 'producao',   // desenvolvimento | producao
    'url_base'     => $_ENV['APP_URL']      ?? 'http://localhost/condux/public',
    'fuso_horario' => 'America/Sao_Paulo',
    'locale'       => 'pt_BR',

    // Pasta onde ficam os uploads (relativa à raiz do projeto)
    'pasta_uploads' => __DIR__ . '/../public/uploads',

    // Extensões permitidas por tipo de upload
    'extensoes_imagem'    => ['jpg', 'jpeg', 'png', 'webp'],
    'extensoes_documento' => ['pdf', 'docx', 'xlsx'],
    'extensoes_video'     => ['mp4', 'mov', 'avi'],

    // Tamanho máximo de upload em bytes (10 MB)
    'tamanho_maximo_upload' => 10 * 1024 * 1024,
];

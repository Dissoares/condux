CREATE TABLE IF NOT EXISTS push_subscriptions (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    endpoint   VARCHAR(500) NOT NULL,
    p256dh     VARCHAR(255) NOT NULL,
    auth       VARCHAR(100) NOT NULL,
    criado_em  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_endpoint (endpoint(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

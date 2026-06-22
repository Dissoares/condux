-- Adiciona perfil 'conselho' ao enum de usuarios
ALTER TABLE usuarios
    MODIFY COLUMN perfil ENUM('sindico','subsindico','morador','conselho') NOT NULL DEFAULT 'morador';

-- Tabela de gestões (mandatos da administração)
CREATE TABLE gestoes (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    descricao    VARCHAR(200) NOT NULL,
    inicio       DATE         NOT NULL,
    fim          DATE         NULL,
    status       ENUM('ativa','encerrada') NOT NULL DEFAULT 'ativa',
    observacoes  TEXT         NULL,
    criado_em    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Membros de cada gestão com seus cargos
CREATE TABLE gestao_membros (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    gestao_id   INT UNSIGNED NOT NULL,
    usuario_id  INT UNSIGNED NOT NULL,
    cargo       ENUM('sindico','subsindico','conselheiro','suplente') NOT NULL DEFAULT 'conselheiro',
    criado_em   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_gestao_usuario (gestao_id, usuario_id),
    CONSTRAINT fk_gm_gestao   FOREIGN KEY (gestao_id)  REFERENCES gestoes(id)  ON DELETE CASCADE,
    CONSTRAINT fk_gm_usuario  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

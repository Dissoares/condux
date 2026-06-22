-- Corrige hash da senha do administrador padrão (senha: condux@2025)
UPDATE usuarios
SET senha = '$2y$12$GzRFWZ8wC7ed.LGDJY6YGe9m3.wNbB.Ib57MPHzEh7MfwL4Fc0igq'
WHERE email = 'admin@condux.com';

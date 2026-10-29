-- Banco de dados do ReUni
-- Rodar no phpMyAdmin (aba SQL) ou no MySQL Workbench

CREATE DATABASE IF NOT EXISTS reuni CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE reuni;

CREATE TABLE instituicao (
    id_instituicao INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    dominio_email VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE campus (
    id_campus INT AUTO_INCREMENT PRIMARY KEY,
    id_instituicao INT NOT NULL,
    nome VARCHAR(60) NOT NULL,
    FOREIGN KEY (id_instituicao) REFERENCES instituicao(id_instituicao) ON DELETE CASCADE
);

CREATE TABLE curso (
    id_curso INT AUTO_INCREMENT PRIMARY KEY,
    id_instituicao INT NOT NULL,
    nome VARCHAR(60) NOT NULL,
    FOREIGN KEY (id_instituicao) REFERENCES instituicao(id_instituicao) ON DELETE CASCADE
);

CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome_completo VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    id_curso INT,
    id_campus INT,
    telefone VARCHAR(20),
    foto_perfil VARCHAR(255),
    reputacao_media DECIMAL(2,1) NOT NULL DEFAULT 0.0,
    status_conta ENUM('ativo','suspenso','banido') NOT NULL DEFAULT 'ativo',
    data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_curso) REFERENCES curso(id_curso) ON DELETE SET NULL,
    FOREIGN KEY (id_campus) REFERENCES campus(id_campus) ON DELETE SET NULL
);

CREATE TABLE categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(40) NOT NULL UNIQUE,
    id_categoria_pai INT,
    FOREIGN KEY (id_categoria_pai) REFERENCES categoria(id_categoria) ON DELETE SET NULL
);

CREATE TABLE anuncio (
    id_anuncio INT AUTO_INCREMENT PRIMARY KEY,
    id_vendedor INT NOT NULL,
    id_categoria INT NOT NULL,
    titulo VARCHAR(80) NOT NULL,
    descricao VARCHAR(500) NOT NULL,
    preco DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    condicao ENUM('novo','seminovo','usado','pecas') NOT NULL,
    status_anuncio ENUM('disponivel','reservado','vendido','em_doacao','doado','removido') NOT NULL DEFAULT 'disponivel',
    local_retirada VARCHAR(100),
    data_publicacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    prazo_doacao DATE,
    FOREIGN KEY (id_vendedor) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria)
);

CREATE TABLE anuncio_foto (
    id_foto INT AUTO_INCREMENT PRIMARY KEY,
    id_anuncio INT NOT NULL,
    caminho VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_anuncio) REFERENCES anuncio(id_anuncio) ON DELETE CASCADE
);

CREATE TABLE favorito (
    id_usuario INT NOT NULL,
    id_anuncio INT NOT NULL,
    data_favorito DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario, id_anuncio),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_anuncio) REFERENCES anuncio(id_anuncio) ON DELETE CASCADE
);

CREATE TABLE proposta (
    id_proposta INT AUTO_INCREMENT PRIMARY KEY,
    id_anuncio INT NOT NULL,
    id_comprador INT NOT NULL,
    valor_proposto DECIMAL(10,2) NOT NULL,
    status_proposta ENUM('pendente','aceita','recusada','cancelada') NOT NULL DEFAULT 'pendente',
    data_proposta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_anuncio) REFERENCES anuncio(id_anuncio) ON DELETE CASCADE,
    FOREIGN KEY (id_comprador) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

CREATE TABLE mensagem (
    id_mensagem INT AUTO_INCREMENT PRIMARY KEY,
    id_remetente INT NOT NULL,
    id_destinatario INT NOT NULL,
    id_anuncio INT,
    conteudo VARCHAR(1000) NOT NULL,
    status_leitura ENUM('lida','nao_lida') NOT NULL DEFAULT 'nao_lida',
    data_envio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_remetente) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_destinatario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_anuncio) REFERENCES anuncio(id_anuncio) ON DELETE SET NULL
);

CREATE TABLE transacao (
    id_transacao INT AUTO_INCREMENT PRIMARY KEY,
    id_anuncio INT NOT NULL,
    id_comprador INT NOT NULL,
    id_vendedor INT NOT NULL,
    valor_final DECIMAL(10,2) NOT NULL,
    status_transacao ENUM('concluida','cancelada') NOT NULL DEFAULT 'concluida',
    data_transacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_anuncio) REFERENCES anuncio(id_anuncio),
    FOREIGN KEY (id_comprador) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_vendedor) REFERENCES usuario(id_usuario)
);

CREATE TABLE avaliacao (
    id_avaliacao INT AUTO_INCREMENT PRIMARY KEY,
    id_transacao INT NOT NULL,
    id_avaliador INT NOT NULL,
    id_avaliado INT NOT NULL,
    nota TINYINT NOT NULL,
    comentario VARCHAR(300),
    data_avaliacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_transacao) REFERENCES transacao(id_transacao) ON DELETE CASCADE,
    FOREIGN KEY (id_avaliador) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_avaliado) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

CREATE TABLE parceiro (
    id_parceiro INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo_parceiro ENUM('ong','cooperativa','comercio') NOT NULL,
    cnpj CHAR(18) NOT NULL UNIQUE,
    area_atuacao VARCHAR(150) NOT NULL,
    contato VARCHAR(100) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE doacao (
    id_doacao INT AUTO_INCREMENT PRIMARY KEY,
    id_anuncio INT NOT NULL,
    id_parceiro INT NOT NULL,
    data_doacao DATE NOT NULL,
    status_doacao ENUM('agendada','concluida','cancelada') NOT NULL DEFAULT 'agendada',
    FOREIGN KEY (id_anuncio) REFERENCES anuncio(id_anuncio) ON DELETE CASCADE,
    FOREIGN KEY (id_parceiro) REFERENCES parceiro(id_parceiro)
);

CREATE TABLE denuncia (
    id_denuncia INT AUTO_INCREMENT PRIMARY KEY,
    id_denunciante INT NOT NULL,
    id_anuncio_alvo INT,
    id_usuario_alvo INT,
    motivo ENUM('item_proibido','conteudo_improprio','fraude','outro') NOT NULL,
    descricao VARCHAR(300),
    status_denuncia ENUM('pendente','em_analise','resolvida') NOT NULL DEFAULT 'pendente',
    data_denuncia DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_denunciante) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_anuncio_alvo) REFERENCES anuncio(id_anuncio) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_alvo) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

CREATE TABLE notificacao (
    id_notificacao INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo ENUM('mensagem','proposta','favorito','prazo_doacao','avaliacao') NOT NULL,
    conteudo VARCHAR(200) NOT NULL,
    status_leitura ENUM('lida','nao_lida') NOT NULL DEFAULT 'nao_lida',
    data_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- Dados iniciais

INSERT INTO instituicao (nome, dominio_email) VALUES
('Faculdade Exemplo', 'aluno.faculdadeexemplo.br');

INSERT INTO campus (id_instituicao, nome) VALUES
(1, 'Campus Central'),
(1, 'Campus Norte');

INSERT INTO curso (id_instituicao, nome) VALUES
(1, 'Engenharia de Software'),
(1, 'Sistemas de Informacao'),
(1, 'Administracao'),
(1, 'Design Digital');

INSERT INTO categoria (nome) VALUES
('Livros'),
('Eletronicos'),
('Moveis'),
('Roupas'),
('Material de Laboratorio'),
('Outros');

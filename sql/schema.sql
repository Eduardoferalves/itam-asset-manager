-- Criação do Banco de Dados
CREATE DATABASE IF NOT EXISTS `itam_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `itam_db`;

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS `usuario` (
    `id_usuario` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `senha_hash` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Departamentos
CREATE TABLE IF NOT EXISTS `departamento` (
    `id_departamento` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(50) UNIQUE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Fornecedores
CREATE TABLE IF NOT EXISTS `fornecedor` (
    `id_fornecedor` INT AUTO_INCREMENT PRIMARY KEY,
    `nome_empresa` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Categorias
CREATE TABLE IF NOT EXISTS `categoria` (
    `id_categoria` INT AUTO_INCREMENT PRIMARY KEY,
    `descricao` VARCHAR(50) UNIQUE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Ativos
CREATE TABLE IF NOT EXISTS `ativo` (
    `id_ativo` INT AUTO_INCREMENT PRIMARY KEY,
    `patrimonio` VARCHAR(30) UNIQUE NOT NULL,
    `status` ENUM('Ativo', 'Inativo', 'Em Manutenção') NOT NULL DEFAULT 'Ativo',
    `data_aquisicao` DATE NOT NULL,
    `id_categoria` INT,
    `id_departamento` INT,
    `id_fornecedor` INT,
    CONSTRAINT `fk_ativo_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON DELETE SET NULL,
    CONSTRAINT `fk_ativo_departamento` FOREIGN KEY (`id_departamento`) REFERENCES `departamento` (`id_departamento`) ON DELETE SET NULL,
    CONSTRAINT `fk_ativo_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedor` (`id_fornecedor`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Manutenções
CREATE TABLE IF NOT EXISTS `manutencao` (
    `id_manutencao` INT AUTO_INCREMENT PRIMARY KEY,
    `descricao` TEXT NOT NULL,
    `custo` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `data_reg` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `id_ativo` INT,
    `id_usuario` INT,
    CONSTRAINT `fk_manutencao_ativo` FOREIGN KEY (`id_ativo`) REFERENCES `ativo` (`id_ativo`) ON DELETE RESTRICT,
    CONSTRAINT `fk_manutencao_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

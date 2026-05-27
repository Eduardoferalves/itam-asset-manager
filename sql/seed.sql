USE `itam_db`;

-- Limpeza prévia para garantir reentrabilidade (opcional, mas bom para desenvolvimento)
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `manutencao`;
TRUNCATE TABLE `ativo`;
TRUNCATE TABLE `categoria`;
TRUNCATE TABLE `fornecedor`;
TRUNCATE TABLE `departamento`;
TRUNCATE TABLE `usuario`;
SET FOREIGN_KEY_CHECKS = 1;

-- Inserindo Usuário Administrador Padrão (Email: admin@itam.com | Senha: admin123)
INSERT INTO `usuario` (`id_usuario`, `nome`, `email`, `senha_hash`) VALUES
(1, 'Administrador ITAM', 'admin@itam.com', '$2y$10$7PXFzvJd/dfEDFDevfTiB.OIzk.fc7.9ifsaoo7DiPiEnyOLLvKYy');

-- Inserindo Departamentos (Apoio, não possui CRUD de interface)
INSERT INTO `departamento` (`id_departamento`, `nome`) VALUES
(1, 'Tecnologia da Informação'),
(2, 'Recursos Humanos'),
(3, 'Financeiro'),
(4, 'Operações'),
(5, 'Jurídico');

-- Inserindo Fornecedores (Apoio, não possui CRUD de interface)
INSERT INTO `fornecedor` (`id_fornecedor`, `nome_empresa`) VALUES
(1, 'Dell Computadores do Brasil'),
(2, 'Lenovo Tecnologia'),
(3, 'HP Inc.'),
(4, 'Apple Computer Brasil'),
(5, 'Cisco Systems');

-- Inserindo Categorias (Apoio, não possui CRUD de interface)
INSERT INTO `categoria` (`id_categoria`, `descricao`) VALUES
(1, 'Notebook'),
(2, 'Desktop'),
(3, 'Servidor'),
(4, 'Switch de Rede'),
(5, 'Monitor de Vídeo');

-- Inserindo Ativos de Teste
INSERT INTO `ativo` (`id_ativo`, `patrimonio`, `status`, `data_aquisicao`, `id_categoria`, `id_departamento`, `id_fornecedor`) VALUES
(1, 'DESK-001', 'Ativo', '2025-01-10', 2, 1, 2),
(2, 'NOTE-002', 'Ativo', '2025-02-15', 1, 2, 1),
(3, 'SERV-003', 'Em Manutenção', '2024-11-20', 3, 1, 1),
(4, 'SWIT-004', 'Ativo', '2024-06-05', 4, 1, 5),
(5, 'NOTE-005', 'Inativo', '2023-08-12', 1, 3, 3);

-- Inserindo Manutenções de Teste
INSERT INTO `manutencao` (`id_manutencao`, `descricao`, `custo`, `data_reg`, `id_ativo`, `id_usuario`) VALUES
(1, 'Troca de pasta térmica e limpeza interna preventiva.', 150.00, '2025-02-01 10:00:00', 1, 1),
(2, 'Upgrade de memória RAM de 8GB para 16GB Kingston DDR4.', 280.00, '2025-02-18 14:30:00', 2, 1),
(3, 'Substituição de disco rígido danificado por SSD Crucial de 480GB.', 350.00, '2025-03-05 09:15:00', 3, 1),
(4, 'Substituição de fonte de alimentação queimada de 500W.', 220.00, '2025-03-12 16:45:00', 3, 1);

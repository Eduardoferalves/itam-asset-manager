# 🖥️ ITAM Asset Manager
### Sistema de Gestão de Ativos de TI e Governança Corporativa

Este projeto consiste no sistema **ITAM Asset Manager**, desenvolvido como o projeto acadêmico (A3) da graduação em **Análise e Desenvolvimento de Sistemas (ADS)** do Centro Universitário de Brasília (**CEUB**). Trata-se de uma solução corporativa completa para inventário, controle de ciclo de vida e governança de ativos de Tecnologia da Informação.

---

## 📑 Índice
1. [Autores](#-autores-e-integração-acadêmica)
2. [Arquitetura](#-arquitetura-e-diferenciais-de-software)
3. [Segurança](#-matriz-de-conformidade-de-segurança)
4. [Instalação Rápida](#-guia-rápido-de-instalação)
5. [Pré-requisitos](#-pré-requisitos)
6. [Passo a Passo Detalhado](#-passo-a-passo-detalhado)
7. [Criação do Banco de Dados](#-criação-do-banco-de-dados)
8. [Credenciais de Acesso](#-credenciais-padrão-de-homologação)
9. [Funcionalidades](#-funcionalidades-principais)
10. [Troubleshooting](#-troubleshooting)

---

## 👨‍💻 Autores e Integração Acadêmica

Abaixo constam as informações oficiais dos integrantes do grupo responsáveis pelo projeto:

| Nome do Aluno | Registro Acadêmico (RA) | E-mail Institucional |
| :--- | :--- | :--- |
| **Eduardo Fernandes Alves** | 22505804 | eduardo.feralves@sempreceub.com |
| **Paulo Henrique Cardoso Rocha** | 22503489 | paulorocha@sempreceub.com |

---

## 🏛️ Arquitetura e Diferenciais de Software

O **ITAM Asset Manager** foi projetado seguindo rigorosos conceitos de engenharia de software, garantindo robustez, portabilidade e máxima facilidade de deploy.

### 1. Front Controller & Padrão MVC Nativo
* A aplicação utiliza uma arquitetura **MVC (Model-View-Controller)** implementada de forma nativa e sem a dependência de frameworks complexos, garantindo excelente performance e facilidade de depuração.
* Centralização do fluxo de execução no **Front Controller** (`index.php`), que atua como o ponto de entrada único do sistema. Ele é responsável pelo roteamento dinâmico de requisições, gerenciamento centralizado do ciclo de vida das sessões, tratamento de mensagens flash de feedback e injeção controlada de dependências.

### 2. Padrão PRG (Post-Redirect-Get) com Retenção de Estado (`$old_input`)
* Para contornar um dos principais problemas de usabilidade do PHP clássico (a perda de dados de formulários ao falhar em uma validação e o reenvio duplicado de requisições ao atualizar a página), a aplicação adota o padrão estrutural **PRG (Post-Redirect-Get)**.
* Ao detectar um erro de validação em uma submissão `POST`, o Controller persiste temporariamente os dados submetidos na sessão sob a chave `old_input` e executa um redirecionamento limpo (`GET`).
* O Front Controller centraliza o ciclo de vida desta variável, injetando-a dinamicamente na view correspondente e destruindo o seu estado da sessão na sequência. Isso provê uma experiência rica ao usuário, mantendo os dados preenchidos nos formulários sem qualquer recarga de página destrutiva.

### 3. Integridade e Consistência Relacional via PDO Strict Mode
* A camada de persistência de dados estabelece conexões utilizando a extensão **PDO** (PHP Data Objects) parametrizada sob o padrão de projeto **Singleton** em `conexao.php`.
* Para mitigar discrepâncias de tipos de dados ou o truncamento silencioso de strings inválidas em campos estruturados (como tipos `ENUM`), a inicialização do driver ativa explicitamente a diretiva `STRICT_ALL_TABLES` no MySQL.
* O driver é instruído explicitamente a rejeitar qualquer transação de escrita em desconformidade com a tipagem rigorosa estipulada no dicionário de dados do banco.

### 4. Segurança Defensiva e Conformidade (CIS Controls & OWASP Top 10)
A segurança foi incorporada desde a concepção do código fonte, mitigando os principais vetores de vulnerabilidades comuns mapeados pelo consórcio OWASP e em conformidade com as diretivas do PPSI 2.0 (Processo de Desenvolvimento de Software Seguro).

---

## 🛡️ Matriz de Conformidade de Segurança

| Vetor de Ataque | Mitigação Técnica Aplicada no Projeto | Impacto na Aplicação |
| :--- | :--- | :--- |
| **SQL Injection (SQLi)** | Utilização integral de **Prepared Statements** parametrizados com a classe PDO em todos os métodos de busca e persistência das Models. | Impede a execução de trechos SQL maliciosos injetados por meio de inputs. |
| **Cross-Site Scripting (XSS)** | Sanitização contextual e rigorosa das saídas de dados nas Views utilizando a função nativa `htmlspecialchars()` parametrizada em UTF-8. | Neutraliza qualquer tentativa de injeção de scripts JavaScript maliciosos no navegador do usuário. |
| **Cross-Site Request Forgery (CSRF)** | Implementação e validação de tokens CSRF criptograficamente seguros gerados em sessão unificada no fluxo do Front Controller e validados estritamente no `AuthController`. | Previne que agentes externos forjem requisições ou ações não autorizadas em nome do usuário autenticado. |
| **Session Hijacking / Fixation** | Ciclo de vida rigoroso de sessão com cookies configurados com as diretivas `SameSite=Strict`, `HttpOnly` e regeneração periódica de IDs de sessão. | Garante que cookies de autenticação não sejam acessíveis por scripts JavaScript externos (`document.cookie`) e previne roubos de sessão. |

---

## ⚡ Guia Rápido de Instalação

Para instalar e rodar o projeto **em menos de 5 minutos**, siga esta sequência:

1. **Extraia o projeto** em `C:\xampp\htdocs\itam-asset-manager`
2. **Abra o XAMPP Control Panel** e clique em **Start** para Apache e MySQL
3. **Crie o banco de dados** via phpMyAdmin (abra `http://localhost/phpmyadmin`)
4. **Importe os scripts SQL** (primeiro `schema.sql`, depois `seed.sql`)
5. **Acesse** `http://localhost/itam-asset-manager`
6. **Faça login** com: `admin@itam.com` / `admin123`

---

## 📋 Pré-requisitos

Certifique-se de que você tem todos os requisitos mínimos instalados:

| Requisito | Versão Mínima | Status |
| :--- | :--- | :--- |
| **XAMPP** | 3.3.0 ou superior | ✅ Necessário |
| **PHP** | 8.1 ou superior | ✅ Necessário |
| **MySQL/MariaDB** | 5.7 ou superior | ✅ Necessário |
| **Navegador Moderno** | Chrome, Firefox, Edge | ✅ Necessário |

**Onde baixar XAMPP:**  
👉 https://www.apachefriends.org/download.html (Selecione a versão para **Windows**)

---

## 🚀 Passo a Passo Detalhado

### **Passo 1: Posicionamento dos Arquivos do Projeto**

Todos os arquivos do projeto devem estar na seguinte localização:
```
C:\xampp\htdocs\itam-asset-manager\
```

**Como fazer:**
1. Se o XAMPP não estiver instalado em `C:\xampp\`, procure pela pasta `htdocs` onde ele foi instalado
2. Extraia o arquivo `itam-asset-manager` (ou copie a pasta) para dentro de `htdocs`
3. Verifique se o caminho está correto visitando `http://localhost/`

**Esperado:**
- Você deve ver um index do XAMPP com links para "phpMyAdmin", "New XAMPP Dashboard", etc.

---

### **Passo 2: Iniciar Apache e MySQL no XAMPP Control Panel**

1. **Localize o XAMPP Control Panel** no seu computador:
   - Normalmente está em: `C:\xampp\xampp-control.exe`
   - Ou acesse via: Iniciar > Pesquisar por "xampp"

2. **Clique no botão "Start"** para os seguintes módulos (nesta ordem):
   - ✅ **Apache** (web server)
   - ✅ **MySQL** (banco de dados)

**Esperado:**
- Ambos os módulos devem ficar com status **"Running"** em verde
- Se houver erro de porta já em uso, consulte a seção [Troubleshooting](#-troubleshooting)

---

### **Passo 3: Criação e Configuração do Banco de Dados**

#### **Método 1: Via phpMyAdmin (Recomendado para Iniciantes)**

1. **Abra phpMyAdmin:**
   - Acesse no navegador: http://localhost/phpmyadmin/
   - Login automático (não precisa de senha no XAMPP padrão)

2. **Crie um novo banco de dados:**
   - Clique em **"Novo"** no menu lateral esquerdo
   - Em "Nome do banco de dados", digite exatamente: **`itam_db`**
   - Em "Collação", selecione: **`utf8mb4_unicode_ci`**
   - Clique em **"Criar"**

3. **Selecione o banco criado:**
   - No menu lateral, clique em **`itam_db`** para selecioná-lo

---

#### **Método 2: Via Linha de Comando (Para Usuários Avançados)**

Se preferir usar o terminal/CMD do Windows:

1. **Abra o Prompt de Comando (CMD)** e navegue até:
   ```cmd
   cd C:\xampp\mysql\bin
   ```

2. **Conecte ao MySQL:**
   ```cmd
   mysql -u root
   ```

3. **Execute os comandos SQL:**
   ```sql
   CREATE DATABASE IF NOT EXISTS `itam_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   USE `itam_db`;
   ```

4. **Saia do MySQL:**
   ```sql
   EXIT;
   ```

---

### **Passo 4: Importação dos Scripts SQL (Sequencial)**

**IMPORTANTE:** Os scripts DEVEM ser executados nesta ordem exata:

#### **Etapa 1: Importar Schema (Estrutura das Tabelas)**

1. No phpMyAdmin, com o banco `itam_db` selecionado
2. Clique na aba **"Importar"** (ou "Import")
3. Clique em **"Escolher arquivo"** (ou "Choose File")
4. Navegue até: `C:\xampp\htdocs\itam-asset-manager\sql\schema.sql`
5. Clique em **"Executar"** (ou "Go")
6. **Esperado:** Mensagem de sucesso "Importação realizada com sucesso"

**O que este script faz:**
```sql
-- Cria o banco (se não existir)
CREATE DATABASE IF NOT EXISTS `itam_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Cria as tabelas:
-- usuario, departamento, fornecedor, categoria, ativo, manutencao
```

---

#### **Etapa 2: Importar Dados de Teste (Sementes)**

1. No phpMyAdmin, **ainda com `itam_db` selecionado**
2. Clique na aba **"Importar"**
3. Clique em **"Escolher arquivo"**
4. Navegue até: `C:\xampp\htdocs\itam-asset-manager\sql\seed.sql`
5. Clique em **"Executar"**
6. **Esperado:** Mensagem de sucesso com as linhas inseridas

**O que este script faz:**
```sql
-- Insere usuário administrador
-- Insere departamentos (TI, RH, Financeiro, etc)
-- Insere fornecedores (Dell, Lenovo, HP, etc)
-- Insere categorias (Notebook, Desktop, Servidor, etc)
-- Insere ativos de teste com números de patrimônio
-- Insere registros de manutenção para teste
```

---

#### **Alternativa: Importar via Linha de Comando**

Se preferir executar tudo de uma vez via terminal:

```cmd
cd C:\xampp\mysql\bin

mysql -u root itam_db < "C:\xampp\htdocs\itam-asset-manager\sql\schema.sql"

mysql -u root itam_db < "C:\xampp\htdocs\itam-asset-manager\sql\seed.sql"
```

**Esperado:** Sem mensagens de erro, tudo executou com sucesso.

---

### **Passo 5: Verificar se o Banco foi Criado Corretamente**

Volte ao phpMyAdmin e verifique se todas as tabelas foram criadas:

1. Selecione `itam_db` no menu lateral
2. Você deve ver essas 6 tabelas:
   - ✅ `usuario` (1 usuário: admin)
   - ✅ `departamento` (5 departamentos)
   - ✅ `fornecedor` (5 fornecedores)
   - ✅ `categoria` (5 categorias)
   - ✅ `ativo` (5 ativos de teste)
   - ✅ `manutencao` (4 registros de manutenção)

3. Clique em cada tabela para visualizar os dados

---

### **Passo 6: Inicializar e Acessar a Aplicação**

Agora que tudo está configurado, acesse o sistema:

1. **Abra o navegador** e vá para:
   ```
   http://localhost/itam-asset-manager/
   ```

2. **Você será automaticamente redirecionado para a tela de login**

3. **Deverá ver a interface:**
   - Logo/ícone da aplicação
   - Campo de "Endereço de E-mail"
   - Campo de "Senha"
   - Botão de login

---

## 🔑 Credenciais Padrão de Homologação

Para acessar o sistema e testar todas as funcionalidades, utilize as seguintes credenciais que foram automaticamente criadas no banco de dados:

### **Usuário Administrador Padrão**

| Campo | Valor |
| :--- | :--- |
| **Email** | `admin@itam.com` |
| **Senha** | `admin123` |
| **Nome** | Administrador ITAM |
| **Perfil** | Administrador Sistema |

**Como usar:**
1. Na tela de login, insira: `admin@itam.com`
2. Insira a senha: `admin123`
3. Clique em **"Entrar"**
4. Você será redirecionado ao painel principal

---

## 🎯 Funcionalidades Principais

O sistema oferece as seguintes funcionalidades:

### **1. Autenticação e Controle de Sessão**
- ✅ Login seguro com email e senha
- ✅ Criptografia de senhas com bcrypt
- ✅ Proteção contra CSRF (Cross-Site Request Forgery)
- ✅ Regeneração automática de tokens de sessão
- ✅ Logout com limpeza de sessão

### **2. Gestão de Ativos (CRUD)**
- ✅ **Listagem:** Visualizar todos os ativos com filtros
- ✅ **Cadastro:** Adicionar novos ativos com:
  - Número de Patrimônio (único)
  - Categoria (Notebook, Desktop, Servidor, etc)
  - Status (Ativo, Inativo, Em Manutenção)
  - Data de Aquisição
  - Departamento
  - Fornecedor
- ✅ **Editar:** Modificar dados de um ativo existente
- ✅ **Excluir:** Remover um ativo do sistema

### **3. Gestão de Manutenção**
- ✅ **Cadastro de Manutenção:** Registrar manutenções realizadas
  - Descrição detalhada
  - Custo da manutenção
  - Ativo relacionado
  - Data/Hora automática
- ✅ **Histórico:** Visualizar todas as manutenções registradas

### **4. Relatórios e Exportação**
- ✅ **Geração de Relatórios em PDF:**
  - Relatório de inventário completo
  - Relatório de manutenções
  - Relatório por departamento
  - Relatório por categoria
- ✅ **Exportação de Dados:** Baixar dados em PDF formatado

### **5. Dados de Suporte (Pré-cadastrados)**
- ✅ **5 Departamentos:** TI, RH, Financeiro, Operações, Jurídico
- ✅ **5 Fornecedores:** Dell, Lenovo, HP, Apple, Cisco
- ✅ **5 Categorias:** Notebook, Desktop, Servidor, Switch, Monitor
- ✅ **5 Ativos de Teste:** Para demonstração de funcionalidades

---

## 🐛 Troubleshooting

### **Problema 1: "Conexão recusada" ao abrir http://localhost/**

**Solução:**
1. Verifique se o **Apache está rodando** no XAMPP Control Panel
2. Se não estiver, clique em **Start** para Apache
3. Aguarde 2-3 segundos
4. Atualize a página do navegador (F5)

---

### **Problema 2: "Erro de conexão com o banco de dados"**

**Solução:**
1. Verifique se o **MySQL está rodando** no XAMPP Control Panel
2. Se não estiver, clique em **Start** para MySQL
3. Aguarde 5-10 segundos (MySQL demora mais para iniciar)
4. Atualize a página do navegador

**Alternativa - Reiniciar MySQL:**
1. Clique em **Stop** para MySQL no XAMPP
2. Aguarde 3 segundos
3. Clique em **Start** novamente

---

### **Problema 3: Porta 80 (Apache) já está em uso**

**Solução:**
1. No XAMPP Control Panel, clique em **Config** para Apache
2. Procure pela linha: `Listen 80`
3. Altere para uma porta livre, como: `Listen 8080`
4. Salve o arquivo
5. Reinicie Apache
6. Acesse: `http://localhost:8080/itam-asset-manager/`

---

### **Problema 4: Porta 3306 (MySQL) já está em uso**

**Solução:**
1. No XAMPP Control Panel, clique em **Config** para MySQL
2. Procure pela linha: `port=3306`
3. Altere para: `port=3307`
4. Salve o arquivo
5. Reinicie MySQL
6. Edite `conexao.php` e altere a linha:
   ```php
   $host = 'localhost:3307';
   ```

---

### **Problema 5: phpMyAdmin não carrega (erro 404)**

**Solução:**
1. Verifique se Apache está rodando
2. Acesse: `http://localhost/dashboard/`
3. Procure pelo link para phpMyAdmin
4. Se ainda não funcionar, reinicie o XAMPP completamente

---

### **Problema 6: Não consegue fazer login (erro de validação)**

**Solução:**
1. Verifique se o banco de dados foi criado corretamente
2. No phpMyAdmin, verifique se a tabela `usuario` existe
3. Verifique se o usuário `admin@itam.com` existe na tabela
4. Tente fazer login novamente
5. Se persistir, consulte a seção [Verificar se o Banco foi Criado](#passo-5-verificar-se-o-banco-foi-criado-corretamente)

---

### **Problema 7: Erro 500 (Internal Server Error)**

**Solução:**
1. Verifique os logs do Apache: `C:\xampp\apache\logs\error.log`
2. Verifique os logs do PHP: `C:\xampp\php\logs\php_error.log`
3. Procure por mensagens de erro específicas
4. Comum: Verificar se `conexao.php` está configurado corretamente

---

### **Problema 8: Caracteres acentuados aparecem como "??????"**

**Solução:**
1. Verifique se o banco foi criado com `utf8mb4_unicode_ci`
2. Se não, recrie o banco seguindo os passos da seção [Passo 3](#passo-3-criação-e-configuração-do-banco-de-dados)
3. Verifique se `conexao.php` tem a linha:
   ```php
   $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
   ```

---

## 📂 Estrutura de Arquivos do Projeto

```
itam-asset-manager/
├── index.php                    # Front Controller (ponto de entrada)
├── conexao.php                  # Conexão PDO Singleton
├── README.md                    # Este arquivo
│
├── controllers/                 # Controladores MVC
│   ├── AuthController.php       # Autenticação e login
│   ├── AtivoController.php      # Gestão de ativos
│   ├── ManutencaoController.php # Gestão de manutenções
│   └── RelatorioController.php  # Geração de relatórios
│
├── models/                      # Modelos de Dados
│   ├── UsuarioModel.php         # Operações com usuários
│   ├── AtivoModel.php           # Operações com ativos
│   └── ManutencaoModel.php      # Operações com manutenções
│
├── views/                       # Camada de Apresentação
│   ├── auth/
│   │   └── login.php            # Tela de login
│   ├── ativos/
│   │   ├── listagem.php         # Listagem de ativos
│   │   └── cadastro.php         # Formulário de cadastro
│   ├── manutencao/
│   │   └── cadastro.php         # Formulário de manutenção
│   ├── relatorio/
│   │   └── index.php            # Relatórios
│   └── layout/
│       ├── header.php           # Cabeçalho (incluso em todas as views)
│       └── footer.php           # Rodapé (incluso em todas as views)
│
├── css/                         # Estilos CSS
│   └── custom.css               # Estilos personalizados
│
├── lib/                         # Bibliotecas Externas
│   └── fpdf/                    # Biblioteca para gerar PDFs
│
└── sql/                         # Scripts de Banco de Dados
    ├── schema.sql               # Criação de tabelas e estrutura
    └── seed.sql                 # Dados iniciais e de teste
```

---

## 💻 Comandos Úteis para Desenvolvimento

### **Reiniciar o XAMPP via CMD**

```cmd
cd C:\xampp
xampp_stop.bat
xampp_start.bat
```

### **Acessar MySQL via Terminal**

```cmd
cd C:\xampp\mysql\bin
mysql -u root -p itam_db
```

### **Executar um SQL diretamente**

```cmd
mysql -u root itam_db < C:\caminho\arquivo.sql
```

### **Backup do Banco de Dados**

```cmd
cd C:\xampp\mysql\bin
mysqldump -u root itam_db > C:\backup_itam_db.sql
```

### **Restaurar Backup**

```cmd
cd C:\xampp\mysql\bin
mysql -u root itam_db < C:\backup_itam_db.sql
```

---

## 📞 Suporte e Dúvidas

**Principais Pontos de Atenção:**

1. **Sempre inicie Apache E MySQL** antes de tentar acessar a aplicação
2. **Crie o banco na ordem correta:** primeiro schema.sql, depois seed.sql
3. **Use as credenciais exatas:** `admin@itam.com` / `admin123`
4. **Verifique a collation:** Deve ser `utf8mb4_unicode_ci` em tudo
5. **Está com problema?** Consulte a seção [Troubleshooting](#-troubleshooting)

---

## 📄 Licença e Informações Acadêmicas

Este projeto foi desenvolvido exclusivamente para fins acadêmicos como trabalho de conclusão (A3) do curso de **Análise e Desenvolvimento de Sistemas** do **CEUB**.

**Data de Criação:** Maio de 2026  
**Instituição:** Centro Universitário de Brasília (CEUB)  
**Curso:** Análise e Desenvolvimento de Sistemas (ADS)

---

## ✨ Agradecimentos

Agradecimentos especiais aos professores e mentores que orientaram o desenvolvimento deste projeto acadêmico, com foco em boas práticas de engenharia de software, segurança da informação e padrões internacionais de desenvolvimento.

* **E-mail de Acesso:** `admin@itam.com`
* **Senha:** `admin123`

---

> *Desenvolvido com foco em excelência e conformidade acadêmica. O ITAM Asset Manager reflete os mais altos padrões de ensino tecnológico do Centro Universitário de Brasília (CEUB).*

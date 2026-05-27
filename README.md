# ITAM Asset Manager
### Sistema de Gestão de Ativos de TI e Governança Corporativa

Este projeto consiste no sistema **ITAM Asset Manager**, desenvolvido como o projeto acadêmico (A3) da graduação em **Análise e Desenvolvimento de Sistemas (ADS)** do Centro Universitário de Brasília (**CEUB**). Trata-se de uma solução corporativa para inventário, controle de ciclo de vida e governança de ativos de Tecnologia da Informação.

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

## ⚙️ Roteiro de Deploy e Instalação (Banca Acadêmica)

Este sistema foi especificamente otimizado para rodar **sem qualquer atrito ou necessidade de configurações avançadas** no ambiente padrão do XAMPP local.

### 📋 Pré-requisitos
* **XAMPP v3.3.0** ou superior instalado no computador.
* **PHP v8.1** ou superior configurado.
* **MySQL/MariaDB** integrado no XAMPP.

---

### 🚀 Passo a Passo para Execução

#### Passo 1: Posicionamento dos Arquivos
Mova a pasta inteira do projeto `itam-asset-manager` para dentro do diretório raiz de servidores do XAMPP:
```
C:\xampp\htdocs\itam-asset-manager
```

#### Passo 2: Inicialização dos Módulos no Painel XAMPP
Abra o **XAMPP Control Panel** e inicialize os serviços necessários clicando em **Start**:
* **Apache**
* **MySQL**

#### Passo 3: Criação do Banco de Dados
1. Abra o seu navegador de preferência e acesse o gerenciador de banco de dados phpMyAdmin:
   [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/)
2. Clique em **"Novo"** no menu lateral esquerdo para criar um banco de dados.
3. Defina o nome do banco exatamente como: **`itam_db`**
4. No campo de agrupamento (Collation), selecione obrigatoriamente: **`utf8mb4_unicode_ci`**
5. Clique em **Criar**.

#### Passo 4: Importação Sequencial dos Scripts SQL
Com o banco `itam_db` selecionado no phpMyAdmin, navegue até a aba **"Importar"** (Import) e execute a importação dos arquivos SQL localizados na pasta `sql/` seguindo estritamente a ordem cronológica abaixo:
1. **Primeiro Script (Estrutura):** Selecione e execute a importação do arquivo **`sql/schema.sql`**. Este script cria a topologia das tabelas de forma estruturalmente testada.
2. **Segundo Script (Sementes/Dados):** Selecione e execute a importação do arquivo **`sql/seed.sql`**. Este script contém a massa de dados inicial necessária para homologação do projeto, incluindo as tabelas já populadas com criptografia Bcrypt nativa.

> ℹ️ *A importação foi estruturada para que as chaves estrangeiras (Foreign Keys) funcionem nativamente e sem quebras de integridade devido à correta hierarquia declarativa contida nos arquivos SQL.*

#### Passo 5: Inicialização e Acesso à Aplicação
No seu navegador, acesse o sistema utilizando o seguinte endereço local:
👉 **[http://localhost/itam-asset-manager/](http://localhost/itam-asset-manager/)**

---

## 🔑 Credenciais Padrão de Homologação

Para que a banca examinadora consiga testar todas as funcionalidades do painel administrativo, módulos de inventário corporativo e geração de relatórios em PDF, utilize as seguintes credenciais semeadas no banco:

* **E-mail de Acesso:** `admin@itam.com`
* **Senha:** `admin123`

---

> *Desenvolvido com foco em excelência e conformidade acadêmica. O ITAM Asset Manager reflete os mais altos padrões de ensino tecnológico do Centro Universitário de Brasília (CEUB).*

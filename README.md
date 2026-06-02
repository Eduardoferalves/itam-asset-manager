<div align="center">
  <img src="https://cdn-icons-png.flaticon.com/512/2920/2920261.png" alt="ITAM Logo" width="100">

  # ITAM | IT Asset Management

  **Sistema de Gestão de Ativos de TI e Governança Corporativa**

  [![PHP Version](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net/)
  [![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
  [![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
  [![License](https://img.shields.io/badge/Status-Release_v1.0-success?style=flat-square)](#)
</div>

---

## 📌 Visão Geral

Desenvolvido com foco estrito em conformidade corporativa e auditoria, o **ITAM (IT Asset Management)** é uma solução de gestão de ciclo de vida de hardwares. O sistema garante o rastreamento financeiro de manutenções e o controle de depreciação de equipamentos, implementando regras rigorosas de imutabilidade de banco de dados e proteção contra falhas de integridade referencial.

Projeto desenvolvido para a disciplina de Desenvolvimento de Sistemas (A3) do **Centro Universitário de Brasília (CEUB)**.

## Autores

| Nome | RA | E-mail |
|------|----|----|
| Eduardo Fernandes Alves | 22505804 | eduardo.feralves@sempreceub.com |
| Paulo Henrique Cardoso Rocha | 22503489 | paulorocha@sempreceub.com |
---

## 📋 Índice

1. [Engenharia e Arquitetura](#-engenharia-e-arquitetura)
2. [Governança e Segurança](#-governança-e-segurança)
3. [Funcionalidades Core](#-funcionalidades-core)
4. [Pré-requisitos](#-pré-requisitos)
5. [Deploy e Instalação](#-deploy-e-instalação)
6. [Estrutura do Projeto](#-estrutura-do-projeto)
7. [Equipe Técnica](#-equipe-técnica)

---

## 🏗️ Engenharia e Arquitetura

O sistema foi arquitetado utilizando o padrão **MVC (Model-View-Controller) Vanilla**, sem acoplamento a frameworks externos de back-end, garantindo alta performance e controle absoluto sobre o roteamento.

- **Front Controller:** Arquitetura centralizada no `index.php` atuando como *Whitelist* de rotas, prevenindo ataques de *Local File Inclusion (LFI)*.
- **Imutabilidade Financeira (Append-Only Log):** O módulo de manutenções atua como um livro-caixa. Os registros financeiros são imutáveis (sem suporte a *Update/Delete*), garantindo trilha de auditoria à prova de fraudes.
- **Integridade Referencial Estrita:** O banco de dados utiliza restrições `ON DELETE RESTRICT`. A aplicação intercepta violações (`PDOException 23000`) bloqueando *Hard Deletes* de ativos com histórico financeiro, forçando o *Soft Delete* (Inativação).
- **Design System (Single Source of Truth):** Interface baseada em **Bootstrap 5** com tipografia e paleta padronizadas (Glassmorphism UI), garantindo consistência visual em todas as *Views* (Listagens e Read-only Forms).

---

## 🔒 Governança e Segurança

A aplicação implementa contramedidas nativas contra os principais vetores de ataque listados pela **OWASP Top 10**:

| Vetor de Ataque | Mitigação Implementada |
| :--- | :--- |
| **SQL Injection** | `PDO Strict Mode` com *Prepared Statements* parametrizados em 100% das queries. |
| **Cross-Site Scripting (XSS)** | Sanitização de *Outputs* via `htmlspecialchars()` e tipagem rigorosa. |
| **Cross-Site Request Forgery (CSRF)** | Geração e validação de Tokens CSRF criptograficamente seguros em operações POST. |
| **Broken Access Control** | Bloqueio de roteamento e redirecionamento de usuários não autenticados via sessão segura. |
| **Session Hijacking** | Sessões configuradas com `HttpOnly`, `SameSite=Strict` e regeneração de ID. |

---

## ⚙️ Funcionalidades Core

- 🔐 **Gestão de Identidade:** Autenticação segura com *hash* de senhas (`bcrypt`/`argon2i`).
- 💻 **Inventário de Hardware:** CRUD completo de ativos, com controle de status, categorias, departamentos e fornecedores.
- 🛠️ **Log de Manutenções:** Registro de serviços vinculados a custos financeiros, blindados contra edição póstuma (*Defensive UI Modal*).
- 📊 **Relatórios Gerenciais (BI):** Geração dinâmica de faturas e consolidação de depreciação em PDF nativo via biblioteca **FPDF** (`LEFT JOIN` para cálculo de custos acumulados).
- 🔍 **Filtros Avançados:** Buscas combinadas por *Status* (ENUM), *Categoria* (FK) e *Patrimônio* (LIKE).

---

## 💻 Pré-requisitos

Para rodar a aplicação localmente, certifique-se de possuir o ambiente abaixo:

- **Servidor Web:** XAMPP 3.3.0+ (Apache) ou servidor embutido do PHP.
- **Linguagem:** PHP 8.1 ou superior (Extensão `PDO_MySQL` habilitada).
- **SGBD:** MySQL 5.7+ ou MariaDB (MySQL Workbench 8.0 CE recomendado para gestão).

---

## 🚀 Deploy e Instalação

### 1. Preparação do Ambiente

Clone este repositório para dentro do diretório público do seu servidor web (ex: `htdocs` no XAMPP):

```bash
cd C:\xampp\htdocs
git clone https://github.com/seu-usuario/itam-asset-manager.git
```

### 2. Configuração do Banco de Dados

Abra o MySQL Workbench e execute os scripts de infraestrutura na seguinte ordem estrita:

1. `sql/schema.sql` — Realiza o build do banco `itam_db` e suas tabelas.
2. `sql/seed.sql` — Injeta os dados mestre: Admin, Categorias, Departamentos e Fornecedores.

**Alternativa via CLI:**

```bash
mysql -u root -p < "C:\xampp\htdocs\itam-asset-manager\sql\schema.sql"
mysql -u root -p < "C:\xampp\htdocs\itam-asset-manager\sql\seed.sql"
```

### 3. Acesso à Aplicação

Certifique-se de que o Apache e o MySQL estão com o status **Running** no XAMPP e acesse via navegador:

```
http://localhost/itam-asset-manager
```

**Credenciais de Acesso (Geradas via Seed):**

| Campo | Valor |
| :--- | :--- |
| **Usuário** | `admin@itam.com` |
| **Senha** | `admin123` |

---

## 📂 Estrutura do Projeto

```
itam-asset-manager/
├── index.php                    # Front Controller / Roteador / Filtro CSRF
├── conexao.php                  # PDO Singleton Config
├── controllers/                 # Controladores de Domínio
│   ├── AtivoController.php      # Lida com restrições de deleção (PDOException)
│   ├── ManutencaoController.php # Garante regras de imutabilidade (Read-only)
│   └── ...
├── models/                      # Lógica de Negócio e Persistência
├── views/                       # Camada de Apresentação (Templates)
│   ├── layout/                  # Single Source of Truth para UI/UX
│   └── ...
├── sql/                         # DDL e DML do Banco de Dados
└── lib/fpdf/                    # Engine de geração de relatórios
```

---

## 🛠️ Troubleshooting

**Erro `Access denied for user`**
Verifique se as credenciais (usuário/senha) no arquivo `conexao.php` correspondem às do seu banco local.

**Layout quebrado ou "Página não encontrada"**
O sistema foi desenhado para rodar a partir da pasta `/itam-asset-manager`. Se você renomear a pasta raiz, as rotas relativas do `header.php` podem não localizar o `css/custom.css`.

**Erro 500 ao gerar PDF**
Verifique se as permissões de leitura/escrita na pasta `/lib/fpdf` estão ativas e se não há warnings no PHP bloqueando a saída de cabeçalhos.

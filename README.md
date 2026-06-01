# ITAM Asset Manager

Sistema de gestão de ativos de TI e governança corporativa desenvolvido como projeto acadêmico (A3) do curso de Análise e Desenvolvimento de Sistemas do Centro Universitário de Brasília (CEUB).

## Índice

1. [Autores](#autores)
2. [Pré-requisitos](#pré-requisitos)
3. [Instalação](#instalação)
4. [Uso](#uso)
5. [Arquitetura](#arquitetura)
6. [Segurança](#segurança)
7. [Funcionalidades](#funcionalidades)

## Autores

| Nome | RA | E-mail |
|------|----|----|
| Eduardo Fernandes Alves | 22505804 | eduardo.feralves@sempreceub.com |
| Paulo Henrique Cardoso Rocha | 22503489 | paulorocha@sempreceub.com |

## Pré-requisitos

- **XAMPP 3.3.0+** (Apache)
- **MySQL Workbench 8.0 CE** (Gerenciamento de BD)
- **PHP 8.1+**
- **MySQL/MariaDB 5.7+**
- **Navegador moderno** (Chrome, Firefox, Edge)

## Instalação

### 1. Clonar ou Extrair o Projeto

```bash
cd C:\xampp\htdocs
git clone <repositorio> itam-asset-manager
# ou copiar a pasta itam-asset-manager diretamente
```

### 2. Criar Banco de Dados (MySQL Workbench)

1. Abra **MySQL Workbench 8.0 CE**
2. Conecte-se ao seu servidor MySQL local
3. Abra uma nova Query (File > New Query Tab)
4. Importe os scripts SQL em sequência:
   - Abra `sql/schema.sql` e execute (cria banco e tabelas)
   - Abra `sql/seed.sql` e execute (insere dados de teste)

**Alternativa via linha de comando:**

```bash
cd C:\xampp\mysql\bin
mysql -u root -p < "C:\xampp\htdocs\itam-asset-manager\sql\schema.sql"
mysql -u root -p < "C:\xampp\htdocs\itam-asset-manager\sql\seed.sql"
```

### 3. Iniciar Apache

1. Abra **XAMPP Control Panel** (`C:\xampp\xampp-control.exe`)
2. Clique em **Start** para Apache
3. Aguarde o status ficar **Running** (em verde)

### 4. Acessar a Aplicação

Abra seu navegador e acesse:

```
http://localhost/itam-asset-manager
```

Você será redirecionado para a tela de login.

## Uso

**Credenciais Padrão:**

- **Email:** `admin@itam.com`
- **Senha:** `admin123`

## Arquitetura

A aplicação segue o padrão **MVC (Model-View-Controller)** com implementação nativa sem dependência de frameworks externos.

**Características principais:**

- **Front Controller** (`index.php`): Ponto de entrada único com roteamento dinâmico
- **MVC Nativo**: Model, View, Controller separados sem complexidade desnecessária
- **Padrão PRG**: Post-Redirect-Get para melhor experiência do usuário
- **PDO Strict Mode**: Prepared statements parametrizados em todas as operações
- **Singleton Pattern**: Gerenciamento centralizado de conexão (`conexao.php`)

## Segurança

A aplicação implementa contramedidas contra os principais vetores de ataque conforme OWASP Top 10:

| Vetor | Mitigação |
|-------|-----------|
| **SQL Injection** | Prepared statements parametrizados com PDO |
| **XSS** | Sanitização com `htmlspecialchars()` UTF-8 |
| **CSRF** | Tokens CSRF criptograficamente seguros |
| **Session Hijacking** | Cookies HttpOnly, SameSite=Strict, regeneração de sessão |

## Funcionalidades

- **Autenticação**: Login seguro com bcrypt
- **Gestão de Ativos**: CRUD completo (cadastrar, listar, editar, excluir)
- **Manutenção**: Registro e histórico de manutenções
- **Relatórios**: Geração de PDFs (inventário, manutenções, departamentos)
- **Gestão de Dados**: Departamentos, fornecedores, categorias pré-cadastrados

## Estrutura do Projeto

```
itam-asset-manager/
├── index.php                    # Front Controller
├── conexao.php                  # Conexão PDO Singleton
├── controllers/                 # Controladores
│   ├── AuthController.php
│   ├── AtivoController.php
│   ├── ManutencaoController.php
│   └── RelatorioController.php
├── models/                      # Modelos de dados
│   ├── UsuarioModel.php
│   ├── AtivoModel.php
│   └── ManutencaoModel.php
├── views/                       # Camada de apresentação
│   ├── auth/
│   ├── ativos/
│   ├── manutencao/
│   ├── relatorio/
│   └── layout/
├── css/                         # Estilos
├── lib/fpdf/                    # Biblioteca PDF
└── sql/                         # Scripts do banco
    ├── schema.sql
    └── seed.sql
```

## Troubleshooting

**Erro: "Conexão recusada"**
- Verifique se Apache está rodando no XAMPP Control Panel

**Erro: "Conexão com banco falhou"**
- Inicie MySQL Workbench e verifique a conexão
- Verifique se os scripts SQL foram executados

**Erro 500 (Internal Server Error)**
- Verifique `C:\xampp\apache\logs\error.log`
- Verifique se `conexao.php` está configurado corretamente

**Caracteres acentuados aparecem errados**
- Verifique se o banco foi criado com charset `utf8mb4_unicode_ci`

## Informações Acadêmicas

**Projeto:** A3 - Análise e Desenvolvimento de Sistemas  
**Instituição:** Centro Universitário de Brasília (CEUB)  
**Data:** Maio de 2026

---

Desenvolvido com foco em excelência e conformidade com padrões de engenharia de software.

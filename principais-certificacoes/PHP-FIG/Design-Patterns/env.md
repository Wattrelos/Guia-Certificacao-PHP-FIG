# 1. Arquivo .env
> O arquivo .env (Environment Variables) é utilizado para armazenar as credenciais e configurações confidenciais do projeto isoladas do código-fonte. Ele nunca deve ser enviado para o repositório de controle de versão (como o Git).
Abaixo está o exemplo prático de um arquivo .env estruturado para suportar conexões com PostgreSQL, MySQL ou MariaDB, seguindo o padrão de mercado adotado por grandes frameworks PHP (como o Laravel e Symfony).
Você deve manter as variáveis ativas apenas para o banco que estiver utilizando no momento:

```env
APP_NAME="Minha Aplicacao PHP"
APP_ENV=local
APP_KEY=base64:randomlygeneratedkeyhere
APP_DEBUG=true
APP_URL=http://localhost

# ==============================================================================
# CONFIGURAÇÃO DO BANCO DE DADOS ATIVO
# (Altere este valor para decidir qual conexão o PHP vai ler abaixo)
# Opções comuns: mysql, pgsql, mariadb
# ==============================================================================

DB_CONNECTION=mysql

# ------------------------------------------------------------------------------
# 🐬 OPÇÃO A: CONFIGURAÇÃO PARA MYSQL / MARIADB
# (Ambos utilizam o mesmo driver e porta padrão)
# ------------------------------------------------------------------------------

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_seu_banco_mysql
DB_USERNAME=root
DB_PASSWORD=sua_senha_secreta

# ------------------------------------------------------------------------------
# 🐘 OPÇÃO B: CONFIGURAÇÃO PARA POSTGRESQL
# (Caso queira usar o Postgres, mude DB_CONNECTION para "pgsql")
# ------------------------------------------------------------------------------

# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=nome_do_seu_banco_postgres
# DB_USERNAME=postgres
# DB_PASSWORD=sua_senha_secreta
# DB_SCHEMA=public
```
## 💡 Três regras de ouro ao usar arquivos .env

   1. Adicione ao .gitignore: Certifique-se de que a linha .env esteja listada no seu arquivo .gitignore para que suas senhas de produção nunca parem no GitHub.
   2. Crie um arquivo .env.example: Mantenha no repositório um arquivo de exemplo sem as senhas reais (ex: DB_PASSWORD=). Assim, novos desenvolvedores do time saberão quais variáveis precisam preencher ao clonar o projeto.
   3. Leitura no PHP: Para ler essas variáveis no PHP puro, você precisará de uma biblioteca padrão de mercado como a vlucas/phpdotenv (instalável via Composer), que expõe os valores através da superglobal $_ENV['DB_HOST'] ou da função getenv('DB_HOST').

⚠️ Nota Importante: Se você já adicionou e commitou um arquivo .env antes de colocar essas regras no .gitignore, o Git continuará rastreando as alterações dele. Para corrigir isso e forçar o Git a parar de rastreá-lo (sem apagá-lo do seu computador), rode o comando abaixo no terminal:git rm --cached .env


# Instalação — TalentMatch

## 1. Requisitos

| Requisito | Versão mínima |
|---|---|
| PHP | 8.1 (recomendado 8.2+) |
| Extensões PHP | `pdo_mysql`, `curl`, `mbstring`, `json`, `openssl` |
| MySQL | 8.0 (ou MariaDB 10.2+) |
| Navegador | Versão atual de Chrome, Firefox, Edge ou Safari |

Não é necessário Node.js, Composer ou Python.

## 2. Obter o código

```bash
git clone <URL_DO_REPOSITORIO> talentmatch
cd talentmatch
```

## 3. Configurar variáveis de ambiente

```bash
cp .env.example .env
```

Edite o arquivo `.env`:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=America/Sao_Paulo

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=talentmatch
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

DEEPSEEK_API_KEY=
DEEPSEEK_MODEL=deepseek-chat
DEEPSEEK_BASE_URL=https://api.deepseek.com
```

> O arquivo `.env` fica **fora da raiz pública** e **não** deve ser versionado.

## 4. Criar o banco de dados

```bash
mysql -u root -p -e "CREATE DATABASE talentmatch CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p -e "CREATE USER 'tm_user'@'localhost' IDENTIFIED BY 'uma_senha_forte';"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON talentmatch.* TO 'tm_user'@'localhost'; FLUSH PRIVILEGES;"
```

## 5. Importar os scripts SQL

```bash
mysql -u tm_user -p talentmatch < database/schema.sql
mysql -u tm_user -p talentmatch < database/seed_jobs.sql
```

- `schema.sql` cria as 13 tabelas e o administrador inicial.
- `seed_jobs.sql` popula o catálogo de vagas fictícias usado pelo fallback da IA.

## 6. Iniciar o servidor de desenvolvimento

```bash
php -S localhost:8000 -t public
```

Acesse **http://localhost:8000**. A página inicial mostra o diagnóstico do sistema:

- **Conectado** → o banco respondeu; tudo pronto para a próxima fase.
- **Sem conexão** → revise as credenciais no `.env` e confirme a importação do SQL.

## 7. Executar os testes

```bash
php tests/lint.php     # Sintaxe de todos os arquivos PHP
php tests/run.php      # Suíte completa
```

Se o banco estiver configurado, os testes de banco são executados; caso contrário,
são ignorados com um aviso.

## 8. Solução de problemas

| Sintoma | Causa provável | Solução |
|---|---|---|
| "Sem conexão" na página inicial | Credenciais incorretas no `.env` | Confira host, banco, usuário e senha |
| Erro de driver PDO | Extensão `pdo_mysql` ausente | Instale/habilite `pdo_mysql` no PHP |
| Página em branco | `APP_DEBUG=false` ocultando o erro | Veja `storage/logs/app-*.log` |
| Fontes não carregam | Sem acesso à internet | O layout usa fontes de sistema como fallback |

## 9. Próximos passos

Após confirmar a conexão, siga para a **Fase 2** (painel administrativo e criação de
salas). Consulte o [README](../README.md#status-do-desenvolvimento) para o andamento.

# Publicação na InfinityFree — TalentMatch

A InfinityFree é uma hospedagem compartilhada gratuita com **PHP** e **MySQL**.
Este guia descreve a publicação da aplicação.

## 1. Requisitos da hospedagem

- PHP 8 compatível (selecione a versão mais recente no painel).
- Extensões `pdo_mysql`, `curl` e `mbstring` habilitadas (padrão na InfinityFree).
- Um banco de dados MySQL criado no painel.
- HTTPS ativo no subdomínio/domínio.

## 2. Estrutura recomendada

Na InfinityFree, a conta possui um diretório `htdocs` (raiz pública). Coloque **todo o
conteúdo de `public/` dentro de `htdocs`** e **o restante fora dele**, junto à raiz da
conta:

```text
/ (raiz da conta)
├── app/            ← fora do htdocs
├── database/       ← fora do htdocs
├── docs/
├── storage/
├── tests/
├── .env            ← fora do htdocs
└── htdocs/         ← conteúdo de public/
    ├── index.php
    ├── admin/
    ├── api/
    └── assets/
```

Se não for possível manter arquivos fora de `htdocs`, os arquivos `.htaccess` incluídos
em `app/`, `database/` e `storage/` bloqueiam o acesso direto — **verifique esse
comportamento antes de publicar**.

## 3. Passo a passo

1. **Criar conta** em infinityfree.com e registrar um subdomínio.
2. **Criar o banco de dados** no painel (MySQL Databases). Anote:
   - host MySQL, nome do banco, usuário e senha.
3. **Configurar o `.env`** (no diretório raiz da conta, fora de `htdocs`):

   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://seu-subdominio.infinityfreeapp.com
   APP_TIMEZONE=America/Sao_Paulo

   DB_HOST=sqlXXX.infinityfree.com
   DB_DATABASE=epiz_XXXXXXX_talentmatch
   DB_USERNAME=epiz_XXXXXXX
   DB_PASSWORD=sua_senha

   DEEPSEEK_API_KEY=sua_chave
   DEEPSEEK_MODEL=deepseek-chat
   DEEPSEEK_BASE_URL=https://api.deepseek.com
   ```

4. **Importar o banco** pelo phpMyAdmin da InfinityFree:
   - Importe `database/schema.sql`.
   - Importe `database/seed_jobs.sql`.
5. **Enviar os arquivos** por FTP (FileZilla) ou pelo gerenciador de arquivos:
   - `public/*` → `htdocs/`
   - `app/`, `database/`, `docs/`, `storage/`, `tests/`, `.env` → raiz da conta.
6. **Ajustar permissões**: `storage/logs` e `storage/cache` precisam ser graváveis
   (geralmente `755`).
7. **Testar**:
   - Abra a URL raiz e confira o **diagnóstico** ("Conectado").
   - Faça login no painel e crie uma sala.
   - Teste o QR Code em um celular.

## 4. Verificações antes da apresentação

- [ ] `APP_DEBUG=false` em produção.
- [ ] `.env` acessível apenas pelo servidor (não pela URL).
- [ ] `database/*.sql` **não** acessíveis pela URL.
- [ ] Chave da DeepSeek configurada e funcionando (ou fallback ativo).
- [ ] HTTPS ativo (QR Code e câmera funcionam melhor em HTTPS).
- [ ] Ensaio completo com múltiplos celulares.

## 5. Limitações da hospedagem compartilhada

- Sem processos persistentes, WebSocket ou workers.
- Limite de conexões e de requisições: evite polling agressivo.
- A sincronização usa eventos HTTP + atualização manual como contingência.

## 6. Checklist de segurança da publicação

- [ ] Nunca versionar o `.env`.
- [ ] Nunca expor a chave da DeepSeek no frontend.
- [ ] Nunca deixar `APP_DEBUG=true` em produção.
- [ ] Confirmar que `app/`, `database/` e `storage/` não são acessíveis pela URL.

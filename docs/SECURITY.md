# Segurança e privacidade — TalentMatch

## 1. Princípios

1. **Nunca confiar no cliente.** Todo estado, identidade e pontuação são decididos no
   servidor.
2. **Minimizar dados.** Coletamos apenas apelido e informações profissionais.
3. **Proteger segredos.** Chaves e credenciais ficam no `.env`, fora da raiz pública.
4. **Falhar sem vazar.** Erros não expõem detalhes internos ao navegador.

## 2. Banco de dados

- **PDO** com **prepared statements** e `ATTR_EMULATE_PREPARES = false`.
- `ERRMODE_EXCEPTION` para capturar e registrar falhas.
- Transações nas operações críticas (geração de vagas, candidatura, cálculo).
- Índices únicos impedem duplicidade (candidaturas e resultados).

## 3. Entrada e saída

- **Validação no servidor** (`App\Core\Validator`) com limites de tamanho e listas de
  valores permitidos.
- **Saída escapada** com `htmlspecialchars` (helper `e()`) contra **XSS**.
- **CSRF**: token por sessão, verificado em toda escrita via campo `csrf_token` ou
  cabeçalho `X-CSRF-Token` (resposta `419` quando inválido).
- Respostas JSON sem `JSON_UNESCAPED_UNICODE` desnecessário e sem HTML.

## 4. Sessões e tokens

- Sessões com cookies `HttpOnly`, `SameSite=Lax` e `Secure` quando em HTTPS.
- Regeneração do ID de sessão no início.
- Token de participante gerado com `random_bytes(32)` e armazenado **somente como hash
  SHA-256**. O navegador não pode escolher o ID de outro participante.
- Limite de tentativas nas operações sensíveis (login e entrada em sala).

## 5. Proteção de arquivos

- `app/`, `database/` e `storage/` fora da raiz pública sempre que possível.
- Arquivos `.htaccess` bloqueiam acesso direto caso permaneçam sob `htdocs`.
- `.gitignore` impede o versionamento de `.env`, logs e dumps.

## 6. Chave da IA

- A chave da DeepSeek é lida de `app/config/ai.php` → `Env::get('DEEPSEEK_API_KEY')`.
- **Nunca** aparece em JavaScript, HTML, arquivos públicos ou respostas de endpoints.
- As chamadas à API partem exclusivamente do servidor, via cURL.

## 7. Privacidade dos participantes

| Coletamos | **Não** coletamos |
|---|---|
| Apelido | CPF, RG |
| Área profissional | Endereço, telefone, e-mail |
| Habilidades, experiências, formações | Dados médicos ou sensíveis |
| — | Currículos reais enviados à IA |

- À IA enviamos apenas **dados agregados** (áreas, habilidades recorrentes), nunca o
  perfil individual de uma pessoa.
- A finalidade é **educacional** e deve ser informada na interface.
- Dados podem ser apagados ao remover a sala (cascata) ou ao final da partida.

## 8. Avisos legais

- Todas as vagas, empresas e salários são **fictícios**.
- O sistema **não** é uma plataforma real de contratação.
- O resultado da compatibilidade **não** representa probabilidade de contratação.

## 9. Testes de segurança previstos (Fase 9)

- Entrada em sala inexistente ou encerrada.
- Token de participante inválido.
- Tentativa de alterar o participante de outra pessoa.
- Resposta duplicada a uma vaga.
- Resposta após o encerramento.
- Requisição sem token CSRF.
- Injeção de SQL e XSS nos campos de texto.
- Falha, timeout e chave inválida da IA (fallback).

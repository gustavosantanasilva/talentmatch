# Fluxo de Git — TalentMatch

## 1. Branches

| Branch | Uso |
|---|---|
| `main` | Código estável, pronto para publicação |
| `develop` | Integração das fases em desenvolvimento |
| `feature/<nome>` | Uma funcionalidade por branch |
| `fix/<nome>` | Correções pontuais |

## 2. Commits convencionais

Formato: `tipo(escopo): descrição no imperativo`

| Tipo | Uso |
|---|---|
| `feat` | Nova funcionalidade |
| `fix` | Correção de bug |
| `docs` | Documentação |
| `style` | Formatação, sem mudança de comportamento |
| `refactor` | Reestruturação sem mudar comportamento |
| `test` | Testes |
| `chore` | Configuração, dependências, tarefas |

Exemplos:

```
feat(rooms): criar sala com código aleatório e QR Code
fix(auth): impedir login com senha incorreta
docs(readme): documentar instalação local
test(database): verificar chaves estrangeiras do esquema
```

## 3. Versionamento semântico

`MAJOR.MINOR.PATCH`

- **MAJOR**: mudanças incompatíveis.
- **MINOR**: novas funcionalidades compatíveis.
- **PATCH**: correções compatíveis.

Cada fase concluída corresponde a um incremento de `MINOR` (ex.: `0.1.0` → `0.2.0`).

## 4. Fluxo de trabalho

```bash
git checkout -b feature/criacao-de-salas
# ... desenvolvimento e testes ...
git add .
git commit -m "feat(rooms): criar sala com código aleatório"
git push origin feature/criacao-de-salas
# abrir Pull Request para develop
```

## 5. Regras

- Nunca commitar `.env`, chaves de API, logs ou dumps de banco.
- Um commit = uma mudança coerente.
- Atualizar `CHANGELOG.md` e `README.md` quando a mudança afetar o usuário.
- Rodar `php tests/lint.php` e `php tests/run.php` antes de commitar.

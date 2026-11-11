# Contribuindo com o TalentMatch

Obrigado pelo interesse! Este é um projeto **educacional** de simulação de
recrutamento. Contribuições que melhorem o código, a documentação ou o experimento
sobre vieses são muito bem-vindas.

## Como contribuir

1. Faça um *fork* do repositório.
2. Crie uma branch descritiva: `git checkout -b feature/minha-melhoria`.
3. Siga o estilo do projeto (veja abaixo).
4. Rode os testes antes de commitar:
   ```bash
   php tests/lint.php
   php tests/run.php
   ```
5. Abra um *Pull Request* explicando o que mudou e por quê.

## Estilo de código

- **PHP 8.1+**, `declare(strict_types=1)`, tipos declarados e nomes claros.
- Comentários apenas quando agregam contexto.
- Sem dependências externas desnecessárias.
- Frontend mobile-first, seguindo o design system em `public/assets/css/app.css`.

## Regras do projeto

- **Nunca** commitar `.env`, chaves de API, logs ou dumps de banco.
- **Nunca** expor segredos no frontend.
- O **servidor** decide estado, identidade e pontuação — nunca o cliente.
- A IA **não** calcula notas nem escolhe vencedores; isso é feito em PHP.
- Toda funcionalidade deve ser validada no servidor e testada.
- Não apresentar funcionalidades planejadas como concluídas.

## Reportando problemas

Inclua: passos para reproduzir, comportamento esperado, comportamento observado,
navegador/versão do PHP e mensagens de log relevantes (sem dados sensíveis).

## Código de conduta

Seja respeitoso. O tema do projeto envolve **vieses e discriminação algorítmica** —
discussões devem ser construtivas e acolhedoras.

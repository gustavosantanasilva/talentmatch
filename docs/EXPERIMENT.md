# Experimento sobre vieses algorítmicos — TalentMatch

> **Status:** o módulo educacional será implementado na **Fase 8**. Este documento
> descreve o experimento planejado; a página web ainda não está disponível.

## 1. Objetivo

Demonstrar, de forma concreta, que **características pessoais irrelevantes não devem
alterar uma avaliação profissional** — e que algoritmos podem introduzir vieses quando
critérios inadequados entram no cálculo.

## 2. Metodologia

São criados **dois perfis fictícios** (A e B) **idênticos** em tudo o que é profissional:

- mesmas **habilidades** (nomes e níveis);
- mesmas **experiências**;
- mesma **formação**;
- mesma **área**.

Altera-se **apenas uma característica pessoal irrelevante** (por exemplo, um campo de
identificação fictício como o "nome do perfil"). Em seguida, o **mesmo algoritmo de
compatibilidade** é executado para os dois perfis, contra a mesma vaga fictícia.

## 3. O que o algoritmo usa

O cálculo (implementado em PHP) considera **somente**:

| Critério | Peso |
|---|---:|
| Habilidades obrigatórias | 50% |
| Habilidades desejáveis | 25% |
| Experiências relacionadas | 15% |
| Formação relevante | 10% |

Nenhum critério depende de idade, gênero, raça, deficiência, aparência ou qualquer
característica pessoal.

## 4. Resultado esperado

```
Perfil A  →  pontuação X
Perfil B  →  pontuação X     (idêntica)
```

As pontuações **devem ser iguais**. Se diferirem, há um **viés** no algoritmo — o que o
experimento pretende expor e discutir.

## 5. A página do experimento conterá

1. Descrição do experimento.
2. Os perfis fictícios lado a lado.
3. Os critérios e pesos utilizados.
4. As notas de cada critério e a nota final de cada perfil.
5. A conclusão educativa: por que dados irrelevantes não podem influenciar decisões.

## 6. Privacidade

O experimento **não** usa dados de visitantes reais. Os perfis são totalmente
fictícios e definidos no próprio código.

## 7. Mensagem final

> Um algoritmo justo é aquele em que **a mesma competência produz o mesmo resultado**,
> independentemente de quem a pessoa é.

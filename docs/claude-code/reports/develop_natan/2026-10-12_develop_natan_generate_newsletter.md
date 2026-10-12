# Relatório — execução do `/generate-newsletter-body`: edição 1.4.0 nas 5 línguas

- **Data**: 2026-10-12
- **Branch**: `develop_natan`
- **Task**: `generate_newsletter`
- **Skill**: `/generate-newsletter-body`
- **Antecessor**: `2026-09-25_develop_natan_generate_newsletter.md` (edição 1.3.0)
- **Depende de**: `2026-10-12_develop_natan_atualiza_relatorio_site.md` (relatório v0.0.16 → v0.0.17, concluído antes)

## 1. Pedido

`/generate-newsletter-body` sem argumentos: gerar `public/content/newsletter/{version}/newsletter_body.{lang}.md` nos 5 idiomas a partir do relatório de estado.

## 2. Versão

A versão veio do campo **Current version** do `relatorio.md` (versão do **CLI**), não do H1 do relatório (versão do **relatório**):

| | Valor |
| --- | --- |
| H1 do relatório (não usada) | `v0.0.17` |
| **Current version (usada)** | **`1.4.0`** |

`public/content/newsletter/1.4.0/` não existia — o diretório mais recente era `1.3.0/`. Portanto não houve sobrescrita e a pergunta obrigatória da skill não se aplicou.

## 3. Arquivos gerados

| Arquivo | Idioma | Bytes | Linhas |
| --- | --- | --- | --- |
| `newsletter_body.md` | EN | 7549 | 49 |
| `newsletter_body.pt_br.md` | PT-BR | 7648 | 49 |
| `newsletter_body.pt_pt.md` | PT-PT | 7965 | 49 |
| `newsletter_body.es.md` | ES | 8117 | 49 |
| `newsletter_body.fr.md` | FR | 8450 | 49 |

Estrutura (idêntica nos 5 — 4 títulos, 11 bullets, 4 cercas de código, 49 linhas):

```
# GitPR 1.4.0 — <Novidades|What's New|Novedades|Nouveautés>
## <Novidades desta versão | What's New in This Version | …>
   (11 bullets)
## <Como usar | How to Use | Cómo usar | Comment l'utiliser>
   (upgrade + 11 exemplos de comando + 3 parágrafos)
## <Dicas úteis | Useful Tips | Consejos útiles | Astuces utiles>
   (a dica)
```

## 4. Método: fatia verbatim, não retradução

Os bullets de "Novidades" são **fatiados verbatim do `relatorio.<lang>.md` daquele mesmo idioma** — nunca retraduzidos a partir do inglês. Isso é a convenção estabelecida nas edições 1.1.0/1.2.0/1.3.0 e está registrada na memória `newsletter-bullets-verbatim-e-dicas-obsoletas`.

Confirmado contra o precedente antes de gerar: na edição 1.3.0, os 13 bullets da newsletter são **byte-idênticos** aos 13 primeiros bullets da seção "What's New" do relatório de então, parando na linha `- **Current version:**`. A mesma fatia aplicada ao v0.0.17 produz **11 bullets**.

Só o bloco **"Como usar"** é autoral, e a dica vem verbatim do `tip_tools.json`.

### 4.1 Uma adaptação obrigatória: a referência `§Tests`

O bullet 9 do relatório termina, em cada idioma, com um ponteiro para uma seção interna do próprio relatório — que não faz sentido numa newsletter (não existe "§Tests" ali):

| Idioma | Removido |
| --- | --- |
| EN | `(see §Tests)` |
| PT-BR | `(ver §Testes)` |
| PT-PT | `(ver §Testes)` |
| ES | `(ver §Tests)` |
| FR | `(voir §Tests)` |

Remoção cirúrgica do parêntese final, com asserção de **exatamente 1** substituição por arquivo. É a única diferença entre a fatia publicada e o relatório — verificada bullet a bullet (§5, checagem 2).

## 5. Verificação

```
=== 1. structure parity ===
  en     lines= 49 headings=4 levels=####### bullets=11 fences=4  ref
  pt_br  lines= 49 headings=4 levels=####### bullets=11 fences=4  SAME
  pt_pt  lines= 49 headings=4 levels=####### bullets=11 fences=4  SAME
  es     lines= 49 headings=4 levels=####### bullets=11 fences=4  SAME
  fr     lines= 49 headings=4 levels=####### bullets=11 fences=4  SAME

=== 2. bullets verbatim from same-language report ===
  en/pt_br/pt_pt/es/fr  11 bullets byte-identical to relatorio.<lang> (minus the section-ref)

=== 3. leftovers that would break in an email ===
  all five: section-ref=0  html-tag=0  relative-md-link=0  site-abs-link=0  unfenced=0

=== 4. tip ===
  tip_23 used=True  source=docs/untracked-files.md   (presente verbatim nos 5)

=== 5. version consistency ===
  H1 carrega a versão do CLI nos 5; nenhum arquivo vazou a versão do relatório
```

Nota sobre a checagem 3: a primeira execução acusou 1 "html-tag" por arquivo. Diagnosticado como falso positivo — é o placeholder `<id>` de `gitpr baseline suppress <id> --reason`, dentro de uma cerca de código, onde o markdown o renderiza literalmente. O verificador foi corrigido para ignorar cercas antes de concluir; nenhum HTML real existe nos arquivos.

### 5.1 `menu.json` não foi alterado

A skill exige isso explicitamente. Comprovado por mtime: `menu.json` está em **12:55:58** (alteração da execução do `/sync-docs`, horas antes), enquanto a newsletter e o `tip_tools.json` estão em **15:23:32**. As 20 linhas que aparecem em `git diff public/content/menu.json` são as entradas dos 4 tópicos novos do `sync-docs`, não desta tarefa.

### 5.2 Suíte de testes

```
Tests: 8 failed, 50 passed (58) — Assertions: 222 — Duration: 4.46s
```

As mesmas 8 falhas pré-existentes de `Auth/*` e `ProfileTest` (`Expected 200 but received 404`), causadas pela rota catch-all [routes/web.php:27](routes/web.php#L27) que sombreia as rotas de auth. **Nenhuma falha nova.**

## 6. Efeito colateral resolvido

O `/update-relatorio` tinha deixado `NewsletterContent::version_from_relatorio()` resolvendo para `1.4.0` **sem** o diretório correspondente, o que fazia `body_markdown()` lançar `RuntimeException` e quebrava o `newsletter:send` sem `--version`. Isso está fechado — confirmado com `php artisan tinker`:

```
version_from_relatorio() -> '1.4.0'
  en     OK   7549 bytes  first line: # GitPR 1.4.0 — What's New
  pt_br  OK   7648 bytes  first line: # GitPR 1.4.0 — Novidades
  pt_pt  OK   7965 bytes  first line: # GitPR 1.4.0 — Novidades
  es     OK   8117 bytes  first line: # GitPR 1.4.0 — Novedades
  fr     OK   8450 bytes  first line: # GitPR 1.4.0 — Nouveautés
```

## 7. Dica consumida

| | |
| --- | --- |
| **id** | `tip_23` |
| **Fonte** | `docs/untracked-files.md` |
| **Tema** | o GitPR ignora arquivos não rastreados de propósito (`git diff HEAD`) — um `.env.local` esquecido nunca vai para a IA |

**Banco após esta edição: 8 usadas, 18 livres.**

A escolha foi filtrada por validade, não só por disponibilidade. O banco tem 26 dicas e **nenhuma** cobre as features da 1.4.0 (`baseline`, `policy`, `risk`, `mentor`, ledger SQLite) — ele antecede este release. Foram verificadas as 19 livres e **quatro estão factualmente obsoletas**, uma delas por causa desta própria release:

| id | Problema | Evidência |
| --- | --- | --- |
| `tip_16` | Usa `gitpr --metrics --dashboard` e `--metrics --export` — **flags removidas na 1.4.0** (quebra de CLI declarada) e diz que os dados ficam em `~/.gitpr/metrics/`, quando agora é o ledger `telemetry.db` | `relatorio.md` §3 + `docs/metricas-telemetria.md` |
| `tip_9` | Descreve hot-swap de binário com `.exe.old` e `gitpr -u`; o canal de binário foi removido e o updater só consulta e informa | "binary channel removed" no relatório; §8 |
| `tip_12` | Diz "12 AI tools"; o MCP está em **16** ferramentas | `relatorio.md:44` ("**16 annotated tools** 🆕, **19 resources** 🆕") |
| `tip_17` | Lista os templates como se ficassem na raiz do projeto; hoje ficam em `.gitpr/skill/` | `docs/skill-template.md` L15 |

`tip_16` é o caso mais grave — a dica está errada justamente por causa da manchete desta edição. Recomendação: rodar `/update-tip-tools` antes da próxima edição para (a) corrigir ou aposentar as quatro e (b) colher dicas das docs novas (`baseline-suppressions`, `policy-packs`, `risk-scoring`, `mentor-mode`), que hoje não têm nenhuma dica no banco.

## 8. Estado final

- 5 arquivos em `public/content/newsletter/1.4.0/`, 49 linhas cada, estrutura e contagens idênticas.
- 11 bullets por arquivo, byte-idênticos ao relatório do respectivo idioma (menos o ponteiro `§`, removido em todos).
- `tip_23` marcada `used=true` — diff de **1 linha** no `tip_tools.json`.
- `menu.json` não tocado por esta tarefa.
- Nenhuma falha de teste nova.
- Próximo passo: `php artisan newsletter:send` (sem `--version`) já resolve para 1.4.0 e funciona.

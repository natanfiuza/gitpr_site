# Relatório — Geração do corpo da newsletter 1.3.0 (generate-newsletter-body)

- **Data**: 2026-09-25
- **Branch**: `develop_natan`
- **Task**: `generate_newsletter`
- **Skill**: `/generate-newsletter-body`

## Contexto

Run do `/generate-newsletter-body`: gerar o corpo da newsletter da versão **1.3.0** do GitPR CLI nos 5 idiomas do site, a partir do relatório de status.

- **Versão determinada**: `Current version: 1.3.0 (bump in the working tree — HEAD at 1.2.0; last tag v1.2.0)` em `public/content/relatorio.md` → regex `\d+\.\d+\.\d+` → **1.3.0**. É a versão do CLI, não a do arquivo de relatório (H1 `v0.0.16`).
- **Colisão**: `1.3.0` **não existia** em `public/content/newsletter/` (existiam 0.0.35, 0.0.36, 0.0.37, 1.0.0, 1.1.0, 1.2.0) — nenhuma sobrescrita foi necessária, e o passo 4 da skill (perguntar antes de sobrescrever) não foi acionado.
- **Fonte**: bloco "What's New in This Version" do `relatorio.*.md`, lido **no idioma correspondente** (o vocabulário de cada tradução veio do próprio relatório daquele idioma, não de uma retradução a partir do inglês).

## O que foi feito

### 1. Arquivos gerados

`public/content/newsletter/1.3.0/` — 5 arquivos, 53 linhas cada, mesma estrutura de seções:

| Arquivo | H1 | Bytes |
| --- | --- | --- |
| `newsletter_body.md` | `# GitPR 1.3.0 — What's New` | 7878 |
| `newsletter_body.pt_br.md` | `# GitPR 1.3.0 — Novidades` | 8133 |
| `newsletter_body.pt_pt.md` | `# GitPR 1.3.0 — Novidades` | 8459 |
| `newsletter_body.es.md` | `# GitPR 1.3.0 — Novedades` | 8528 |
| `newsletter_body.fr.md` | `# GitPR 1.3.0 — Nouveautés` | 8934 |

Estrutura (idêntica nos 5, com os títulos de seção na convenção já usada pelas newsletters 1.1.0 e 1.2.0):
`## Novidades desta versão` / `## Como usar` / `## Dicas úteis` (e equivalentes em en/es/fr), com **13 bullets** de novidades em cada.

### 2. Como os bullets foram produzidos

Os 13 bullets **não foram reescritos**: foram fatiados verbatim do bloco "Novidades desta versão" de cada `relatorio.*.md` por um script de geração (`gen_newsletter_130.py`), com duas asserções por linha — toda linha precisa começar com `- **` e nenhuma pode conter `§`. A verificação posterior confirmou igualdade byte a byte entre os bullets publicados e os do relatório, nos 5 idiomas.

Isso evita o risco de drift de transcrição em ~24 KB de prosa e mantém a paridade entre as 5 edições por construção, em vez de por conferência manual. O único texto autorado nesta tarefa é o bloco "Como usar" (prosa + 3 cercas de código) e a dica, ambos em 5 idiomas.

O script ficou fora do repositório (`%TEMP%\gen_newsletter_130.py`) — não é artefato versionado.

### 3. Novidades publicadas (13)

| Novidade | Origem no relatório |
| --- | --- |
| `gitpr demo` — tour guiado sem chave de API, sem repositório e sem rede | §28 |
| Selo GitPR no corpo do PR + comando `gitpr badge` | §29 |
| `gitpr split` — um commit atômico por concern, árvore intacta | §30 |
| Varredura de segredos embutida (7 regras, não sobrescrevíveis) | §31 |
| Suporte a `extensions: ["*"]` | §6 (Linter) |
| Bridges SAST opt-in — Semgrep, Gitleaks e Bandit | §32 |
| `gitpr tests generate` — suíte na convenção do repositório | §33 |
| `gitpr explain` + flag `--explain` — guia do revisor | §34 |
| Arquitetura em camadas (`src/domain/` + `src/application/`) | §"Base Architecture" |
| Suíte determinística e o primeiro CI | §35 |
| As 3 falhas herdadas fechadas | §"Tests and Quality" |
| Dívida nova e concentrada (40 chaves `__()`, 4 falhas) | §33, §34, §"Tests and Quality" |
| Estado do release 1.3.0 | Overview / "Distribution Pipeline" |

### 4. Seção "Como usar"

Só o essencial, na mesma escala da 1.2.0: `pip install --upgrade gitpr-cli`, o tour (`gitpr demo`, `--lang`, `--no-tui`) e os 4 subcomandos novos — `badge`, `split`, `tests generate` e `explain`/`--explain` — todos anotados como somente-leitura até a opção que escreve (`--apply`). Dois parágrafos de fechamento registram o efeito colateral do `split --apply` sobre o índice, a confirmação com **No/Não** pré-selecionado do `tests generate`, e a **mudança de comportamento** da varredura de segredos (um commit que passava pode ser bloqueado), com as duas saídas de emergência (`GITPR_LINTER_SECURITY`, `GITPR_SAST_*_ENABLED`).

As opções foram conferidas contra o relatório (§28–§34), que é a fonte desta tarefa — nenhuma flag foi inventada. Os valores de `--lang` usados são os códigos canônicos do i18n (`pt_br`, `pt_pt`, `es_es`, `fr_fr`, `en_us`), não as formas curtas.

### 5. Dica consumida

| Campo | Valor |
| --- | --- |
| **id** | `tip_14` |
| **source** | `docs/github-ci-linter.md` |
| **tema** | linter gratuito como quality gate no GitHub Actions, bloqueando segredos antes da revisão humana (exit 0/1) |
| **marcada** | `used: false` → **`used: true`** em `public/content/tip_tools.json` |

Escolhida por ser a dica livre **simultaneamente correta e alinhada ao tema da versão**: o release 1.3.0 tem como manchete a varredura de segredos embutida, e a dica descreve exatamente esse linter como portão de CI. Banco após o consumo: **7 usadas, 19 livres**.

Descartes relevantes entre as 20 candidatas — dicas que a evolução do projeto tornou **imprecisas**, e que teriam publicado informação errada para os inscritos:

- `tip_9` (`docs/auto-update.md`): fala do binário trocado em *hot-swap* com `.exe.old`. O canal de binário foi **removido** — a distribuição é PyPI (`pip install gitpr-cli`).
- `tip_12` (`docs/mcp-integration.md`): cita "12 AI tools"; o MCP está em **14** ferramentas desde a 1.2.0.
- `tip_17` (`docs/skill-template.md`): descreve templates "no projeto" sem citar `.gitpr/skill/` e lista seis tipos, ignorando `tests` e `explain`.

## Verificação

| Checagem | Resultado |
| --- | --- |
| Bullets idênticos ao relatório de origem, nos 5 idiomas | OK — igualdade byte a byte (`13` bullets por arquivo) |
| Estrutura idêntica nos 5 arquivos (3 seções, 13 bullets, 53 linhas, 3 blocos de código) | OK |
| Resolução pelo código da aplicação (`NewsletterContent::version_from_relatorio()` + `body_markdown()`) | **1.3.0** resolvido; os 5 idiomas carregam com o H1 correto |
| Suíte da aplicação | `./vendor/bin/pest tests/Feature/NewsletterSendCommandTest.php` → **6 passed, 17 assertions** |
| `public/content/menu.json` inalterado por esta tarefa | OK — o diff de 10 linhas é do `/sync-docs` (mtime 2026-09-24 22:57, anterior a esta run) |
| Dica marcada `used=true` | OK — `tip_14`, JSON válido (26 dicas) |
| Line endings | LF (`CR=0` nos 5 arquivos), conforme `* text=auto eol=lf` do [.gitattributes](.gitattributes) |
| Encoding | UTF-8 sem BOM, `0` ocorrências de U+FFFD nos 5 arquivos |
| Conteúdo compatível com e-mail | Markdown puro, sem HTML embutido nem classes CSS; blocos de código em cercas simples, como nas edições anteriores |

## Arquivos alterados

```
public/content/newsletter/1.3.0/newsletter_body.md          (novo)
public/content/newsletter/1.3.0/newsletter_body.pt_br.md    (novo)
public/content/newsletter/1.3.0/newsletter_body.pt_pt.md    (novo)
public/content/newsletter/1.3.0/newsletter_body.es.md       (novo)
public/content/newsletter/1.3.0/newsletter_body.fr.md       (novo)
public/content/tip_tools.json                               (tip_14: used=false → true)
docs/claude-code/reports/develop_natan/2026-09-25_develop_natan_generate_newsletter.md  (este relatório)
```

## Observações

- **A release 1.3.0 ainda não foi fechada.** O relatório registra que `__version__` (1.3.0) e `__lang_version__` (v0.0.32) estão no working tree, **sem commit** (HEAD em 1.2.0 / v0.0.31), e que a entrada `[1.3.0]` do `CHANGELOG.md` cobre **apenas** a varredura de segredos. A newsletter foi gerada mesmo assim, porque a versão do CLI citada no relatório é 1.3.0 e o diretório não existia — mas **o envio deve esperar o fechamento da release**, senão os inscritos recebem novidades que ainda não estão no PyPI. O bullet "Estado do release 1.3.0" declara essa pendência explicitamente nas 5 edições, em vez de anunciar um lançamento que não ocorreu.
- **Dívida declarada dentro da própria newsletter.** Duas das 13 novidades são honestas sobre problemas abertos: as **40 chaves `__()` sem tradução** e as **4 falhas** na suíte (registro de skills pela metade em `tests` e `explain`). Foram mantidas porque fazem parte do bloco "Novidades desta versão" do relatório fonte e porque a convenção das edições anteriores é declarar o estado real — a 1.2.0 fez o mesmo com "o bump ainda não foi commitado". Vale registrar a tensão: são itens voltados ao mantenedor, não ao usuário final.
- **A newsletter 1.3.0 destrava o envio.** Pelo `NewsletterContent::body_markdown()`, o comando `newsletter:send` sem `--version` lança `RuntimeException` quando não existe corpo para a versão resolvida — o que era o caso de `1.3.0` desde a atualização do relatório (`/update-relatorio`, run anterior de hoje). Com os 5 arquivos no lugar, o caminho volta a resolver.
- **Nenhum arquivo de documentação foi alterado nesta tarefa** — as mudanças em `public/content/docs/` e em `menu.json` que aparecem em `git status` são do `/sync-docs`, executado antes.

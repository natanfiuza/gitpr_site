# Geração do Corpo da Newsletter — GitPR 1.6.0

- **Data:** 2026-11-08
- **Branch:** `develop_natan`
- **Skill:** `.claude/skills/generate-newsletter-body`
- **Fonte:** `public/content/relatorio*.md` (v0.0.23, publicado pela skill `update-relatorio`)

## Versão

| | Valor |
|---|---|
| Versão do relatório | v0.0.23 (2026-11-08) |
| Versão do CLI citada | **1.6.0** |
| Versão da newsletter | **1.6.0** (resolvida do campo *Current version*, não do H1) |

`public/content/newsletter/` parava na `1.5.0` — o diretório `1.6.0/` foi criado do zero, sem sobrescrever nada.

## Arquivos gerados (5)

| Arquivo | Idioma | Linhas |
|---|---|---|
| `public/content/newsletter/1.6.0/newsletter_body.md` | EN | 33 |
| `public/content/newsletter/1.6.0/newsletter_body.pt_br.md` | PT-BR | 33 |
| `public/content/newsletter/1.6.0/newsletter_body.pt_pt.md` | PT-PT | 33 |
| `public/content/newsletter/1.6.0/newsletter_body.es.md` | ES | 33 |
| `public/content/newsletter/1.6.0/newsletter_body.fr.md` | FR | 33 |

Estrutura idêntica à da edição 1.5.0: H1 na linha 1, três H2 nas linhas 3/12/31 e término com
newline único. Sem front matter, emoji, separador ou rodapé; sem HTML/CSS embutido.

## Conteúdo

Seis bullets, fundindo os oito itens de "What's New" do relatório v0.0.23:

1. Forge por repositório — `src/scm_settings.py`, um slot de credencial por forge e migração
   idempotente das chaves legadas (ADR-016).
2. Fecho de issue multi-forge — `get_issue`/`get_default_branch`/`closing_line`, Azure DevOps
   em `not-supported`, registro em `.gitpr/conf/issue.yml` (ADR-010).
3. `gitpr seed` / `-sd` e o wizard de instalação em 5 etapas.
4. TUI: categoria Smart Excludes e categoria `check`; `DEFAULT_CONFIG` derivado de
   `ConfigField.seeded`.
5. Token Trend + Latency Histogram no CLI/TUI/MCP, `collapse_binary_hunks()` e 100+ padrões de
   smart excludes.
6. Guarda de TTY (`_may_ask_the_user()`) desbloqueando a suíte — **3422 cenários em 159
   arquivos** — e o estado da release: 1.5.0 no PyPI, 1.6.0 **sem tag e sem publicação**.

Ponteiros de seção do relatório (`section 47`, `secção 47`) foram removidos, como nas edições
anteriores. Os links das ADRs usam URLs absolutas do GitHub, no mesmo padrão da 1.5.0.

**Como usar:** estado da release e `pip install --upgrade gitpr-cli`; `gitpr seed` (idempotente,
`--force`, `--all`); e o exemplo YAML da seção `scm` versionada em
`.gitpr/conf/gitpr.config.yml`, com a explicação de que `scm.provider` só é gravado quando a
forge escolhida difere da detectada no `origin`.

## Dica consumida

| id | Fonte | `used` |
|---|---|---|
| `tip_30` | `docs/scm-multiforge.md` | `false` → **`true`** |

Texto copiado **verbatim** de `tip_30.content.<lang>` para a terceira seção de cada arquivo
(parágrafo único, sem id nem rótulo). O banco passou de 27 para **26 dicas não usadas** de 40.

## Verificação

| Checagem | Resultado |
|---|---|
| Linhas por arquivo | 33 nos 5, newline final único |
| H1 contém `1.6.0` | sim nos 5 |
| Trilha de H2 | `[1, 3, 12, 31]` nos 5, nomes no idioma correto |
| Bullets `- **…**` | 6 nos 5 |
| Dica byte-idêntica ao `tip_tools.json` | sim nos 5 idiomas |
| `tip_tools.json` válido / `tip_30.used` / restantes | OK / `true` / 26 |
| `NewsletterContent::body_markdown('1.6.0', …)` | EN 4199 B, FR 4634 B, idioma desconhecido cai no EN |
| `version_from_relatorio()` | `1.6.0` |
| `./vendor/bin/pest tests/Feature/NewsletterSendCommandTest.php` | 6/6 verdes, 17 asserções |
| `public/content/menu.json` | não alterado nesta tarefa (mtime anterior, 08:00:29) |

## Observações

- **1.6.0 ainda não foi publicada.** O texto de "Como usar" avisa que o PyPI serve a 1.5.0 até
  a tag `v1.6.0` passar pelo `release.yml`; convém revisar esse parágrafo quando a release sair.
- Edições anteriores (`1.4.x`, `1.5.0`), `relatorio*.md`, `public/content/docs/*` (sync-docs em
  andamento) e `menu.json` não foram tocados.
- `git status` mostra `public/content/menu.json` modificado desde antes desta tarefa (WIP do
  sync-docs), não por causa dela.

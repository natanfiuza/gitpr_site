# Atualização do banco de dicas — 2026-10-27

- **Branch:** `develop_natan`
- **Skill:** `update-tip-tools`
- **Arquivo:** `public/content/tip_tools.json`
- **Fontes varridas:** `public/content/docs/**/*.md` (excluindo `newsletter/**` e `relatorio*.md`)

## Resumo

| | Antes | Depois |
|---|---|---|
| Dicas no banco | 26 | **40** |
| Adicionadas | — | **14** |
| Corrigidas | — | **4** |
| `used=true` | 10 | **10** (mesmos ids) |
| Livres para a próxima edição | 16 | **30** |

Nenhuma dica usada voltou para `used=false`: os 10 ids já consumidos (`tip_1`, `tip_2`, `tip_3`, `tip_5`, `tip_7`, `tip_11`, `tip_14`, `tip_21`, `tip_23`, `tip_24`) continuam intocados.

## Correções

Quatro dicas descreviam comportamento que não existe mais. Todas estavam com `used=false`, então nenhuma newsletter as publicou.

| id | Estava | Está agora |
|---|---|---|
| `tip_9` | Auto-update com hot-swap de binário, `.exe.old` e rollback automático (canal binário removido) | Checagem diária no PyPI que **bloqueia** a execução, sem modo de compatibilidade; `-u` só imprime o comando; isenções `--quiet`/`--hook`/`--mcp`/`-h`; offline roda normal |
| `tip_12` | "12 AI tools" e diagnósticos no stderr | `--tool`/`--tool-args` sem contagem fixa; `--tool` sozinho lista as ferramentas; em modo `--tool` a saída é só JSON e o stderr fica vazio |
| `tip_16` | `--metrics --dashboard` / `--metrics --export` (flags removidas na 1.4.0) | `gitpr metrics dashboard` / `gitpr metrics export`, mais `bundle`/`merge` para consolidar o time sem servidor |
| `tip_17` | Seis templates na raiz do projeto | Oito templates em `.gitpr/skill/` (inclui `.gitpr.release.md` e `.gitpr.linter.yml`), `--skill` nunca sobrescreve, `gitpr config` edita |

## Dicas adicionadas

| id | Fonte | Tema |
|---|---|---|
| `tip_27` | `docs/check-ci.md` | `gitpr check`: CI único com SARIF/JSON/JUnit, `--no-ia`, `--ia-only-high-risk` e exit codes 0/1/2 |
| `tip_28` | `docs/risk-scoring.md` | `gitpr risk`: score local 0–100 sem rede nem tokens, `--file`, `--format json`, `GITPR_RISK_INCLUDE_IN_REVIEW` |
| `tip_29` | `docs/suggested-reviewers.md` | Revisores sugeridos por `git blame` nas linhas adicionadas, ranking 50/30/20, bots excluídos |
| `tip_30` | `docs/scm-multiforge.md` | Quatro forges (GitHub/GitLab/Bitbucket/Azure DevOps), auto-detecção pelo `origin`, wizard `--init` |
| `tip_31` | `docs/split-command.md` | `gitpr split`: hunks agrupados em commits atômicos, dry run por padrão, `--apply`/`--max-groups` |
| `tip_32` | `docs/fix-command.md` | `gitpr fix`: findings da última revisão viram patches, classificação de confiança, `--rollback` |
| `tip_33` | `docs/baseline-suppressions.md` | `gitpr baseline`: adoção em repositório legado, só `new` bloqueia, decisões auditáveis e checksum |
| `tip_34` | `docs/mentor-mode.md` | `--mentor` / `gitpr mentor`: didática para júnior, top 10 por severidade |
| `tip_35` | `docs/explain-pr.md` | `gitpr explain`: guia do revisor, `--explain` no PR, `GITPR_EXPLAIN_BY_DEFAULT` |
| `tip_36` | `docs/tests-generate.md` | `gitpr tests generate`: framework detectado, `--finding`, `--apply` |
| `tip_37` | `docs/release-notes.md` | `gitpr release`: seção do `CHANGELOG.md`, bump semântico, resumo por IA, `--publish` |
| `tip_38` | `docs/usage-log.md` | Log de uso: um arquivo por dia com nome `uuid5` da data, sem diff nem credenciais |
| `tip_39` | `docs/badge.md` | Badge do PR com as contagens do linter, `gitpr badge --readme` |
| `tip_40` | `docs/policy-packs.md` | Policy Pack: política do time versionada em `.gitpr/policy.lock.yml` |

As 14 cobrem justamente o buraco apontado na edição 1.4.2: o banco antecipava a 1.4.0 e não tinha `baseline`, `policy`, `risk`, `mentor`, `split`, `fix`, `check` nem as duas features documentadas na 1.4.2 (`explain` e `tests generate`). Agora cobre.

## Verificação

| Checagem | Resultado |
|---|---|
| JSON válido, 4 espaços de indentação | sim (482 linhas) |
| Ids sequenciais `tip_1`…`tip_40`, sem colisão | sim |
| Toda dica com os 5 idiomas preenchidos | sim |
| `used` preservado (nenhum `true` → `false`) | 10/10 |
| Chaves na ordem `en, pt_br, pt_pt, fr, es` | sim |
| `source` aponta para arquivo existente | 40/40 |
| Vazamento de idioma entre campos | 0 |
| `public/content/menu.json` alterado | **não** |
| Frases obsoletas (`12 AI tools`, `.exe.old`, `--metrics --dashboard`, `--metrics --export`) | 0 ocorrências |

Terminologia alinhada às traduções do próprio site: ES `hallazgo`/`migraciones`/`deuda`, FR `constat`/`migration`/`dette`, e `dry run` mantido em ES/FR como nos docs. Em PT usei `finding` (26 ocorrências nos docs contra 10 de `achado`), exceto onde o texto ficaria artificial.

## Observações

- Nenhuma dica nova foi escrita a partir de suposição: todas saem de um trecho dos docs listados em `source`.
- `tip_4` (`--pre-save`), `tip_22` (`gitpr --status`) e `tip_7` (`-ht`/`-b`) foram reverificados e continuam válidos.
- `tip_19` e `tip_21` usam `source` sem o prefixo `docs/` porque apontam para páginas do site (`providers.md`, `i18n.md`), não para `docs/`; mantidos como estavam.
- O banco agora tem 30 dicas livres — folga para ~30 edições no ritmo de uma por newsletter.

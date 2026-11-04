# Atualização do Relatório de Status do Site

- **Data:** 2026-11-04
- **Branch:** `develop_natan`
- **Skill:** `.claude/skills/update-relatorio`
- **Fonte:** `gitpr/docs/reports/relatorio_estado_v0.0.22.md`

## Versão

| | Antes | Depois |
|---|---|---|
| Relatório | v0.0.21 (2026-10-29) | **v0.0.22 (2026-11-04)** |
| CLI citada | 1.4.4 | **1.5.0** (tagueada, ainda não publicada no PyPI) |
| Dicionários de idioma | v0.0.36 | **v0.0.38** |

## Arquivos alterados (5)

| Arquivo | Idioma | Linhas |
|---|---|---|
| `public/content/relatorio.md` | EN | 922 |
| `public/content/relatorio.pt_br.md` | PT-BR | 922 |
| `public/content/relatorio.pt_pt.md` | PT-PT | 922 |
| `public/content/relatorio.es.md` | ES | 922 |
| `public/content/relatorio.fr.md` | FR | 922 |

Diff total: **+1260 / −865**.

## Método

O relatório fonte cresceu de 843 para 922 linhas, com 101 blocos alterados
(**251 linhas novas, 172 removidas**). Em vez de retraduzir 900+ linhas por idioma — o que
descartaria traduções já revisadas e abriria espaço para drift —, as cinco variantes foram
atualizadas por **delta**:

1. **PT-BR:** reprodução byte a byte do relatório fonte (`git show HEAD:` → LF). Essa é a
   convenção do arquivo, confirmada por checksum em janelas anteriores. Efeito colateral
   positivo: corrigiu 4 linhas em que o PT-BR tinha ficado atrás do fonte desde a v0.0.21
   (linha de CI/CD e o item de automação do release).
2. **EN, ES, FR, PT-PT:** cada bloco alterado foi traduzido isoladamente, reaproveitando o
   texto já aprovado quando a fonte mantinha a mesma frase. Os blocos foram aplicados por
   script sobre as linhas de índice correspondentes (843 linhas casavam 1:1), portanto
   **toda linha não alterada permaneceu idêntica byte a byte**.

## Verificação

| Checagem | Resultado |
|---|---|
| Linhas por arquivo | 922 nos 5 |
| Estrutura (`##`/`###`/`---`/linhas de tabela/marcadores 🆕) | idêntica ao fonte em todos: 8 / 46 / 8 / 188 / 88 |
| Resíduo de português em EN/ES/FR | nenhum (os `dos`/`é` encontrados são palavras legítimas de espanhol/francês) |
| Cabeçalhos | cada arquivo com seu título, versão v0.0.22 e data 2026-11-04 |
| Quebras de linha duras (dois espaços finais) | preservadas |
| `./vendor/bin/pest tests/Feature/NewsletterSendCommandTest.php` | 6/6 verdes |

## Conteúdo novo incorporado

- `gitpr -is` registra a issue em `.gitpr/conf/github.yml`; `--close-issue` / `--no-close-issue` /
  `GITPR_ISSUE_AUTOCLOSE`; pacote `src/issue_autoclose/` e ADRs 001–007.
- Base do PR versionada em `.gitpr/conf/gitpr.config.yml`, resolvedor `src/project_config.py`,
  modal `PrBaseApp` e ADR-015.
- Dívida de i18n paga: 1698 chaves × 6 dicionários, `__lang_version__` v0.0.38, extrator por AST.
- Ruleset de segredos ampliado (STS, `github_pat_`, cookies Slack, atribuição sem aspas).
- SSOT de versão (ADR-014), contrato de exit code do baseline testado, correção do `-h` antes
  do subcomando, read-back legado sem `attached None`.
- Suíte: **153 módulos / 3276 cenários** (era 141 / 3010).

## Pendência reportada

`NewsletterContent::version_from_relatorio()` agora devolve **1.5.0** (confirmado via tinker),
mas `public/content/newsletter/1.5.0/` não existe — o diretório para em `1.4.4`. Com isso,
`php artisan newsletter:send` **sem `--version`** passa a falhar explicitamente até o corpo da
newsletter 1.5.0 ser gerado (comportamento desenhado; usar `/generate-newsletter-body`).

## Anomalias menores (não bloqueantes)

- No fonte, a linha de `get_base_branch(quiet=)` tem markdown malformado
  (`... ): **`). EN e ES preservaram a forma original; FR normalizou para `) :**`. Diferença
  cosmética, sem efeito de renderização.
- Diferenças de negrito em três linhas ES/FR e PT-PT já existiam antes desta janela (trechos
  não tocados pelo delta) e foram mantidas.

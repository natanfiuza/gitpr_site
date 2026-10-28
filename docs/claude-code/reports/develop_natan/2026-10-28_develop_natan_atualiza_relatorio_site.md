# Atualização do relatório de estado do site — 2026-10-28

- **Branch:** `develop_natan`
- **Skill:** `update-relatorio`
- **Fonte (autoridade):** `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\docs\reports\relatorio_estado_v0.0.20.md`
- **Destino:** `public/content/relatorio.md` + 4 traduções

## Versões

| | Antes | Depois |
|---|---|---|
| Relatório | v0.0.19 (2026-10-27) | **v0.0.20 (2026-10-28)** |
| Linhas | 795 | 810 |
| Títulos (H1 + H2 + H3) | 52 | 52 |
| Bytes por arquivo | ~101–109 KB | ~104–112 KB |

O relatório fonte é o mais recente de `docs/reports/` (`relatorio_estado_v0.0.20.md`, 810 linhas, 107.319 bytes). O arquivo sem versão no nome (`relatorio_estado_ GitPR-CLI.md`) foi ignorado, conforme a skill.

## Método

O delta foi levantado uma vez, em PT-BR, com `diff -u` entre `relatorio_estado_v0.0.19.md` e `relatorio_estado_v0.0.20.md`. O mesmo delta foi então espelhado nos cinco arquivos do site:

1. **PT-BR** — `cp` integral do fonte, por ser o caso-limite já documentado: o arquivo do site é reprodução byte a byte do relatório, não tradução do inglês.
2. **Inglês** — aplicação hunk a hunk do delta do fonte, traduzindo cada bloco para o inglês e preservando a convenção do arquivo (H1 com versão do relatório + data, `What's New in This Version (v0.0.20)`).
3. **PT-PT, ES e FR** — cada idioma recebeu o delta em inglês (`git diff HEAD -- public/content/relatorio.md`) mapeado para as linhas correspondentes do próprio arquivo, sem reescrever conteúdo inalterado.

Os cinco arquivos ficaram alinhados linha a linha: 810 linhas, 52 títulos nas mesmas posições e 8 separadores `---`.

Distribuição do delta (por arquivo):

| Métrica | Valor |
|---|---|
| Inserções | 115 |
| Remoções | 100 |
| Regiões alteradas | ~62 |
| Saldo | +15 linhas |

## Principais mudanças de conteúdo

- Cabeçalho e linha de novidades → v0.0.20 (2026-10-28); versão atual 1.4.3 (bump mergeado no PR #228, **ainda não publicado**).
- **Gate de atualização:** cache com janela deslizante de 1 h, `-u` sem cache, comparação PEP 440, `bounded_urlopen` (3 s), escrita atômica, validação na leitura e semântica booleana estrita de `GITPR_SKIP_UPDATE_CHECK`.
- **Gate em subcomandos**, apenas em execução interativa (TTY); ajuda do subcomando lida do `argv` bruto.
- **§29 badge:** as 7 falhas de `tests/badge/test_badge_paths.py` fechadas; suíte cai de 12 para 5 falhas conhecidas (3 i18n + 2 mentor).
- **Testes:** 2.964 → **2.996 cenários** (140 módulos, +32 cenários, +0 arquivos); `test_updater.py` de 23 para 55.
- **Release engine e pipeline:** seção `[1.4.3]` na working tree, sem tag e sem publicação no PyPI.
- **Documentação:** nenhum tópico novo; `docs/auto-update.*` e `docs/tutorial/install-from-source.*` atualizados nos 5 idiomas; novo `CONTEXT.md` com o glossário do gate.
- **Próximos passos:** publicar a 1.4.3, documentar a mudança de semântica do `GITPR_SKIP_UPDATE_CHECK`, revalidar o gate real depois do release.

## Arquivos alterados

| Arquivo | Linhas | Título (H1) |
|---|---|---|
| `public/content/relatorio.md` | 810 | `# **🚀 Project Status Report: GitPR CLI — v0.0.20 (2026-10-28)**` |
| `public/content/relatorio.pt_br.md` | 810 | `# **🚀 Relatório de Status do Projeto: GitPR CLI — v0.0.20 (2026-10-28)**` |
| `public/content/relatorio.pt_pt.md` | 810 | `# **🚀 Relatório de Estado do Projeto: GitPR CLI — v0.0.20 (2026-10-28)**` |
| `public/content/relatorio.es.md` | 810 | `# **🚀 Informe de Estado del Proyecto: GitPR CLI — v0.0.20 (2026-10-28)**` |
| `public/content/relatorio.fr.md` | 810 | `# **🚀 Rapport de Statut du Projet : GitPR CLI — v0.0.20 (2026-10-28)**` |

O `relatorio.pt_br.md` é **cópia byte a byte** do fonte, não tradução do inglês: MD5 `22351dc54972b2a26730691197fb5bce`, idêntico ao de `relatorio_estado_v0.0.20.md`. Nenhum arquivo do site foi apagado.

## Verificação

| Checagem | Resultado |
|---|---|
| Linhas por arquivo | 810 nos 5 |
| Títulos | 52 nos 5 (H2 = 8, H3 = 43) |
| Posição de cada título vs fonte | 0 divergências |
| Separadores `---` | 8 nos 5 |
| Título no idioma certo | 5/5 |
| Âncoras novas (`v0.0.20`, `2026-10-28`, `2996`, `1.4.3`, `bounded_urlopen`, `CONTEXT.md`, `55`) | presentes nos 5 |
| Marcadores obsoletos (`v0.0.19 (2026-10-27)`, `2964 collected`, `12 failed, 2950`, `main.py:678`) | 0 nos 5 |
| Linhas de prosa idênticas ao inglês (vazamento) | 0 reais |
| Marcadores de outro idioma em ES/FR/PT-PT | 0 |
| `NewsletterContent::version_from_relatorio()` | resolve **1.4.3** |
| `NewsletterSendCommandTest` | 6/6 passando |

Terminologia conferida contra o `HEAD` de cada arquivo: ES usa `proveedor`/`puerta`/`riesgo`; FR usa `Provider` (capitalizado, SCM), `fournisseur` (IA), `barrière`, `risque`; PT-PT usa `provedor`, `portão`, `risco`. Os únicos `provider`/`gate`/`risk` remanescentes em prosa inglesa são identificadores de código, nomes de comando (`gitpr risk`) e os termos ingleses do glossário `CONTEXT.md`, que o próprio relatório cita.

## Observações

- **Newsletter 1.4.3:** diferente da atualização anterior (em que o corpo da 1.4.2 ainda não existia), `public/content/newsletter/1.4.3/` já está presente e o regex do relatório em inglês extrai `1.4.3` corretamente — nenhuma ação pendente nesse fluxo.
- **Fallback ES/FR do site:** o relatório segue dizendo que o site "ainda pode servir inglês" para `?lang=es`/`?lang=fr`; esta atualização não altera a configuração de locales, apenas o conteúdo.
- Nenhuma alteração de código PHP/Vue foi feita; o escopo é exclusivamente os cinco arquivos Markdown de conteúdo.

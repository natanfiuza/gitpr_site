# Geração da Newsletter — GitPR 1.4.3

**Data:** 2026-10-29
**Branch:** `develop_natan`
**Skill:** `generate-newsletter-body`

## Versão

- **Versão do CLI:** `1.4.3` — extraída de `Current version` em [relatorio.md](../../../../public/content/relatorio.md#L15).
- **Decisão de escopo:** o relatório registra que a 1.4.3 ainda **não está publicada** (PyPI serve 1.4.2, sem tag `v1.4.3`). O usuário optou por gerar a edição 1.4.3 agora; o envio deve ocorrer após a publicação da release.
- **Versão do relatório (H1):** v0.0.20 — não usada, conforme a skill.

## Arquivos gerados

Em `public/content/newsletter/1.4.3/`:

| Arquivo | Idioma |
| --- | --- |
| `newsletter_body.md` | en |
| `newsletter_body.pt_br.md` | pt_br |
| `newsletter_body.pt_pt.md` | pt_pt |
| `newsletter_body.es.md` | es |
| `newsletter_body.fr.md` | fr |

### Estrutura

Cada arquivo tem exatamente quatro seções, nos títulos equivalentes da edição anterior (1.4.2):

1. `# GitPR 1.4.3 — …`
2. Novidades da versão (6 bullets)
3. Como usar
4. Dicas úteis

### Origem das novidades

Os bullets são **fatias verbatim** do relatório de **cada idioma** — não foram retraduzidos do inglês. A fatia vai do cabeçalho de "Novidades" até a linha de versão atual, com remoção do ponteiro `§` (não havia nenhum nesta janela). Verificação por hash MD5 da fatia:

| Idioma | Bullets | MD5 (12 primeiros) |
| --- | --- | --- |
| en | 6 | `80d415aaf6fa` |
| pt_br | 6 | `8ace5381ccc9` |
| pt_pt | 6 | `538ef59b936a` |
| es | 6 | `1ea235da6675` |
| fr | 6 | `7da919888a34` |

### "Como usar" (autorado)

Bloco novo, escrito por idioma, sem tirar do relatório. Cobre apenas o que mudou nesta versão:

- `pip install --upgrade gitpr-cli` (sem afirmar que a 1.4.3 já está no PyPI).
- Portão de atualização agora alcança subcomandos interativos; CI, pipes, hooks, MCP, `--quiet` e `-h` nunca são bloqueados.
- `gitpr -u`/`--update` consulta o PyPI em toda execução.
- `GITPR_SKIP_UPDATE_CHECK` como booleano estrito.

## Dica consumida

- **`tip_27`** — `docs/check-ci.md` (`gitpr check` como entrada única de CI: SARIF/JUnit, `--no-ia`, `--ia-only-high-risk`, exit codes 0/1/2).
- Marcada com `used = true` em [tip_tools.json](../../../../public/content/tip_tools.json).
- **Auditoria:** conteúdo conferido contra `public/content/docs/check-ci.md` (SARIF 2.1.0, JUnit, flags e tabela de exit codes presentes).
- **Dica descartada:** `tip_9` (`docs/auto-update.md`) ficou **desatualizada por esta própria release** — afirma cache de 24h, enquanto a 1.4.3 passa a janela corrida de 1h e estende o portão aos subcomandos. Não foi consumida; recomenda-se corrigi-la numa próxima execução de `/update-tip-tools`.

## Verificações

- [x] 5 arquivos com a mesma estrutura de seções, cada um no seu idioma.
- [x] Bullets byte-idênticos à fatia do relatório de cada idioma (hash conferido).
- [x] Dica presente verbatim no idioma correspondente em cada arquivo.
- [x] Nenhum ponteiro `§` remanescente.
- [x] `public/content/menu.json` **não alterado** (newsletter fica fora do menu por design).
- [x] `tip_tools.json` válido após a marcação; `tip_27` com `used = true`.
- [x] `public/content/newsletter/1.4.3/` não existia antes; nada foi sobrescrito.

## Observações

- O `NewsletterContent::version_from_relatorio()` resolve a versão pelo mesmo campo `Current version` do relatório, portanto `newsletter:send` sem `--version` já aponta para `1.4.3`.
- Envio pendente: publicar a 1.4.3 no PyPI (tag + CHANGELOG + upload) e só então rodar `php artisan newsletter:send`.

# Relatório — Geração do corpo da newsletter (generate-newsletter-body)

- **Data**: 2026-09-13
- **Branch**: `develop_natan`
- **Task**: `generate_newsletter`
- **Skill**: `/generate-newsletter-body`

## Contexto

Run do `/generate-newsletter-body`: gerar `newsletter_body.{lang}.md` nos 5 idiomas do site a partir do relatório de status mais recente.

- **Versão usada**: **1.1.0** — extraída do campo `Current version` de `public/content/relatorio.md` (versão do CLI), e **não** a versão do arquivo de relatório (v0.0.14, no H1). Conforme a observação da skill.
- **Diretório de destino**: `public/content/newsletter/1.1.0/` — **não existia** (havia apenas 0.0.35, 0.0.36, 0.0.37 e 1.0.0), então não foi necessário perguntar sobre sobrescrita.
- **Fonte de conteúdo**: os 5 arquivos `relatorio*.md` recém-atualizados para a v0.0.14.

## Arquivos gerados

| Arquivo | Idioma | Seções |
| --- | --- | --- |
| `public/content/newsletter/1.1.0/newsletter_body.md` | en | 4 |
| `public/content/newsletter/1.1.0/newsletter_body.pt_br.md` | pt_br | 4 |
| `public/content/newsletter/1.1.0/newsletter_body.pt_pt.md` | pt_pt | 4 |
| `public/content/newsletter/1.1.0/newsletter_body.es.md` | es | 4 |
| `public/content/newsletter/1.1.0/newsletter_body.fr.md` | fr | 4 |

Estrutura adotada (mesma da edição 1.0.0, com os mesmos títulos de seção por idioma):

```
# GitPR 1.1.0 — {What's New | Novidades | Novedades | Nouveautés}
## {What's New in This Version | Novidades desta versão | Novedades de esta versión | Nouveautés de cette version}
## {How to Use | Como usar | Cómo usar | Comment l'utiliser}
## {Useful Tips | Dicas úteis | Consejos útiles | Astuces utiles}
```

## Dica consumida

| id | source | usado antes | marcado |
| --- | --- | --- | --- |
| `tip_21` | `i18n.md` | `false` | ✅ `used: true` |

**Motivo da escolha**: a dica trata do `--lang`, que é exatamente um dos destaques da 1.1.0 (a correção do idioma dos hooks Git fez o `--lang` passar a valer também para os scripts de hook). O fecho da dica menciona essa ligação.

Total de dicas marcadas como usadas agora: 5 (`tip_1`, `tip_5`, `tip_7`, `tip_11`, `tip_21`) de 26. Restam 21 disponíveis.

### ⚠️ Dica descartada por estar desatualizada

`tip_9` (`docs/auto-update.md`) ainda descreve o **hot-swap com `.exe.old` e rollback** — comportamento **removido na 1.1.0**. A dica ficou factualmente incorreta e **não deve ser usada** em newsletters futuras enquanto não for reescrita. Recomendação: rodar `/update-tip-tools` para corrigi-la ou removê-la do banco.

## Conteúdo

**Novidades** — condensadas do relatório para leitura em e-mail, cobrindo: TUI `gitpr config`, seção Skills, log geral de uso, correção do idioma dos hooks, distribuição PyPI-only com portão de atualização, i18n 955 chaves, 2 famílias de docs novas, remoção da `PR_AUTO_PUBLISH` e o salto para 1.1.0.

**Como usar** — ajustado em relação à edição 1.0.0:

> A newsletter da 1.0.0 dizia *"Or download the standalone binary from GitHub Releases"*. Essa linha foi **removida**, porque o canal binário deixou de existir na 1.1.0. No lugar entrou o aviso do portão de atualização obrigatória (bloqueio com exit code 1, cache diário, `--update` só reporta e `GITPR_SKIP_UPDATE_CHECK` para automação offline), além de `gitpr config` e do caminho do log de uso.

Sem HTML embutido, sem classes CSS e sem links externos — o corpo funciona em e-mail.

## Verificação

- Os 5 arquivos têm **4 seções cada**, na mesma ordem e com os títulos no idioma correto (confirmado por `grep`).
- `public/content/menu.json` **não foi alterado** por esta tarefa. Ele aparece como modificado no `git status`, mas a alteração é **pré-existente**, do run anterior do `/sync-docs` (adição dos tópicos `config-tui`, `release-notes`, `usage-log` e `PR Default Mode` ao menu) — o diff não contém nenhuma referência a newsletter.
- `tip_tools.json` continua sendo **JSON válido** (26 dicas) e o diff tem **exatamente 1 linha** alterada (`"used": false` → `"used": true` em `tip_21`).
- Checagem por menção obsoleta a binário standalone / GitHub Releases: a única ocorrência é a frase que **informa a aposentadoria** do canal, intencional.

## Arquivos alterados

```
public/content/newsletter/1.1.0/newsletter_body.md          (novo)
public/content/newsletter/1.1.0/newsletter_body.pt_br.md    (novo)
public/content/newsletter/1.1.0/newsletter_body.pt_pt.md    (novo)
public/content/newsletter/1.1.0/newsletter_body.es.md       (novo)
public/content/newsletter/1.1.0/newsletter_body.fr.md       (novo)
public/content/tip_tools.json                               (1 linha)
docs/claude-code/reports/develop_natan/2026-09-13_develop_natan_generate_newsletter.md  (este relatório)
```

## Observações

- O envio é feito pelo comando `php artisan newsletter:send` (manual), que resolve o idioma de cada inscrito com fallback para inglês.
- O diretório `1.1.0/` é novo — nada foi sobrescrito.

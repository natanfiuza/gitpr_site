# Relatório — execução do `/sync-docs`: 4 tópicos novos da v1.4.0, 5 tópicos atualizados e o bug de precedência de rotas

- **Data**: 2026-10-12
- **Branch**: `develop_natan`
- **Task**: `sync_docs`
- **Skill**: `/sync-docs`
- **Antecessor**: `2026-09-24_develop_natan_sync_docs.md` (última sincronização; deixou o site com 47 tópicos e 204 arquivos em paridade)

## 1. Pedido

`/sync-docs` sem argumentos: sincronizar `public/content/docs/` com `<gitpr>/docs/`.

## 2. Diagnóstico

A fonte traz a documentação da **v1.4.0** (a `gitpr@1.4.0`, commit `d…`/"Nova atualização da versão 1.3.0" é o que está no site). O diagnóstico encontrou quatro frentes:

| # | Achado | Escala |
| --- | --- | --- |
| 1 | Tópicos só na fonte | 4 tópicos × 5 variantes = **20 arquivos** |
| 2 | Tópicos comuns com conteúdo divergente | 5 tópicos × 5 variantes = **25 arquivos** |
| 3 | Entradas de menu a criar | 4 tópicos × 5 línguas = **20 linhas** |
| 4 | Links cruzados a reescrever | a executar após 1 e 2 |

### 2.1 Tópicos novos (v1.4.0)

| Tópico | Comando |
| --- | --- |
| `baseline-suppressions` | `gitpr baseline` |
| `policy-packs` | `gitpr policy` |
| `risk-scoring` | `gitpr risk` |
| `mentor-mode` | `gitpr mentor` / `--mentor` |

Os quatro têm **5 variantes na fonte** e os quatro aparecem no `CHANGELOG.md` §`[1.4.0]`, em ordem — que é a ordem usada na inserção do menu.

### 2.2 Tópicos divergentes

| Tópico | Natureza da mudança |
| --- | --- |
| `readme` | +82 linhas: os 4 comandos novos, `--mentor` em `-r`/`-f`, contagem de ferramentas MCP 14→16 e recursos 18→19 |
| `metricas-telemetria` | **+279 linhas**: o JSON de telemetria virou livro-razão SQLite |
| `ARCHITECTURE` | `--metrics`/`--dashboard` → `gitpr metrics` |
| `mcp-annotations` | tool `get_baseline_status` adicionada |
| `mcp-integration` | resource `get_baseline_status` adicionada |

As três últimas são alterações de uma linha, detectadas pelo `--source` no byte exato.

## 3. O que foi entregue

### 3.1 Os 4 tópicos novos — 20 arquivos

Cópia fiel da fonte com o mapeamento de sufixo da skill (`.es_es`→`.es`, `.fr_fr`→`.fr`). Os 20 arquivos ficaram **byte a byte idênticos à fonte** (sha256 conferido, 20/20).

| Tópico | en | pt_br | pt_pt | es | fr |
| --- | --- | --- | --- | --- | --- |
| `baseline-suppressions` | criado | criado | criado | criado (`.es_es`→`.es`) | criado (`.fr_fr`→`.fr`) |
| `mentor-mode` | criado | criado | criado | criado (`.es_es`→`.es`) | criado (`.fr_fr`→`.fr`) |
| `policy-packs` | criado | criado | criado | criado (`.es_es`→`.es`) | criado (`.fr_fr`→`.fr`) |
| `risk-scoring` | criado | criado | criado | criado (`.es_es`→`.es`) | criado (`.fr_fr`→`.fr`) |

**Os tópicos novos não trouxeram link relativo nenhum** — verificado por varredura: os destinos internos deles são URLs absolutas do GitHub. Por isso a reescrita de links não os tocou (é também por isso que eles continuam idênticos à fonte depois da reescrita).

### 3.2 Os 5 tópicos divergentes — 25 arquivos

Sobrescritos pela fonte, **variante por variante** (as 5 de cada, não só a inglesa): `ARCHITECTURE`, `mcp-annotations`, `mcp-integration`, `metricas-telemetria`, `readme`.

### 3.3 `menu.json` — 20 inserções

De **47 para 51** entradas `docs/*` por língua. Título = H1 da variante daquele idioma **sem o prefixo "Technical Documentation: "**, a convenção do resto do menu; título por idioma, posição única compartilhada pelas 5 línguas (como no menu existente).

| Tópico | Inserido antes de | `en` | `pt_br` / `pt_pt` | `es` | `fr` |
| --- | --- | --- | --- | --- | --- |
| `baseline-suppressions` | `docs/blame-arqueologo` | Baseline and Auditable Suppressions | Baseline e Supressões Auditáveis | Baseline y Supresiones Auditables | Baseline et suppressions auditables |
| `mentor-mode` | `docs/linter-regras-customizadas` | Junior Mentor Mode | Modo Mentor Júnior | Modo Mentor Junior | Mode Mentor Junior |
| `risk-scoring` | `docs/map-reduce-diff` | Local Risk Scoring | Pontuação de Risco Local | Puntuación de Riesgo Local | Évaluation locale des risques |
| `policy-packs` | `docs/providers-ia` | Policy Packs | Policy Packs | Policy Packs | Policy Packs |

**Sobre a posição.** O `SKILL.md` pede "ordem alfabética aproximada do menu". O menu real é **cronológico por release** no fim (…`metricas-telemetria`, `otimizacao-de-tokens`, `release-notes`, `usage-log`), o que admitiria anexar os 4 no fim. Segui a skill e inseri em posição aproximadamente alfabética pelo título inglês (B, J, L, P) — cada entrada fica adjacente a vizinhos em ordem. **É a decisão mais discutível desta execução**; anexar no fim também seria defensável. O diff tem exatamente 20 linhas, sem reformatação.

### 3.4 Reescrita de links cruzados

`rewrite_doc_links.py` reescreveu **215 links em 20 arquivos** — todos entre os 25 sobrescritos de §3.2. Nenhum arquivo pré-existente do site foi tocado (o `--check` rodado **antes** da cópia já dava exit 0).

## 4. Verificação

| Verificação | Resultado |
| --- | --- |
| `rewrite_doc_links.py --self-test` | **36/36 ok** |
| `rewrite_doc_links.py --check` | **exit 0** — nenhum link cru restante |
| `rewrite_doc_links.py --check --source <gitpr>` | **exit 0 — 224 comparados, 0 `DIVERGE`** (era exit 1 com 45 problemas: 20 ausentes + 25 divergentes). É o critério de aceite |
| sha256 dos 20 arquivos novos × fonte | **20/20 idênticos** |
| Sufixos legados `.es_es`/`.fr_fr` no site | **nenhum** criado |
| `menu.json` parseia nas 5 línguas | ok (`json.load`) + asserção de path único por língua — um JSON inválido derrubaria o menu inteiro |
| Cobertura do menu | **51 tópicos × 5 línguas, 0 tópicos sem entrada** |
| Variantes por tópico | todos os tópicos comuns com **5/5** |
| Escopo do diff | conferido: **nada fora de `public/content/`** |
| `./vendor/bin/pest` | **50 passaram, 8 falharam** — falhas **pré-existentes**, ver §6.1 |

**Não rodei verificação em navegador.** A mudança é só de conteúdo markdown e `menu.json` (lido em runtime via `public_path()`, [DocsController.php:45](../../../../app/Http/Controllers/DocsController.php#L45)) — sem alteração de Vue/JS, sem `npm run build`.

## 5. Estado final de sincronização

- **51 tópicos no site** (era 47), **236 arquivos** (era 216). Todos com entrada no menu.
- **224 arquivos comparáveis** com a fonte, **0 divergências** (era 204).
- **5 tópicos sem as 5 variantes** — todos lacuna da própria fonte ou exclusivos do site, **nenhum é lacuna deste sync**:

| Tópico | Variantes | Por quê |
| --- | --- | --- |
| `github-issue-prompt-com-gh` | só `en` | monolíngue na fonte |
| `version-markers` | só `en` | monolíngue na fonte |
| `review-pr` | `en`, `pt_br` | a fonte só tem essas duas |
| `caveman-commit` | só `en` | exclusivo do site |
| `como_reverter_commit_git_localmente` | só `en` | migrou para `docs/extra/` na fonte |

- **Exclusivos do site** (4, não tocados): `caveman-commit`, `chat-interativo`, `como_reverter_commit_git_localmente`, `testar_sem_usar_pypi`.
- **0 arquivos legados** `.es_es`/`.fr_fr` — nada a remover.

## 6. Notas e anomalias

### 6.1 Oito testes Pest falham — **pré-existente, não é desta task**

`./vendor/bin/pest`: 50 passam, **8 falham** — todos `Auth/*` e `ProfileTest`, com `Expected 200 but received 404`.

**Causa raiz encontrada** (e não é a sincronização): [routes/web.php:27](../../../../routes/web.php#L27) registra o catch-all dos docs **antes** das rotas de auth, dashboard e profile:

```php
Route::get('/{page?}', [DocsController::class, 'show_document'])->where('page', '.*');  // linha 27
// ... /dashboard (38), /profile (43), require auth.php (48) — todos DEPOIS
```

Com `where('page', '.*')`, o catch-all casa **qualquer** caminho e vence por ordem de registro. Confirmado empiricamente pelo roteador:

```
/login      -> App\Http\Controllers\DocsController@show_document
/register   -> App\Http\Controllers\DocsController@show_document
/dashboard  -> App\Http\Controllers\DocsController@show_document
```

`route:list` mostra as rotas nomeadas existindo (elas estão registradas), o que mascara o problema — a resolução em tempo de requisição é que escolhe o catch-all. Não há cache de rotas (`bootstrap/cache/` só tem `packages.php` e `services.php`).

**Não corrigi.** É fora do escopo do `/sync-docs`, o diff desta task está confinado a `public/content/`, e a correção (mover o catch-all para o fim de `web.php`) é mudança de comportamento do site que é sua para decidir. Vale saber que isso **não é só teste vermelho**: em produção `/login`, `/dashboard` e `/profile` respondem 404, ou seja, toda a área autenticada do Breeze está inalcançável.

### 6.2 `rewrite_doc_links.py` quebra no console cp1252

O modo `--check --source` **aborta com traceback** ao imprimir uma divergência cujo trecho de contexto contenha emoji:

```
UnicodeEncodeError: 'charmap' codec can't encode character '✅' in position 70
  File "rewrite_doc_links.py", line 433, in main
    print(f"  DIVERGE  {name}: {why}")
```

Acontece **só quando há divergências** (o emoji vem do conteúdo: ✅ das tabelas de `mcp-annotations`). O exit code continua 1, então um gate de CI ainda falha — mas o relatório sai truncado na primeira divergência com emoji, que foi exatamente o que aconteceu nesta execução: vi 5 `DIVERGE` de `ARCHITECTURE` e 5 `ausente no site` antes de morrer, quando havia 45 problemas.

**Contorno usado**: `PYTHONIOENCODING=utf-8` na invocação. **Não corrigi o script** — ele é ferramenta da skill, e a correção (uma linha, `sys.stdout.reconfigure(encoding="utf-8", errors="replace")` no início de `main()`) é sua para aprovar. **Recomendo corrigir**, porque sem ela a saída do comando de aceite fica parcial justamente no caso em que ele mais importa.

### 6.3 Notas menores

- **`readme` × `README`.** O tópico do site é `readme` (minúsculo) e a fonte é `README.md` na raiz do repo; o pareamento é do próprio script (`_source_pairs`). Sem ação.
- **Os links do `readme` para outros docs são URLs absolutas do GitHub**, não relativas — por isso a reescrita não os toca e o `readme` do site aponta para o GitHub em vez de `/docs/...`. É o estado da fonte, preservado fielmente; não é regressão desta task.
- **`public/build/` continua versionado** e não foi reconstruído (nenhuma mudança em `resources/`).

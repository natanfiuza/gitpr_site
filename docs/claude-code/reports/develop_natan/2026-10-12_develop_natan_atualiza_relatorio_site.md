# Relatório — execução do `/update-relatorio`: relatório de estado v0.0.16 → v0.0.17 nas 5 línguas

- **Data**: 2026-10-12
- **Branch**: `develop_natan`
- **Task**: `atualiza_relatorio_site`
- **Skill**: `/update-relatorio`
- **Antecessor**: `2026-09-25_develop_natan_atualiza_relatorio_site.md` (v0.0.15 → v0.0.16)
- **Estado do repositório no início**: limpo (`git status` sem alterações; HEAD = `ade4291 Nova atualização da versão 1.3.0`)

## 1. Pedido

`/update-relatorio` sem argumentos: sincronizar `public/content/relatorio.md` e suas 4 traduções com o relatório de estado mais recente do repositório GitPR CLI.

## 2. Diagnóstico

### 2.1 Fonte escolhida

`C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\docs\reports\` — a maior versão semântica encontrada é **`relatorio_estado_v0.0.17.md`** (754 linhas, 114951 bytes). O arquivo sem versão no nome (`relatorio_estado_ GitPR-CLI.md` — tem espaço) foi ignorado, conforme a skill.

### 2.2 Comparação de versões

| | Publicado no site (HEAD) | Fonte nova |
| --- | --- | --- |
| Versão do **relatório** (H1) | `v0.0.16` | **`v0.0.17`** |
| Data do relatório | 2026-09-23 | **2026-10-12** |
| Versão do **CLI** citada no corpo | `1.3.0` | **`1.4.0`** |

Versões diferentes → prossegue. As duas versões (relatório × CLI) foram mantidas distintas do início ao fim, conforme o aviso da skill.

### 2.3 Não é uma atualização incremental — é retradução integral

O relatório v0.0.17 é uma reescrita substancial, não um incremento: apenas ~1,8% do inglês anterior era reaproveitável. Todo o corpo (8 seções H2, 41 H3) foi retraduzido do fonte PT-BR, sem resumo nem omissão — a skill é explícita: *"o arquivo do site deve refletir todo o conteúdo do relatório fonte"*.

## 3. Método

1. Fonte PT-BR → **inglês** em 6 blocos contíguos (`%TEMP%\rel_en\00..05.md`), traduzidos integralmente, cada bloco preservando a contagem de linhas do trecho correspondente.
2. Concatenação com `strip('\n')` + `join('\n\n')` — evita o defeito de junção em que `---` colaria logo após um bullet sem a linha em branco obrigatória.
3. **PT-BR**: cópia byte a byte do fonte (`cp`), **não** tradução do inglês — ver §3.1.
4. Inglês → PT-PT, ES, FR por três subagentes em paralelo, cada um com a mesma receita explícita (6 fatias a partir do inglês, contagem de linhas por fatia, concatenação, script de autoverificação obrigatório).
5. Verificação **independente** dos três (não confiar no auto-relato dos subagentes) + `git show HEAD:` de cada arquivo para apurar a convenção terminológica própria de cada idioma.

### 3.1 O caso PT-BR — o arquivo do site **é** o fonte

`public/content/relatorio.pt_br.md` é reprodução byte-idêntica do fonte PT-BR, não uma retradução do inglês. Confirmado por checksum antes de qualquer trabalho de tradução:

```
sha256 fonte : ad77fbe3247d6a41ffb6c8b7f47ff235dcfb41e5ee277f4fad9a8a1f55d3aad8
sha256 site  : ad77fbe3247d6a41ffb6c8b7f47ff235dcfb41e5ee277f4fad9a8a1f55d3aad8
byte-identical: True   (754 linhas, 114951 bytes)
```

O arquivo **não foi tocado** por nenhum agente de tradução. Esta é a convenção registrada na memória `relatorio-ptbr-reproducao-fonte`.

## 4. O que foi entregue

| Arquivo | Idioma | Antes (linhas / bytes) | Depois (linhas / bytes) | Δ bytes |
| --- | --- | --- | --- | --- |
| `public/content/relatorio.md` | EN (pivô) | 638 / 87212 | **754 / 112563** | +25351 |
| `public/content/relatorio.pt_br.md` | PT-BR | 638 / 88853 | **754 / 114951** | +26098 |
| `public/content/relatorio.pt_pt.md` | PT-PT | 638 / 92159 | **754 / 118901** | +26742 |
| `public/content/relatorio.es.md` | ES | 638 / 91894 | **754 / 118558** | +26664 |
| `public/content/relatorio.fr.md` | FR | 638 / 95087 | **754 / 123035** | +27948 |

### 4.1 Cabeçalhos por idioma

| Idioma | H1 | Seção de novidades | Rodapé (data / ramo / autor) |
| --- | --- | --- | --- |
| EN | `# **🚀 Project Status Report: GitPR CLI — v0.0.17 (2026-10-12)**` | `**What's New in This Version (v0.0.17):**` | `**Report generated on:**` / `**Branch:**` / `**Author:**` |
| PT-BR | `# **🚀 Relatório de Status do Projeto: GitPR CLI — v0.0.17 (2026-10-12)**` | `**Novidades desta versão (v0.0.17):**` | `**Relatório gerado em:**` / `**Branch:**` / `**Autor:**` |
| PT-PT | `# **🚀 Relatório de Estado do Projeto: GitPR CLI — v0.0.17 (2026-10-12)**` | `**Novidades desta versão (v0.0.17):**` | `**Relatório gerado em:**` / `**Branch:**` / `**Autor:**` |
| ES | `# **🚀 Informe de Estado del Proyecto: GitPR CLI — v0.0.17 (2026-10-12)**` | `**Novedades de esta versión (v0.0.17):**` | `**Informe generado el:**` / `**Rama:**` / `**Autor:**` |
| FR | `# **🚀 Rapport de Statut du Projet : GitPR CLI — v0.0.17 (2026-10-12)**` | `**Nouveautés de cette version (v0.0.17) :**` | `**Rapport généré le :**` / `**Branche :**` / `**Auteur :**` |

O H1 espelha a **versão do relatório** (v0.0.17), não a do CLI (1.4.0) — a do CLI aparece na linha `- **Current version:** 1.4.0 …`, que é o carrier lido pelo regex da newsletter (§5.1). O estilo de título de cada idioma foi preservado do arquivo anterior. O FR mantém a tipografia francesa de espaço antes dos dois-pontos.

## 5. Verificação

### 5.1 Paridade estrutural com o fonte

Script de verificação comparando o fonte PT-BR com os 5 arquivos do site, linha a linha:

```
en     lines=754 ok=True kind_mismatch=0 hrules=8 new=135
pt_br  lines=754 ok=True kind_mismatch=0 hrules=8 new=135
pt_pt  lines=754 ok=True kind_mismatch=0 hrules=8 new=135
es     lines=754 ok=True kind_mismatch=0 hrules=8 new=135
fr     lines=754 ok=True kind_mismatch=0 hrules=8 new=135
```

- **754 linhas** em todos, idêntico ao fonte;
- **0** posições de título divergentes (8 H2 e 41 H3 nos mesmos números de linha e nos mesmos níveis);
- **8** separadores `---`;
- **163** linhas de tabela e **372** bullets em todos;
- `kind_mismatch=0`: cada linha do site tem o mesmo tipo (título/tabela/bullet/código/vazia) que a linha homóloga do fonte;
- **0** links relativos `.md` remanescentes (o `relatorio` não é tópico de `docs/`, então não passa pelo `rewrite_doc_links.py`; foi checado que não há nenhum).

### 5.2 Ausência de vazamento de outro idioma

Varredura com fronteira de palavra (`\b`) por marcadores exclusivos de cada idioma nos outros quatro arquivos. Resultado: nenhum achado real.

O primeiro passe acusou falsos positivos, diagnosticados e descartados: os marcadores `"dos "`, `"das "` e `"está "` no arquivo ES eram substrings de `"todos "` / `"todas "` e da palavra espanhola legítima `está`. Reexecutado com `\b`, só restou `está` — que é espanhol correto.

Em PT-PT, `provedor` (24×) foi sinalizado contra a lista de marcadores pt-BR. Investigado com `git show HEAD:public/content/relatorio.pt_pt.md`: o arquivo PT-PT anterior já usava `provedor` 21× contra `fornecedor` 1×. É a **convenção estabelecida do idioma**, não vazamento. Idem `conforme o`, que é português europeu válido.

### 5.3 Duas regressões terminológicas reais — corrigidas

A verificação independente cruzou cada arquivo com o seu próprio antecessor no HEAD e com a documentação do site. Duas regressões reais foram encontradas e corrigidas:

**ES — 17 substituições.** Os subagentes deixaram passar 7 ocorrências de "provider" e 3 de "gate" sem traduzir. Evidência de que o arquivo ES já traduzia esses termos:

| Termo no HEAD (ES) | Ocorrências | Termo deixado pelos agentes | Ocorrências |
| --- | --- | --- | --- |
| `Proveedor de Azure DevOps/Bitbucket/GitHub/GitLab` | 11× `proveedor` | `Provider …` | 7× |
| `puerta` (gate) | 9× | `gate` | 3× |
| `delega en el proveedor`, `Resolución del proveedor`, `Contrato del proveedor falso` | — | idem em inglês | — |

Corroborado pela doc do site: [policy-packs.es.md](public/content/docs/policy-packs.es.md) usa "puerta"; [risk-scoring.es.md](public/content/docs/risk-scoring.es.md) tem por título "Puntuación de Riesgo Local".

As 17 edições (cada uma assertada como ocorrência única na linha): L99 `risk`→`riesgo`, L200 `al risk`→`al riesgo`, L394 `provider`→`proveedor`, L479 `el risk`→`el riesgo`, L541 `Gate:`→`Puerta:`, L563 `provider fake`→`proveedor falso`, L599/L600/L629/L723 `risk`→`riesgo`, L605/L606/L610/L611 `Provider X`→`Proveedor de X`, L609 `provider`→`proveedor`, L725 `gate`→`puerta`, L744 `gate`→`puerta`.

**FR — 7 substituições.** Foram deixados 2 "Gate" onde o FR usa `barrière` (11× no HEAD) e 4 `Fournisseur <forge>` onde o FR usa `Provider <forge>` para forges SCM — reservando `fournisseur` para provedores de IA. O próprio arquivo novo diz, na L248, "les quatre providers".

As 7 edições: L541 `Gate :`→`Barrière :`, L744 `gate`→`barrière`, L605/L606/L610/L611 `Fournisseur X`→`Provider X`, L609 `fournisseur`→`provider`.

**Resíduos intencionais** (não são regressão — são nomes próprios de comando/feature ou contagem de categoria de schema): `risk` em ES L643/655/660/693 e FR L655/660/693; `provider` em FR L248/605/606/609/610/611 (a convenção restaurada).

### 5.4 Suíte de testes

```
Tests:  8 failed, 50 passed (58) — Assertions: 222 — Duration: 6.52s
```

As 8 falhas são **exatamente** as mesmas documentadas no relatório de `sync-docs` de hoje, e todas com a mesma causa:

```
Auth\AuthenticationTest::login_screen_can_be_rendered
Auth\EmailVerificationTest::email_verification_screen_can_be_rendered
Auth\EmailVerificationTest::email_can_be_verified
Auth\PasswordConfirmationTest::confirm_password_screen_can_be_rendered
Auth\PasswordResetTest::reset_password_link_screen_can_be_rendered
Auth\PasswordResetTest::reset_password_screen_can_be_rendered
Auth\RegistrationTest::registration_screen_can_be_rendered
ProfileTest::profile_page_is_displayed
```

Todas `Expected 200 but received 404`: a rota catch-all `Route::get('/{page?}', …)->where('page', '.*')` em [routes/web.php:27](routes/web.php#L27) está registrada **antes** das rotas de auth e as sombreia. É bug pré-existente e fora do escopo desta tarefa — nenhuma falha nova foi introduzida pela atualização do relatório.

## 6. Efeitos colaterais e achados

### 6.1 A newsletter agora resolve para uma versão sem diretório — esperado, mas bloqueia o próximo passo

[NewsletterContent::version_from_relatorio()](app/Support/NewsletterContent.php) lê o **inglês** com `/Current version:\*{0,2}\s*(\d+\.\d+\.\d+)/`. A linha carrier ficou:

```
- **Current version:** 1.4.0 (**tagged** — `v1.4.0`, merge of PR #207, 2026-10-12; `v1.3.0` on 2026-09-24)
```

Confirmado com `php artisan tinker`:

| Chamada | Resultado |
| --- | --- |
| `version_from_relatorio()` | `"1.4.0"` (antes: `"1.3.0"`) |
| `body_markdown('1.4.0', 'en')` | `RuntimeException: Newsletter body not found for version 1.4.0.` |
| `last_sent_version()` | `NULL` (sem diretório de marcador nesta máquina) |

`public/content/newsletter/` vai até `1.3.0/`; não existe `1.4.0/`. **Consequência operacional**: `php artisan newsletter:send` sem `--version` vai estourar até que `/generate-newsletter-body` crie o diretório 1.4.0. É a lacuna já conhecida e registrada na memória `newsletter-bullets-verbatim-e-dicas-obsoletas` — o passo seguinte natural é rodar `/generate-newsletter-body`.

### 6.2 Ambiguidade `por padrão` / "by pattern" — observada, não corrigida

Tanto o fonte PT-BR quanto o PT-PT traduzem o inglês "by pattern" (classificação de bugs/reverts por padrão textual) como "por padrão", que em português lê-se primeiro como *default*. O ES ("por patrón") e o FR ("par motif") são precisos. O texto PT-BR é a redação do próprio autor no fonte, e o `relatorio.pt_br.md` é byte-frozen por convenção (§3.1) — **anotado para o autor, não editado**.

### 6.3 Sem `npm run build`

Alteração é só de conteúdo markdown em `public/content/`, servido em runtime. Nenhum asset, nenhum componente Vue tocado.

## 7. Estado final

- 5 arquivos de relatório em `public/content/`, todos em **754 linhas**, paridade estrutural exata com o fonte (0 divergências de posição de título, 0 de tipo de linha).
- 3 idiomas retraduzidos integralmente (PT-PT, ES, FR) a partir do inglês; 1 reproduzido byte a byte do fonte (PT-BR); 1 construído como pivô (EN).
- 24 correções terminológicas aplicadas na verificação (17 ES + 7 FR), com evidência `git show HEAD:` por arquivo.
- Nenhuma falha de teste nova (50 passed / 8 failed pré-existentes).
- Próximo passo sugerido: `/generate-newsletter-body` para a versão **1.4.0** (o relatório do site já é a fonte de fatia verbatim da newsletter).

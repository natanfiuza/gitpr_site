# GitPR CLI — Relatório de Estado do Projeto

---

## Visão Geral

O **GitPR** é uma ferramenta CLI para automação de fluxo de trabalho Git com IA (Google Gemini / DeepSeek). Atua como um assistente inteligente local que realiza Code Reviews, gera descrições de Pull Request, cria mensagens de commit semânticas, audita dívida técnica e injeta boas práticas no fluxo do programador (abordagem **Shift Left**).

---

## Arquitetura e Bibliotecas Base

- **Linguagem:** Python 3.x
- **Framework CLI:** Click (comandos, flags, formatação de terminal)
- **UI/Terminal:** TUI interativa (Textual) para chat e edição de issues
- **Encriptação:** Encriptação simétrica Fernet para proteção local de chaves de API
- **Configuração:** dotenv, PyYAML (para o linter estático)
- **Fornecedores IA:** SDK Google GenAI (gemini-pro-latest) + DeepSeek + Ollama

---

## Módulos Implementados

### 1. Núcleo e Operações Git (`src/core.py`)
Comunicação estruturada com LLM solicitando respostas estritamente em JSON (`commit_message` e `pr_description`). Otimização nativa do Git com flags `-U1`, `-w`, `-M`, `-B` para diffs mínimos e focados.

### 2. Interface CLI e Configuração (`src/main.py`, `src/config.py`)
Deteção de primeira execução, configuração interativa de chaves API, configuração `.env` em `~/.gitpr/`. Encaminhamento de comandos para todas as flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`).

### 3. Motor de Análise Estática (`src/linter_engine.py`)
Linter offline que analisa apenas linhas adicionadas (`+`) do `git diff`. Lê `.gitpr.linter.yml` com regras regex, ignorando comentários e com exclusão de caminhos via `fnmatch`.

### 4. Cofre de Segurança (`src/security.py`)
Geração de chave Fernet (`secret.key`), funções `encrypt_data` e `decrypt_data`. Chaves de API nunca armazenadas em texto simples.

### 5. Auto-Updater (`src/updater.py`)
Atualização hot-swap de binário via API GitHub Releases com verificação SHA-256 e capacidade de rollback.

### 6. Chat e Auto-Patch (`src/ui/chat_app.py`)
TUI interativa com memória de mensagens por ramo. F5 extrai blocos de código para ficheiros de patch. F6 exporta sessões para Markdown. Slash commands para ações comuns.

### 7. Internacionalização (`src/i18n.py`)
Helper `__()` inspirado no Laravel com placeholders nomeados. Pacotes de tradução JSON em `~/.gitpr/langs/`. Fallback para inglês para chaves em falta. Suporta `en`, `pt_br`, `pt_pt`, `fr`, `es`.

### 8. Arquitetura Map-Reduce
Otimização em duas camadas para diffs grandes:
- **Camada 1:** Flags nativas do Git (`-U1`, `-w`, `-M`, `-B`) para contexto mínimo
- **Camada 2:** Estimativa de tokens (`len() // 4`), divisão segura nos limites `diff --git`, chamadas de IA em lote com `time.sleep(1)` para respeitar rate limit, e etapa final Reduce concatenando sumários

---

## Métricas Chave

- **Fornecedores IA:** 3 (Gemini, DeepSeek, Ollama)
- **Idiomas Suportados:** 5 (EN, PT-BR, PT-PT, FR, ES)
- **Comandos CLI:** Mais de 12 flags
- **Linter:** Configurável por YAML, custo zero de IA
- **Cache:** Baseado em MD5, deduplicação automática
- **Segurança:** Encriptação simétrica Fernet (AES-128-CBC)

---

## Documentação

A documentação completa está disponível em [github.com/natafiuza/gitpr](https://github.com/natafiuza/gitpr) e neste site.

---

[← Contribuição](/contribuicao) &nbsp;|&nbsp; [Início →](/index)

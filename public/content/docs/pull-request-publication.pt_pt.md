# Documentação técnica: publicação de PR no GitHub (`--publish`)

Esta documentação descreve o fluxo de publicação de Pull Requests através da interface interativa no terminal (TUI), permitindo-lhe rever, editar e publicar Pull Requests diretamente no GitHub sem sair do terminal.

---

## 1. O que é o Publicador de PR?

Ao executar o comando `gitpr --publish`, o GitPR gera a descrição da PR com IA (tal como no comando padrão), guarda o ficheiro `.md` localmente e abre um painel interativo diretamente no terminal. Isto permite-lhe rever, editar e publicar a Pull Request gerada pela Inteligência Artificial antes de a enviar para o repositório remoto via API REST.

---

## 2. Modos de Publicação

O Publicador de PR tem **3 modos de execução**, acionados de acordo com os flags combinados com `--publish`.

### 2.1 Modo Interativo — `gitpr --publish`

Abre a TUI para revisão e edição antes de publicar.

```bash
gitpr --publish
```

| Característica | Descrição |
|---|---|
| **Fluxo** | `git fetch` → a IA gera a PR → `.md` guardado → TUI abre → utilizador edita → POST para o GitHub |
| **Quando usar** | Quando quiser rever e ajustar o conteúdo da PR antes de publicar |
| **Resultado** | Pull Request criada no GitHub com o conteúdo editado |
| **Ideal para** | Fluxo de trabalho padrão — controlo total sobre o que é publicado |

> **Dica:** O ficheiro `.md` local é guardado antes de a TUI abrir e é novamente guardado com quaisquer edições antes de publicar. Tem sempre uma cópia de segurança.

---

### 2.2 Publicação Direta — `gitpr --publish --no-edit`

Salta o editor interativo e publica diretamente.

```bash
gitpr --publish --no-edit
```

| Característica | Descrição |
|---|---|
| **Fluxo** | `git fetch` → a IA gera a PR → `.md` guardado → POST direto para o GitHub |
| **Quando usar** | Quando confiar no resultado da IA e quiser publicar imediatamente |
| **Resultado** | Pull Request criada no GitHub sem abrir a TUI |
| **Ideal para** | Pipelines de CI/CD, correções rápidas, fluxos de trabalho automatizados |

> **Cuidado:** Use com precaução — não terá a oportunidade de rever ou editar o conteúdo antes de publicar.

---

### 2.3 Modo de Publicação Automática — `PR_AUTO_PUBLISH=true`

Configura o GitPR para abrir sempre a TUI do publicador após gerar uma descrição de PR.

```bash
# Em ~/.gitpr/.env
PR_AUTO_PUBLISH=true
```

| Característica | Descrição |
|---|---|
| **Ativação** | Variável de ambiente em `~/.gitpr/.env` |
| **Comportamento** | Cada execução de `gitpr` abre a TUI do publicador após gerar a PR |
| **Quando usar** | Quando quiser publicar sempre após gerar a descrição da PR |
| **Ideal para** | Equipas que seguem um fluxo de trabalho de "gerar e publicar" |

---

## 3. Configuração da Branch de Base

A branch de destino da Pull Request é resolvida pela seguinte ordem de prioridade:

| Prioridade | Origem | Como configurar |
|---|---|---|
| **1 (mais alta)** | flag `--base` | `gitpr --publish --base develop` |
| **2** | variável de ambiente `PR_DEFAULT_BASE` | `PR_DEFAULT_BASE=develop` em `~/.gitpr/.env` |
| **3 (padrão)** | Deteção automática | `git symbolic-ref refs/remotes/origin/HEAD` (geralmente `main` ou `master`) |

---

## 4. Atalhos e Navegação na TUI

A interface foi concebida para ser rápida e não exigir utilização constante do rato. Pode navegar pelos campos com a tecla `Tab` e utilizar os seguintes atalhos:

| Tecla | Ação | Descrição |
|---|---|---|
| **`F1`** | Ajuda | Abre uma janela modal flutuante com instruções rápidas de utilização da interface |
| **`F2`** | Guardar `.md` Local | Guarda o conteúdo atualizado no ficheiro de descrição da PR do projeto atual. Ideal quando quiser aperfeiçoar o conteúdo mais tarde |
| **`F3`** | Publicar PR | Liga-se à API REST do GitHub e cria a Pull Request no repositório remoto. O link direto para a PR recém-criada será apresentado no terminal |
| **`Esc`** | Sair | Anula a operação e fecha a interface sem publicar |
| **`Tab`** | Navegar | Alterna o foco entre os campos da interface |

---

## 5. Integração com o GitHub (Token PAT)

Para criar Pull Requests diretamente no repositório remoto (`F3`), o GitPR precisa de um **Personal Access Token (PAT)** do GitHub com o scope `repo`.

### 5.1 Configuração do Token

Na primeira vez que utilizar `F3`, o GitPR:

1. Deteta que não existe nenhum token configurado
2. Apresenta o URL de geração do token com parâmetros pré-preenchidos (scope `repo`)
3. Pede-lhe para colar o token gerado
4. Guarda-o encriptado (Fernet) no ficheiro `~/.gitpr/.env`

> **Nota:** A TUI de issues (`gitpr -is`) partilha o mesmo token. Se já configurou um token para Issues, este será reutilizado automaticamente.

### 5.2 Segurança

- O token é guardado como um hash encriptado — nunca em texto simples
- A chave-mestra de desencriptação está localizada em `~/.gitpr/secret.key`
- O token é validado via `GET /user` antes de a TUI abrir
- Consulte o guia completo em [github-pat-integration.md](github-pat-integration.md)

---

## 6. API do GitHub — Criação de PR

A PR é criada via `POST https://api.github.com/repos/{owner}/{repo}/pulls` com o seguinte payload:

```json
{
  "title": "PR title (editable in TUI)",
  "body": "Full markdown PR description with commit message",
  "head": "Current branch (source)",
  "base": "Target branch (main, develop, etc.)"
}
```

---

## 7. Tratamento de Erros

| Erro | Comportamento |
|---|---|
| Token inválido/expirado (401) | Solicita um novo token (até 3 tentativas) |
| Branch não encontrada (422) | Apresenta a mensagem de erro do GitHub com detalhes |
| Sem commits para fundir (422) | Apresenta erro de validação sugerindo fazer alterações primeiro |
| A PR já existe (422) | Apresenta o conflito específico |
| Falha de rede | Apresenta mensagem de erro de ligação |
| Remote em falta | Erro antes de a TUI abrir — nenhuma chamada à API é tentada |

---

## 8. Variáveis de Ambiente

| Variável | Padrão | Descrição |
|---|---|---|
| `GITHUB_TOKEN_ENCRYPTED` | *(nenhum)* | Personal Access Token do GitHub encriptado |
| `PR_DEFAULT_BASE` | *(vazio)* | Branch de destino padrão (usa deteção automática quando vazia) |
| `PR_AUTO_PUBLISH` | `false` | Definir como `true` para abrir sempre o publicador após a geração da PR |

---

## 9. Exemplos Práticos

### Exemplo 1: Rever e publicar uma funcionalidade

```bash
# Terminou o desenvolvimento na branch feature/login
gitpr --publish
# → A IA gera a descrição da PR e abre a TUI
# → Reveja o título, o corpo e a branch de base
# → Prima F3 para criar a PR no GitHub
```

### Exemplo 2: Publicação rápida sem edição

```bash
gitpr --publish --no-edit
# → A PR é gerada e publicada imediatamente
# → O URL da PR é apresentado no terminal
```

### Exemplo 3: Publicar para uma branch de base personalizada

```bash
gitpr --publish --base staging
# → A branch de destino é definida como "staging" em vez de "main"
```

---

## 10. Ficheiros Relacionados

| Ficheiro | Função |
|---|---|
| `.gitpr.pr.md` | Template local com regras personalizadas para geração da descrição de PR (transferir com `gitpr -s`) |
| `~/.gitpr/.env` | Configuração global: chaves de API, padrões de PR e token GitHub encriptado |
| `~/.gitpr/secret.key` | Chave-mestra Fernet para desencriptação de credenciais |

> **Nota:** Consulte também a [documentação principal (README.md)](../README.md) para uma visão geral de todas as funcionalidades do GitPR e o [guia de descrição de PR](pr-descricao-padrao.md) para o fluxo de geração padrão de PR.

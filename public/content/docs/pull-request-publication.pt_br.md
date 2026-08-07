# Documentação Técnica: Publicação de PR no GitHub (`--publish`)

Esta documentação descreve o fluxo de publicação de Pull Requests via interface interativa de terminal (TUI), permitindo revisar, editar e publicar Pull Requests diretamente no GitHub sem sair do terminal.

---

## 1. O que é o Publisher de PR?

Quando você executa o comando `gitpr --publish`, o GitPR gera a descrição do PR com IA (igual ao comando padrão), salva o arquivo `.md` localmente e abre um painel interativo diretamente no terminal. Isso permite revisar, editar e publicar o Pull Request gerado pela Inteligência Artificial antes de enviá-lo ao repositório remoto via API REST.

---

## 2. Modos de Publicação

O Publisher de PR possui **3 modos de execução**, acionados conforme as flags combinadas com `--publish`.

### 2.1 Modo Interativo — `gitpr --publish`

Abre a TUI para revisão e edição antes de publicar.

```bash
gitpr --publish
```

| Característica | Descrição |
| --- | --- |
| **Fluxo** | `git fetch` → IA gera o PR → `.md` salvo → TUI abre → usuário edita → POST para o GitHub |
| **Quando usar** | Quando você quer revisar e ajustar o conteúdo do PR antes de publicar |
| **Resultado** | Pull Request criado no GitHub com o conteúdo editado |
| **Ideal para** | Fluxo de trabalho padrão — controle total sobre o que é publicado |

> **Dica:** O arquivo `.md` local é salvo antes de a TUI abrir e re-salvo com quaisquer edições antes de publicar. Você sempre tem um backup.

---

### 2.2 Publicação Direta — `gitpr --publish --no-edit`

Ignora o editor interativo e publica diretamente.

```bash
gitpr --publish --no-edit
```

| Característica | Descrição |
| --- | --- |
| **Fluxo** | `git fetch` → IA gera o PR → `.md` salvo → POST direto para o GitHub |
| **Quando usar** | Quando você confia no resultado da IA e quer publicar imediatamente |
| **Resultado** | Pull Request criado no GitHub sem abrir a TUI |
| **Ideal para** | Pipelines de CI/CD, correções rápidas, fluxos de trabalho automatizados |

> **Cuidado:** Use com cuidado — você não terá a chance de revisar ou editar o conteúdo antes de publicar.

---

### 2.3 Modo de Publicação Automática — `PR_AUTO_PUBLISH=true`

Configura o GitPR para sempre abrir a TUI do publisher após gerar uma descrição de PR.

```bash
# Em ~/.gitpr/.env
PR_AUTO_PUBLISH=true
```

| Característica | Descrição |
| --- | --- |
| **Ativação** | Variável de ambiente no `~/.gitpr/.env` |
| **Comportamento** | Toda execução do `gitpr` abre a TUI do publisher após gerar o PR |
| **Quando usar** | Quando você sempre quer publicar após gerar a descrição do PR |
| **Ideal para** | Equipes que seguem um fluxo de trabalho "gerar e publicar" |

---

## 3. Configuração da Branch Base

A branch de destino do Pull Request é resolvida na seguinte ordem de prioridade:

| Prioridade | Origem | Como configurar |
| --- | --- | --- |
| **1 (maior)** | flag `--base` | `gitpr --publish --base develop` |
| **2** | env `PR_DEFAULT_BASE` | `PR_DEFAULT_BASE=develop` no `~/.gitpr/.env` |
| **3 (padrão)** | Detecção automática | `git symbolic-ref refs/remotes/origin/HEAD` (geralmente `main` ou `master`) |

---

## 4. Atalhos e Navegação da TUI

A interface foi desenhada para ser rápida e dispensar o uso constante do mouse. Você pode navegar pelos campos usando a tecla `Tab` e utilizar os seguintes atalhos:

| Tecla | Ação | Descrição |
| --- | --- | --- |
| **`F1`** | Ajuda | Abre um modal flutuante com instruções rápidas de uso da interface |
| **`F2`** | Salvar `.md` Local | Salva o conteúdo atualizado no arquivo de descrição do PR no projeto atual. Ideal para quando você quiser refinar o conteúdo posteriormente |
| **`F3`** | Publicar PR | Conecta-se à API REST do GitHub e cria o Pull Request no repositório remoto. O link direto para o PR recém-criado será exibido no terminal |
| **`Esc`** | Sair | Aborta a operação e fecha a interface sem publicar |
| **`Tab`** | Navegar | Alterna o foco entre os campos da interface |

---

## 5. Integração com GitHub (Token PAT)

Para criar Pull Requests diretamente no repositório remoto (`F3`), o GitPR precisa de um **Personal Access Token (PAT)** do GitHub com escopo `repo`.

### 5.1 Configuração do Token

Na primeira vez que usar `F3`, o GitPR irá:

1. Detectar que nenhum token está configurado
2. Exibir a URL de geração do token com os parâmetros pré-preenchidos (escopo `repo`)
3. Solicitar que você cole o token gerado
4. Armazená-lo criptografado (Fernet) no arquivo `~/.gitpr/.env`

> **Nota:** A TUI de Issues (`gitpr -is`) compartilha o mesmo token. Se você já configurou um token para Issues, ele será reutilizado automaticamente.

### 5.2 Segurança

- O token é armazenado como hash criptografado — nunca em texto plano
- A chave mestra de descriptografia está localizada em `~/.gitpr/secret.key`
- O token é validado via `GET /user` antes de a TUI abrir
- Consulte o guia completo em [github-pat-integration.md](github-pat-integration.md)

---

## 6. API do GitHub — Criação de PR

O PR é criado via `POST https://api.github.com/repos/{owner}/{repo}/pulls` com o seguinte payload:

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
| --- | --- |
| Token inválido/expirado (401) | Solicita um novo token (até 3 tentativas) |
| Branch não encontrada (422) | Exibe a mensagem de erro do GitHub com detalhes |
| Sem commits para mesclar (422) | Exibe erro de validação sugerindo fazer alterações primeiro |
| PR já existente (422) | Exibe o conflito específico |
| Falha de rede | Exibe a mensagem de erro de conexão |
| Remote ausente | Erro antes de a TUI abrir — nenhuma chamada de API é tentada |

---

## 8. Variáveis de Ambiente

| Variável | Padrão | Descrição |
| --- | --- | --- |
| `GITHUB_TOKEN_ENCRYPTED` | *(nenhum)* | Token de Acesso Pessoal do GitHub criptografado |
| `PR_DEFAULT_BASE` | *(vazio)* | Branch de destino padrão (usa detecção automática quando vazio) |
| `PR_AUTO_PUBLISH` | `false` | Defina como `true` para sempre abrir o publisher após a geração do PR |

---

## 9. Exemplos Práticos

### Exemplo 1: Revisar e publicar uma feature

```bash
# Você terminou o desenvolvimento na branch feature/login
gitpr --publish
# → A IA gera a descrição do PR e abre a TUI
# → Revise o título, o corpo e a branch base
# → Pressione F3 para criar o PR no GitHub
```

### Exemplo 2: Publicação rápida sem edição

```bash
gitpr --publish --no-edit
# → O PR é gerado e publicado imediatamente
# → A URL do PR é exibida no terminal
```

### Exemplo 3: Publicar contra uma branch base personalizada

```bash
gitpr --publish --base staging
# → A branch de destino é definida como "staging" em vez de "main"
```

---

## 10. Arquivos Relacionados

| Arquivo | Função |
| --- | --- |
| `.gitpr.pr.md` | Template local com regras personalizadas para geração de descrição de PR (baixe com `gitpr -s`) |
| `~/.gitpr/.env` | Configuração global: chaves de API, padrões de PR e token do GitHub criptografado |
| `~/.gitpr/secret.key` | Chave mestra Fernet para descriptografia das credenciais |

> **Nota:** Consulte também a [documentação principal (README.md)](../README.md) para uma visão geral de todos os recursos do GitPR e o [guia de Descrição de PR](pr-descricao-padrao.md) para o fluxo padrão de geração de PR.

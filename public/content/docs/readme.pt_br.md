
# **GitPR CLI 🚀**

<p align="center">
  <img src="https://raw.githubusercontent.com/natanfiuza/gitpr/main/docs/logo.png" alt="GitPR Logo" width="150">
</p>

GitPR CLI é uma ferramenta de automação por linha de comando que utiliza inteligência artificial **Google Gemini** e **DeepSeek** para analisar suas alterações de código (git diff) ou arquivos inteiros. A ferramenta gera automaticamente mensagens de commit no padrão *Conventional Commits*, descrições detalhadas de Pull Requests e revisões de código profundas voltadas para a redução de dívida técnica.

🌐 **Site:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/) · 📂 **Repositório:** [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)

## **🛠️ Tecnologias e Bibliotecas Utilizadas**

Este projeto foi desenvolvido em Python e utiliza as seguintes bibliotecas principais:

* [**Click**](https://click.palletsprojects.com/): Para criar uma interface de linha de comando (CLI) robusta e amigável.
* [**Google GenAI**](https://pypi.org/project/google-genai/): SDK oficial para integração direta com a API Gemini.
* [**OpenAI**](https://pypi.org/project/openai/): Biblioteca utilizada devido à sua compatibilidade total com a poderosa API **DeepSeek**.
* [**Python-dotenv**](https://pypi.org/project/python-dotenv/): Para gerenciamento seguro de variáveis de ambiente.
* [**Pytest**](https://docs.pytest.org/): Para execução de testes unitários de forma simples, colorida e legível no console.
* [**Cryptography**](https://cryptography.io/): Para garantir que sua `GEMINI_API_KEY` seja armazenada de forma criptografada e segura em disco.
* [**PyYAML**](https://pyyaml.org/): Usado para ler e processar as regras personalizadas de análise estática do arquivo `.gitpr.linter.yml`.
* [**Textual**](https://textual.textualize.io/): Biblioteca poderosa para criar Interfaces Gráficas no Terminal (TUI), utilizada no painel interativo de geração e edição de issues.
* [**Requests**](https://pypi.org/project/requests/): Biblioteca elegante e robusta para requisições HTTP, usada para se comunicar com a API REST do GitHub.
* [**MCP**](https://pypi.org/project/mcp/): SDK oficial em Python para o Model Context Protocol, permitindo que o GitPR se integre diretamente com editores e IDEs alimentados por IA.

----

## 📦 Como Compilar o Executável Localmente

Se você deseja gerar seu próprio binário a partir do código-fonte, utilizamos o **PyInstaller**. Certifique-se de estar no diretório raiz do projeto com o ambiente virtual configurado.

1. Instale as dependências de desenvolvimento (se ainda não o fez):
   ```bash
   pipenv install --dev
   ```

2. Execute o comando de build apontando para nosso ponto de entrada (`run.py`):
   ```bash
   pipenv run pyinstaller --noconfirm --onefile --icon=icon.ico --name gitpr run.py
   ```
> **Nota técnica:** A flag `--onefile` garante que todo o Python, bibliotecas e dependências sejam compactados em um único binário, enquanto `--paths src` ajuda o compilador a encontrar nossos arquivos `core.py` e `config.py`. 🛠️

Após executar este comando, o PyInstaller criará algumas pastas (`build` e `dist`).
Seu arquivo final pronto para uso estará dentro da pasta **`dist/`** com o nome `gitpr` (ou `gitpr.exe` no Windows).


----

## 🧪 Executando Testes

Para garantir que a lógica de captura do Git e a integração com IA estão funcionando corretamente, utilizamos testes unitários.

1. Instale as dependências de teste (se ainda não o fez):
   ```bash
   pipenv install --dev pytest
   ```

2. Execute os testes com o comando:
   ```bash
   pipenv run pytest -v
   ```
O Pytest detectará automaticamente os arquivos dentro da pasta `tests/` e exibirá um relatório detalhado da execução.

----
## **⚙️ Instalação e Configuração**

### **Usando o Executável (Recomendado)**

1. Baixe o arquivo executável do gitpr na aba "Releases" do GitHub.
2. Mova o executável para uma pasta que esteja no seu PATH (ex.: /usr/local/bin no Linux/Mac ou sua pasta de usuário no Windows).
3. Na primeira execução, o assistente irá guiá-lo:
   ```bash
   $ gitpr
   ```
```bash
🚀 Automação Inteligente de PR com IA

🔧 Primeira execução detectada! Vamos configurar o GitPR CLI.

🔑 Digite sua GEMINI_API_KEY:

📄 Padrão do nome do arquivo de saída [{branch}_{datetime}_PR_DESC.md]:
```
*Nota: Sua configuração será salva com segurança no arquivo `~/.gitpr/.env`.*

> **🔒 Nota de Segurança:** O GitPR CLI utiliza criptografia simétrica (Fernet). Sua chave de API é armazenada como um hash no arquivo `.env`, e a chave mestra para descriptografia é gerada automaticamente em `~/.gitpr/secret.key`. **Nunca compartilhe seu arquivo secret.key.**

### A partir do Código-Fonte

1. Clone o repositório: `git clone https://github.com/natanfiuza/gitpr.git`

2. Entre na pasta: `cd gitpr`

3. Configure o ambiente:
```bash
pipenv install google-genai openai python-dotenv click cryptography
```
4. Execute: pipenv run python src/main.py

## **💻 Como Usar**

O GitPR possui um comportamento padrão poderoso e várias opções avançadas para auxiliá-lo no seu dia a dia como desenvolvedor.

### **Comportamento Padrão (Pull Request)**
Simplesmente execute o comando básico no seu terminal:
```bash
gitpr
```
A ferramenta sincronizará com o remoto (`git fetch`), comparará suas alterações com a branch main remota (ex.: `origin/main`) e gerará um arquivo Markdown (ex.: `feature-login_20260421110134_PR_DESC.md`) na raiz do seu projeto com a sugestão completa para o seu Pull Request.

### **Opções e Comandos Avançados**
Você pode utilizar as seguintes *flags* para ações específicas:

* `-c` ou `--commit`: Executa um `git diff` local e exibe **apenas a mensagem de commit sugerida**.
* `-r` ou `--review`: Realiza uma **Revisão de Código** detalhada das alterações locais.
* `-f` ou `--fullreview`: Realiza uma **Revisão de Código Completa** analisando todas as alterações desde a branch remota.
* `-i <arquivo>` ou `--input <arquivo>`: **Auditoria Completa de Arquivo.** Deve ser usado junto com `-r` ou `-f`; ignora o histórico do git e faz uma Revisão de Código do arquivo inteiro. Excelente para atuar como consultor em refatoração de código legado.
* `--provider <gemini|deepseek|ollama>`: Força o uso de uma IA específica apenas nesta execução, ignorando seu padrão salvo no `.env`.
* `--lang <código>`: Força o idioma da interface para esta execução (ex.: `en_us`, `pt_br`). Sobrepõe `GITPR_LANG` no `.env` sem persistir a alteração.
* `-ch` ou `--chat`: Abre o **Chat Interativo de Programação em Par** — um terminal TUI onde a IA vê seu diff atual e mantém uma conversa contextual. Possui memória por branch, comandos de barra (`/explain`, `/tests`, `/optimize`, `/clear`), auto-patching (F5), atualização de diff (F2) e exportação de sessão (F6).
* `-l` ou `--linter`: Executa **apenas o linter estático local** (sem chamadas de IA). Ideal para uso em pipelines CI/CD para bloquear código não conforme.
* `--mcp`: Inicia o GitPR como um **servidor MCP** (Model Context Protocol) via transporte stdio. Permite integração com VS Code, Cursor, Claude Desktop e outros editores compatíveis com MCP — expondo todas as capacidades de IA do GitPR como ferramentas diretamente dentro da sua IDE. Também disponível como o comando independente `gitpr-mcp`.
* `-ih` ou `--installhooks`: Instala automaticamente **Git Hooks locais** (`pre-commit` e `prepare-commit-msg`) no seu repositório.
* `-s` ou `--skill`: Cria os arquivos de template de contexto da IA (`.gitpr.commit.md`, `.gitpr.pr.md`, `.gitpr.review.md`, `.gitpr.filereview.md`, `.gitpr.issue.md`, `.gitpr.blame.md`) e o Linter (`.gitpr.linter.yml`) na raiz do projeto.
* `-is` ou `--issue`: Gera automaticamente um rascunho de **Issue padronizada** e abre uma interface interativa (TUI) para edição ou envio direto via API REST. Este recurso possui **3 mecanismos de contexto** dependendo da combinação de comandos:
  * **Issue de Novo Código (`gitpr -is`):** Lê o `git diff` atual. **Por que usar:** Ideal para documentar rapidamente a tarefa que você acabou de programar, antes de fazer o commit.
  * **Issue Épico/Release (`gitpr -is -ht`):** Lê o histórico completo da branch atual (Git Log + Cache de PR). **Por que usar:** Ideal para gerar documentação consolidada de uma release inteira ou de uma *feature* grande que levou vários dias/commits para ser concluída.
  * **Issue Arqueológica/Dívida Técnica (`gitpr -is -b arquivo:linhas`):** Lê a linha do tempo de uma regra de negócio específica. **Por que usar:** Ideal para documentar dívida técnica, explicando como um bloco de código legado evoluiu e por que precisa ser refatorado.
* `-h` ou `--help`: Mostra a ajuda geral com todas as opções. Use junto com outra flag para **ajuda contextual** (ex.: `gitpr -h --issue`, `gitpr -h --linter`) com um link direto para a documentação detalhada de cada funcionalidade.
* `-u` ou `--update`: Verifica e instala a versão mais recente do GitPR (Auto-Atualizador).

> **⚙️ Nota Técnica (--hook):** O GitPR possui uma flag oculta `--hook <arquivo>` que é acionada exclusivamente pelo sistema de Git Hooks em segundo plano. Ela permite que a IA injete a mensagem sugerida diretamente no arquivo temporário do Git, sem poluir seu terminal.
>
> **⚙️ Nota Técnica (--pre-save):** O GitPR possui uma flag de depuração oculta `--pre-save` que pode ser combinada com qualquer comando de IA (ex.: `gitpr -c --pre-save`). Antes de cada chamada de IA, ele salva o payload completo que será enviado ao modelo (instrução do sistema + prompt + contadores de caracteres) em um arquivo `_{ação}-{datahora}.json` na pasta atual, e então prossegue normalmente. Útil para inspecionar prompts muito grandes. Nota: quando a resposta vem do cache local, nenhuma chamada é feita e nenhum arquivo é gerado.

### 📦 Diffs Grandes (Map-Reduce)

Quando seu diff é grande demais para uma única chamada de IA (mais de ~90k tokens estimados), o GitPR automaticamente divide em lotes por arquivo, solicita um resumo técnico de cada parte à IA (Map) e unifica tudo na mensagem de commit, revisão ou descrição de PR final (Reduce). Nenhuma flag necessária — ele é ativado sob demanda e mostra o progresso no console.

📚 Documentação completa: [docs/map-reduce-diff.md](https://github.com/natanfiuza/gitpr/blob/main/docs/map-reduce-diff.md)

## 🛡️ Linter Local (Análise Estática)

O GitPR CLI permite definir regras rigorosas que serão validadas instantaneamente durante `--review` ou `--fullreview`, sem depender de IA. Isso é ideal para evitar que erros comuns (como `console.log` ou IPs de teste) cheguem ao repositório.

### Como configurar o `.gitpr.linter.yml`:
Ao executar `gitpr --skill`, um template será gerado. Você pode configurar regras usando Expressões Regulares (Regex):

```yaml
rules:
  - name: "check-localhost"
    extensions: ["js", "php"] # Extensões a serem validadas
    regex: 'http(s)?://(localhost|127\.0\.0\.1)' # O que procurar
    message: "🚨 Uso de localhost detectado no arquivo {file_name}"
    ignore_comments: true # Ignora se a linha estiver comentada
    ignore_paths: # Pastas ou arquivos ignorados (aceita *)
      - "vendor/*"
      - "node_modules/*"
```

O Linter analisa apenas as **linhas adicionadas** no seu `git diff`, garantindo uma execução focada e extremamente rápida. Se houver violações, elas aparecerão destacadas no topo do seu arquivo de revisão.

## 🧠 Arquitetura Multi-Modelo (Independente de IA)

O GitPR não está vinculado a uma única Inteligência Artificial. Durante a configuração inicial, o usuário pode escolher seu mecanismo padrão. Atualmente suportamos:
* **Google Gemini** (Padrão: `gemini-2.5-flash`)
* **DeepSeek** (Padrão: `deepseek-chat`)
* **Ollama** (Local) — execute modelos localmente sem internet, totalmente compatível com o formato da API OpenAI

Você pode alternar dinamicamente entre modelos configurando as variáveis `GEMINI_API_MODEL` ou `DEEPSEEK_API_MODEL` no seu arquivo `~/.gitpr/.env`, ou alternar em tempo real usando a flag `--provider`.

## 🎯 Sistema de "Skills" Personalizável (Engenharia de Prompt)

Em vez de ocultar instruções da IA no código-fonte, o GitPR utiliza arquivos Markdown locais que atuam como *Instruções do Sistema*. Ao executar `gitpr -s`, os seguintes arquivos são gerados na raiz do seu projeto para personalizar a "persona" da IA de acordo com as regras de negócio da sua empresa:

* `.gitpr.commit.md`: Regras para geração de mensagens de commit curtas.
* `.gitpr.pr.md`: Estrutura de tópicos necessária para a descrição do Pull Request.
* `.gitpr.review.md`: Define o foco arquitetural (ex.: SOLID, Clean Code) para análise de diff.
* `.gitpr.filereview.md`: Define regras rigorosas de coesão e acoplamento para auditoria completa de arquivos (usado com `--input`).
* `.gitpr.issue.md`: Define a estrutura e o nível de detalhamento necessários para geração de Issues padronizadas (usado com `--issue`).
* `.gitpr.blame.md`: Define o foco da análise arqueológica para rastreamento de código legado (usado com `--blame`).

## 🌐 Internacionalização (i18n)

O GitPR detecta automaticamente o idioma do seu sistema e exibe mensagens no seu idioma nativo. O sistema de i18n é inspirado no **helper `__()` do Laravel**:

* **Detecção automática:** Na primeira execução, o GitPR detecta o idioma do seu SO e o salva em `~/.gitpr/.env` (`GITPR_LANG`).
* **Arquivos de tradução:** Os pacotes de idioma são baixados automaticamente do repositório oficial para `~/.gitpr/langs/`.
* **Fallback para inglês:** Se uma tradução estiver ausente, o texto em inglês é exibido diretamente.
* **API para desenvolvedores:** Use `from src.i18n import __` e envolva todas as strings voltadas ao usuário com `__("Your text here")`.
* **Placeholders:** Suporta parâmetros nomeados — `__("Baixando {arquivo}...", arquivo="template.md")`.

Para forçar um idioma específico, defina `GITPR_LANG=pt_br` ou `GITPR_LANG=en` no `~/.gitpr/.env`.

> 📖 **Guia completo para desenvolvedores:** [docs/i18n_explanation.md](https://github.com/natanfiuza/gitpr/blob/main/docs/i18n_explanation.md) — arquitetura, padrões de uso, precauções com importação circular e como adicionar novos idiomas.

## 🔌 Integração MCP (Model Context Protocol)

O GitPR pode ser executado como um **servidor MCP**, expondo suas capacidades alimentadas por IA como ferramentas que o assistente de IA do seu editor pode invocar diretamente — sem necessidade de terminal. Isso possibilita um fluxo de trabalho totalmente integrado onde você pode gerar mensagens de commit, revisar código, executar linters, rastrear origem de código e criar issues sem sair da sua IDE.

### Editores Suportados

| Editor | Arquivo de Configuração |
| ------ | ----------------------- |
| **VS Code** | `.vscode/mcp.json` |
| **Cursor** | `.cursor/mcp.json` |
| **Claude Code** | `.mcp.json` |
| **Claude Desktop** | `claude_desktop_config.json` |
| **Zed** | `settings.json` |

### Configuração Rápida

Use o instalador integrado para configurar seu editor automaticamente:

```bash
gitpr-mcp --install vscode       # Cria .vscode/mcp.json
gitpr-mcp --install cursor        # Cria .cursor/mcp.json
gitpr-mcp --install claude-code   # Cria .mcp.json
gitpr-mcp --install claude        # Atualiza a configuração do Claude Desktop
gitpr-mcp --install zed           # Atualiza as configurações do Zed
gitpr-mcp --install auto          # Detecta e instala automaticamente para todos encontrados
```

O instalador cria o diretório de configuração se necessário, mescla com qualquer configuração existente (nunca sobrescreve outros servidores) e é seguro de executar várias vezes.

> A configuração manual também é suportada — veja [docs/mcp-integration.md](https://github.com/natanfiuza/gitpr/blob/main/docs/mcp-integration.md)
> para o formato JSON de configuração de cada editor.

Uma vez configurado, use linguagem natural no chat de IA do seu editor:

   * *"Revise minhas alterações atuais"* → chama `review_code`
   * *"Gere uma mensagem de commit"* → chama `generate_commit_message`
   * *"Crie uma descrição de PR"* → chama `generate_pr_description`
   * *"Execute o linter no meu diff"* → chama `run_linter`

### Ferramentas MCP Disponíveis

| Ferramenta | Descrição |
| ---------- | --------- |
| `get_git_context` | Branch atual, nome do repositório e URL remota |
| `analyze_diff` | Git diff das alterações não commitadas |
| `get_full_diff` | Diff completo em relação a origin/main |
| `generate_commit_message` | Mensagem de commit no padrão Conventional Commits gerada por IA |
| `review_code` | Revisão de código por IA das alterações locais |
| `full_review` | Revisão de código por IA de todas as alterações desde origin/main |
| `generate_pr_description` | Descrição completa de PR (título + corpo) |
| `run_linter` | Linter estático contra o `.gitpr.linter.yml` |
| `analyze_blame` | Git blame + classificação por IA |
| `generate_issue` | Issue estruturada a partir de diff, histórico ou blame |

📖 **Documentação completa:** [docs/mcp-integration.md](https://github.com/natanfiuza/gitpr/blob/main/docs/mcp-integration.md) — disponível em 5 idiomas (EN, PT-BR, PT-PT, ES, FR).

## 📚 Documentação Técnica e Guias Avançados

Para manter este README conciso, detalhamos as implementações mais avançadas focadas em **DevOps** e **Integração Contínua** em documentos separados.

Se você deseja implementar o GitPR como uma barreira de qualidade automatizada em sua equipe, confira os guias abaixo.

> 🌐 Cada guia está disponível em **5 idiomas** — adicione `.pt_br`, `.pt_pt`, `.fr_fr` ou `.es_es` antes da extensão `.md` para versões traduzidas (ex.: `docs/understanding_chat_functionality.pt_br.md`). O inglês é o padrão, sem sufixo.

### Chat e Funcionalidades Interativas

* [**🧠 Chat Interativo (Programação em Par)**](https://github.com/natanfiuza/gitpr/blob/main/docs/understanding_chat_functionality.md) — Como usar o chat de IA com memória, comandos de barra, auto-patch e exportação de sessão.

### DevOps e CI/CD

* [**Git Hooks Locais (Shift-Left)**](https://github.com/natanfiuza/gitpr/blob/main/docs/git-hooks-locais.md) — Como usar `gitpr --installhooks` para criar barreiras de proteção na máquina do desenvolvedor e usar IA para escrever mensagens de commit automaticamente.
* [**Linter Estático Personalizável**](https://github.com/natanfiuza/gitpr/blob/main/docs/linter-regras-customizadas.md) — Como criar regras de validação no `.gitpr.linter.yml` para CI/CD e hooks de pre-commit.
* [**Integração CI/CD (GitHub Actions)**](https://github.com/natanfiuza/gitpr/blob/main/docs/github-ci-linter.md) — Como executar o GitPR no pipeline para bloquear o "Merge" de PRs com violações.

### Funcionalidades Principais

* [**Pull Request (Modo Padrão)**](https://github.com/natanfiuza/gitpr/blob/main/docs/pr-descricao-padrao.md) — Fluxo completo para geração de descrições de PR sem flags.
* [**Revisão de Código com IA**](https://github.com/natanfiuza/gitpr/blob/main/docs/code-review-ia.md) — Guia dos modos de revisão (`--review`, `--fullreview`) e auditoria de arquivos (`--input`).
* [**Mensagens de Commit com IA**](https://github.com/natanfiuza/gitpr/blob/main/docs/commit-message-ia.md) — Como gerar mensagens no padrão Conventional Commits e integrar com Git Hooks.
* [**Geração de Issue e Interface TUI**](https://github.com/natanfiuza/gitpr/blob/main/docs/issue-tui-help.md) — Como usar a interface gráfica no terminal (TUI) e os 3 mecanismos de contexto para gerenciar Issues estruturadas.
* [**Arqueólogo de Código (Git Blame)**](https://github.com/natanfiuza/gitpr/blob/main/docs/blame-arqueologo.md) — Como rastrear a origem de regras de negócio com `git blame` e IA.
* [**Sistema de Skills e Templates**](https://github.com/natanfiuza/gitpr/blob/main/docs/skill-template.md) — Como personalizar o comportamento da IA com arquivos `.gitpr.*.md`.

### Configuração e Infraestrutura

* [**Provedores de IA**](https://github.com/natanfiuza/gitpr/blob/main/docs/providers-ia.md) — Configuração e seleção entre Google Gemini, DeepSeek e Ollama.
* [**Auto-Atualizador**](https://github.com/natanfiuza/gitpr/blob/main/docs/auto-update.md) — Como funciona a atualização automática (hot-swap) do GitPR.
* [**Integração e Segurança do Token GitHub (PAT)**](https://github.com/natanfiuza/gitpr/blob/main/docs/github-pat-integration.md) — Entenda como o GitPR cria issues diretamente no repositório com autenticação.
* [**Internacionalização (i18n)**](https://github.com/natanfiuza/gitpr/blob/main/docs/i18n_explanation.md) — Arquitetura, padrões de uso e como adicionar novos idiomas.
* [**Integração MCP**](https://github.com/natanfiuza/gitpr/blob/main/docs/mcp-integration.md) — Conecte o GitPR ao VS Code, Cursor e Claude Desktop via Model Context Protocol.

## ⚡ Sistema de Cache Local (Economia de Cota)

O GitPR possui um mecanismo de cache inteligente baseado em **MD5**. Sempre que você executa um comando (`--review`, `--commit`, etc.), a ferramenta gera um hash exato do seu código atual (diff) e instruções.
Se você executar o mesmo comando novamente sem alterar o código, o GitPR intercepta a requisição e retorna o resultado instantaneamente (em milissegundos) da pasta `~/.gitpr/cache/prompts/`, economizando seu tempo e suas cotas da API Gemini!

## 🔄 Auto-Atualizador (Atualização Over-The-Air)

Nunca mais se preocupe em baixar manualmente novas versões. O GitPR possui um Guardião de Conexão e um atualizador embutido:
* Ele verifica a disponibilidade da rede antes de iniciar para não bloquear seu fluxo de trabalho offline.
* A cada execução, ele verifica silenciosamente se há uma nova versão oficial na API do GitHub.
* Você pode forçar a verificação e instalação executando `gitpr --update` ou `gitpr -u`.
* A ferramenta utiliza a técnica *Hot-Swap*, baixando o novo `.exe` e substituindo a versão antiga de forma transparente.

## Publicação no PyPI

```bash
pipenv run python -m build
pipenv run twine upload dist/*
```
## **🤝 Como Contribuir**

Contribuições são muito bem-vindas! Para contribuir:

1. Faça um Fork do projeto.
2. Crie uma branch para sua *feature* (git checkout -b feature/NovaFuncionalidade).
3. Commit suas alterações (git commit -m 'feat: adicionar nova funcionalidade'). Dica: Use o próprio GitPR para gerar esta mensagem! 😄
4. Faça Push para a branch (git push origin feature/NovaFuncionalidade).
5. Abra um Pull Request.

## **✨ Agradecimentos e Autoria**

Projeto concebido e desenvolvido por:

**Natan Fiuza** - [contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br)

## **📄 Licença**

Este projeto está licenciado sob a **GNU Lesser General Public License v2.1 (LGPL-2.1)**. Consulte o arquivo LICENSE para mais detalhes.

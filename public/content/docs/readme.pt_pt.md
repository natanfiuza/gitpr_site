# **GitPR CLI 🚀**

<p align="center">
  <img src="https://raw.githubusercontent.com/natanfiuza/gitpr/main/docs/logo.png" alt="GitPR Logo" width="150">
</p>

O GitPR CLI é uma ferramenta de automação de linha de comandos que utiliza inteligência artificial **Google Gemini** e **DeepSeek** para analisar as suas alterações de código (git diff) ou ficheiros completos. A ferramenta gera automaticamente mensagens de commit no formato *Conventional Commits*, descrições detalhadas de Pull Requests e revisões de código profundas com o objetivo de reduzir a dívida técnica.

🌐 **Site:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/) · 📂 **Repositório:** [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)

## **🛠️ Tecnologias e Bibliotecas Utilizadas**

Este projeto foi desenvolvido em Python e utiliza as seguintes bibliotecas principais:

* [**Click**](https://click.palletsprojects.com/): Para criar uma interface de linha de comandos (CLI) robusta e intuitiva.
* [**Google GenAI**](https://pypi.org/project/google-genai/): SDK oficial para integração direta com a API Gemini.
* [**OpenAI**](https://pypi.org/project/openai/): Biblioteca utilizada devido à sua total compatibilidade com a poderosa API **DeepSeek**.
* [**Python-dotenv**](https://pypi.org/project/python-dotenv/): Para gestão segura de variáveis de ambiente.
* [**Pytest**](https://docs.pytest.org/): Para executar testes unitários de forma simples, colorida e legível na consola.
* [**Cryptography**](https://cryptography.io/): Para garantir que a sua `GEMINI_API_KEY` é armazenada de forma encriptada e segura no disco.
* [**PyYAML**](https://pyyaml.org/): Utilizado para ler e processar as regras personalizadas de análise estática do ficheiro `.gitpr.linter.yml`.
* [**Textual**](https://textual.textualize.io/): Biblioteca poderosa para criar Interfaces Gráficas de Terminal (TUI), utilizada no painel interativo de geração e edição de issues.
* [**Requests**](https://pypi.org/project/requests/): Biblioteca elegante e robusta para pedidos HTTP, utilizada para comunicar com a API REST do GitHub.
* [**MCP**](https://pypi.org/project/mcp/): SDK oficial Python para o Model Context Protocol, permitindo que o GitPR se integre diretamente com editores e IDEs alimentados por IA.

----

## 📦 Como Compilar o Executável Localmente

Se pretender gerar o seu próprio binário a partir do código fonte, utilizamos o **PyInstaller**. Certifique-se de que está na raiz do projeto com o ambiente virtual configurado.

1. Instale as dependências de desenvolvimento (se ainda não o fez):
   ```bash
   pipenv install --dev
   ```

2. Execute o comando de compilação apontando para o nosso ponto de entrada (`run.py`):
   ```bash
   pipenv run pyinstaller --noconfirm --onefile --icon=icon.ico --name gitpr run.py
   ```
> **Nota técnica:** A flag `--onefile` garante que todo o Python, bibliotecas e dependências são comprimidos num único binário, enquanto `--paths src` ajuda o compilador a encontrar os nossos ficheiros `core.py` e `config.py`. 🛠️

Após executar este comando, o PyInstaller criará algumas pastas (`build` e `dist`).
O seu ficheiro final pronto a usar estará dentro da pasta **`dist/`** com o nome `gitpr` (ou `gitpr.exe` no Windows).


----

## 🧪 Executar Testes

Para garantir que a lógica de captura do Git e a integração com IA estão a funcionar corretamente, utilizamos testes unitários.

1. Instale as dependências de teste (se ainda não o fez):
   ```bash
   pipenv install --dev pytest
   ```

2. Execute os testes com o comando:
   ```bash
   pipenv run pytest -v
   ```
O Pytest detetará automaticamente os ficheiros dentro da pasta `tests/` e exibirá um relatório de execução detalhado.

----
## **⚙️ Instalação e Configuração**

### **Usando o Executável (Recomendado)**

1. Descarregue o ficheiro executável gitpr a partir do separador "Releases" no GitHub.
2. Mova o executável para uma pasta que esteja no seu PATH (ex.: /usr/local/bin no Linux/Mac ou a sua pasta de utilizador no Windows).
3. Na primeira execução, o assistente guiá-lo-á:
   ```bash
   $ gitpr
   ```
```bash
🚀 Automação Inteligente de PR com IA

🔧 Primeira execução detetada! Vamos configurar o GitPR CLI.

🔑 Insira a sua GEMINI_API_KEY:

📄 Padrão predefinido do nome do ficheiro de saída [{branch}_{datetime}_PR_DESC.md]:
```
*Nota: A sua configuração será guardada de forma segura no ficheiro `~/.gitpr/.env`.*

> **🔒 Nota de Segurança:** O GitPR CLI utiliza encriptação simétrica (Fernet). A sua chave de API é armazenada como um hash no ficheiro `.env`, e a chave mestra para desencriptação é gerada automaticamente em `~/.gitpr/secret.key`. **Nunca partilhe o seu ficheiro secret.key.**

### A Partir do Código Fonte

1. Clone o repositório: `git clone https://github.com/natanfiuza/gitpr.git`

2. Entre na pasta: `cd gitpr`

3. Configure o ambiente:
```bash
pipenv install google-genai openai python-dotenv click cryptography
```
4. Execute: pipenv run python src/main.py

## **💻 Como Utilizar**

O GitPR tem um comportamento predefinido poderoso e várias opções avançadas para o ajudar no seu dia a dia como programador.

### **Comportamento Predefinido (Pull Request)**
Simplesmente execute o comando base no seu terminal:
```bash
gitpr
```
A ferramenta sincronizará com o repositório remoto (`git fetch`), comparará as suas alterações com a branch principal remota (ex.: `origin/main`) e gerará um ficheiro Markdown (ex.: `feature-login_20260421110134_PR_DESC.md`) na raiz do seu projeto com a sugestão completa para a sua Pull Request.

### **Opções e Comandos Avançados**
Pode passar as seguintes *flags* para ações específicas:

* `-c` ou `--commit`: Executa um `git diff` local e exibe **apenas a mensagem de commit sugerida**.
* `-r` ou `--review`: Realiza uma **Revisão de Código** detalhada das alterações locais.
* `-f` ou `--fullreview`: Realiza uma **Revisão de Código Completa** analisando todas as alterações desde a branch remota.
* `-i <ficheiro>` ou `--input <ficheiro>`: **Auditoria de Ficheiro Completa.** Deve ser usado juntamente com `-r` ou `-f`; ignora o histórico do git e faz uma Revisão de Código do ficheiro inteiro. Excelente para atuar como consultor em refatoração de código legado.
* `--provider <gemini|deepseek|ollama>`: Força a utilização de uma IA específica apenas para esta execução, ignorando a sua predefinição guardada em `.env`.
* `--lang <código>`: Força o idioma da interface para esta execução (ex.: `en_us`, `pt_br`). Substitui `GITPR_LANG` em `.env` sem persistir a alteração.
* `-ch` ou `--chat`: Abre o **Chat Interativo de Programação a Pares** — um terminal TUI onde a IA vê o seu diff atual e mantém uma conversa contextual. Inclui memória por branch, comandos de barra (`/explain`, `/tests`, `/optimize`, `/clear`), auto-correção (F5), atualização de diff (F2) e exportação de sessão (F6).
* `-l` ou `--linter`: Executa **apenas o linter estático local** (sem chamadas à IA). Ideal para usar em pipelines de CI/CD para bloquear código não conforme.
* `--mcp`: Inicia o GitPR como um **servidor MCP** (Model Context Protocol) em transporte stdio. Permite integração com VS Code, Cursor, Claude Desktop e outros editores compatíveis com MCP — expondo todas as capacidades de IA do GitPR como ferramentas diretamente no seu IDE. Também disponível como o comando autónomo `gitpr-mcp`.
* `-ih` ou `--installhooks`: Instala automaticamente **Git Hooks locais** (`pre-commit` e `prepare-commit-msg`) no seu repositório.
* `-s` ou `--skill`: Cria os ficheiros de template de contexto da IA (`.gitpr.commit.md`, `.gitpr.pr.md`, `.gitpr.review.md`, `.gitpr.filereview.md`, `.gitpr.issue.md`, `.gitpr.blame.md`) e o Linter (`.gitpr.linter.yml`) na raiz do projeto.
* `-is` ou `--issue`: Gera automaticamente um rascunho de uma **Issue padronizada** e abre uma interface interativa (TUI) para edição ou submissão direta via API REST. Esta funcionalidade tem **3 motores de contexto** dependendo da combinação de comandos:
  * **Issue de Novo Código (`gitpr -is`):** Lê o `git diff` atual. **Porquê usar:** Ideal para documentar rapidamente a tarefa que acabou de programar, antes de fazer commit.
  * **Issue Épico/Release (`gitpr -is -ht`):** Lê o histórico completo da branch atual (Git Log + Cache de PR). **Porquê usar:** Ideal para gerar documentação consolidada de uma release completa ou de uma *feature* grande que demorou vários dias/commits a concluir.
  * **Issue Arqueológica/Dívida Técnica (`gitpr -is -b ficheiro:linhas`):** Lê a linha temporal de uma regra de negócio específica. **Porquê usar:** Ideal para documentar dívida técnica, explicando como um bloco de código legado evoluiu e porque precisa de ser refatorado.
* `-h` ou `--help`: Mostra a ajuda geral com todas as opções. Use juntamente com outra flag para **ajuda contextual** (ex.: `gitpr -h --issue`, `gitpr -h --linter`) com uma ligação direta para a documentação detalhada de cada funcionalidade.
* `-u` ou `--update`: Verifica e instala a versão mais recente do GitPR (Atualização Automática).

> **⚙️ Nota Técnica (--hook):** O GitPR tem uma flag oculta `--hook <ficheiro>` que é acionada exclusivamente pelo sistema de Git Hooks em segundo plano. Permite que a IA injete a mensagem sugerida diretamente no ficheiro temporário do Git, sem poluir o seu terminal.
>
> **⚙️ Nota Técnica (--pre-save):** O GitPR tem uma flag de depuração oculta `--pre-save` que pode ser combinada com qualquer comando de IA (ex.: `gitpr -c --pre-save`). Antes de cada chamada à IA, guarda o payload completo que será enviado ao modelo (instrução de sistema + prompt + contadores de caracteres) num ficheiro `_{ação}-{datetime}.json` na pasta atual e depois prossegue normalmente. Útil para inspecionar prompts muito grandes. Nota: quando a resposta vem da cache local, nenhuma chamada é feita e nenhum ficheiro é gerado.

### 📦 Diffs Grandes (Map-Reduce)

Quando o seu diff é demasiado grande para uma única chamada à IA (mais de ~90k tokens estimados), o GitPR divide-o automaticamente em lotes por ficheiro, pede à IA um resumo técnico de cada parte (Map) e unifica tudo na mensagem de commit final, revisão ou descrição de PR (Reduce). Não são necessárias flags — ativa-se sob demanda e mostra o progresso na consola.

📚 Documentação completa: [docs/map-reduce-diff.md](https://github.com/natanfiuza/gitpr/blob/main/docs/map-reduce-diff.md)

## 🛡️ Linter Local (Análise Estática)

O GitPR CLI permite-lhe definir regras rigorosas que serão validadas instantaneamente durante `--review` ou `--fullreview`, sem depender da IA. Isto é ideal para evitar que erros comuns (como `console.log` ou IPs de teste) cheguem ao repositório.

### Como configurar `.gitpr.linter.yml`:
Ao executar `gitpr --skill`, será gerado um template. Pode configurar regras usando Expressões Regulares (Regex):

```yaml
rules:
  - name: "check-localhost"
    extensions: ["js", "php"] # Extensões a validar
    regex: 'http(s)?://(localhost|127\.0\.0\.1)' # O que procurar
    message: "🚨 Utilização de localhost detetada no ficheiro {file_name}"
    ignore_comments: true # Ignora se a linha estiver comentada
    ignore_paths: # Pastas ou ficheiros ignorados (aceita *)
      - "vendor/*"
      - "node_modules/*"
```

O Linter analisa apenas as **linhas adicionadas** no seu `git diff`, garantindo uma execução focada e extremamente rápida. Se houver violações, estas aparecerão destacadas no topo do seu ficheiro de revisão.

## 🧠 Arquitetura Multi-Modelo (Agnística em Relação à IA)

O GitPR não está vinculado a uma única Inteligência Artificial. Durante a configuração inicial, o utilizador pode escolher o seu motor predefinido. Atualmente suportamos:
* **Google Gemini** (Predefinição: `gemini-2.5-flash`)
* **DeepSeek** (Predefinição: `deepseek-chat`)
* **Ollama** (Local) — execute modelos localmente sem internet, totalmente compatível com o formato da API OpenAI

Pode alternar dinamicamente entre modelos configurando as variáveis `GEMINI_API_MODEL` ou `DEEPSEEK_API_MODEL` no seu ficheiro `~/.gitpr/.env`, ou alternar em tempo real usando a flag `--provider`.

## 🎯 Sistema de "Skills" Personalizáveis (Engenharia de Prompt)

Em vez de esconder instruções da IA no código fonte, o GitPR utiliza ficheiros Markdown locais que funcionam como *Instruções de Sistema*. Ao executar `gitpr -s`, os seguintes ficheiros são gerados na raiz do seu projeto para personalizar a "persona" da IA de acordo com as regras de negócio da sua empresa:

* `.gitpr.commit.md`: Regras para gerar mensagens de commit curtas.
* `.gitpr.pr.md`: Estrutura de tópicos necessária para a descrição da Pull Request.
* `.gitpr.review.md`: Define o foco arquitetural (ex.: SOLID, Clean Code) para análise de diff.
* `.gitpr.filereview.md`: Define regras rigorosas de coesão e acoplamento para auditoria de ficheiros completos (usado com `--input`).
* `.gitpr.issue.md`: Define a estrutura e o nível de detalhe necessários para gerar Issues padronizadas (usado com `--issue`).
* `.gitpr.blame.md`: Define o foco da análise arqueológica para rastreio de código legado (usado com `--blame`).

## 🌐 Internacionalização (i18n)

O GitPR deteta automaticamente o idioma do seu sistema e exibe mensagens no seu idioma nativo. O sistema de i18n é inspirado no **helper `__()` do Laravel**:

* **Deteção automática:** Na primeira execução, o GitPR deteta o idioma do seu SO e guarda-o em `~/.gitpr/.env` (`GITPR_LANG`).
* **Ficheiros de tradução:** Os pacotes de idiomas são descarregados automaticamente do repositório oficial para `~/.gitpr/langs/`.
* **Idioma de reserva (inglês):** Se faltar uma tradução, o texto em inglês é exibido diretamente.
* **API para programadores:** Use `from src.i18n import __` e envolva todas as strings visíveis ao utilizador com `__("O seu texto aqui")`.
* **Placeholders:** Suporta parâmetros nomeados — `__("A descarregar {ficheiro}...", ficheiro="template.md")`.

Para forçar um idioma específico, defina `GITPR_LANG=pt_br` ou `GITPR_LANG=en` em `~/.gitpr/.env`.

> 📖 **Guia completo para programadores:** [docs/i18n_explanation.md](https://github.com/natanfiuza/gitpr/blob/main/docs/i18n_explanation.md) — arquitetura, padrões de utilização, precauções com importações circulares e como adicionar novos idiomas.

## 🔌 Integração MCP (Model Context Protocol)

O GitPR pode ser executado como um **servidor MCP**, expondo as suas capacidades alimentadas por IA como ferramentas que o assistente de IA do seu editor pode invocar diretamente — sem necessidade do terminal. Isto permite um fluxo de trabalho totalmente integrado onde pode gerar mensagens de commit, rever código, executar linters, rastrear origens de código e criar issues sem sair do seu IDE.

### Editores Suportados

| Editor | Ficheiro de Configuração |
| ------ | ----------- |
| **VS Code** | `.vscode/mcp.json` |
| **Cursor** | `.cursor/mcp.json` |
| **Claude Code** | `.mcp.json` |
| **Claude Desktop** | `claude_desktop_config.json` |
| **Zed** | `settings.json` |

### Configuração Rápida

Utilize o instalador integrado para configurar o seu editor automaticamente:

```bash
gitpr-mcp --install vscode    # Cria .vscode/mcp.json
gitpr-mcp --install cursor      # Cria .cursor/mcp.json
gitpr-mcp --install claude-code # Cria .mcp.json
gitpr-mcp --install claude      # Atualiza a configuração do Claude Desktop
gitpr-mcp --install zed         # Atualiza as definições do Zed
gitpr-mcp --install auto      # Deteção automática e instalação para todos os encontrados
```

O instalador cria o diretório de configuração se necessário, faz a fusão com qualquer configuração existente (nunca substitui outros servidores) e é seguro de executar múltiplas vezes.

> A configuração manual também é suportada — consulte [docs/mcp-integration.md](https://github.com/natanfiuza/gitpr/blob/main/docs/mcp-integration.md)
> para o formato JSON de configuração para cada editor.

Uma vez configurado, use linguagem natural no chat de IA do seu editor:

   * *"Review my current changes"* → chama `review_code`
   * *"Generate a commit message"* → chama `generate_commit_message`
   * *"Create a PR description"* → chama `generate_pr_description`
   * *"Run the linter on my diff"* → chama `run_linter`

### Ferramentas MCP Disponíveis

| Ferramenta | Descrição |
| ---- | ----------- |
| `get_git_context` | Branch atual, nome do repositório e URL remoto |
| `analyze_diff` | Git diff das alterações não commitadas |
| `get_full_diff` | Diff completo contra origin/main |
| `generate_commit_message` | Mensagem de commit no formato Conventional Commits gerada por IA |
| `review_code` | Revisão de código IA das alterações locais |
| `full_review` | Revisão de código IA de todas as alterações desde origin/main |
| `generate_pr_description` | Descrição completa da PR (título + corpo) |
| `run_linter` | Linter estático contra `.gitpr.linter.yml` |
| `analyze_blame` | Git blame + classificação IA |
| `generate_issue` | Issue estruturada a partir de diff, histórico ou blame |

📖 **Documentação completa:** [docs/mcp-integration.md](https://github.com/natanfiuza/gitpr/blob/main/docs/mcp-integration.md) — disponível em 5 idiomas (EN, PT-BR, PT-PT, ES, FR).

## 📚 Documentação Técnica e Guias Avançados

Para manter este README conciso, detalhamos as implementações mais avançadas focadas em **DevOps** e **Integração Contínua** em documentos separados.

Se pretende implementar o GitPR como uma barreira de qualidade automatizada na sua equipa, consulte os guias abaixo.

> 🌐 Cada guia está disponível em **5 idiomas** — adicione `.pt_br`, `.pt_pt`, `.fr_fr` ou `.es_es` antes da extensão `.md` para versões traduzidas (ex.: `docs/understanding_chat_functionality.pt_br.md`). O inglês é o idioma predefinido, sem sufixo.

### Chat & Funcionalidades Interativas

* [**🧠 Chat Interativo (Programação a Pares)**](https://github.com/natanfiuza/gitpr/blob/main/docs/understanding_chat_functionality.md) — Como usar o chat de IA com memória, comandos de barra, auto-correção e exportação de sessão.

### DevOps & CI/CD

* [**Git Hooks Locais (Shift-Left)**](https://github.com/natanfiuza/gitpr/blob/main/docs/git-hooks-locais.md) — Como usar `gitpr --installhooks` para criar barreiras de proteção na máquina do programador e usar IA para escrever mensagens de commit automaticamente.
* [**Linter Estático Personalizável**](https://github.com/natanfiuza/gitpr/blob/main/docs/linter-regras-customizadas.md) — Como criar regras de validação em `.gitpr.linter.yml` para CI/CD e hooks de pre-commit.
* [**Integração CI/CD (GitHub Actions)**](https://github.com/natanfiuza/gitpr/blob/main/docs/github-ci-linter.md) — Como executar o GitPR no pipeline para bloquear o "Merge" de PRs com violações.

### Funcionalidades Principais

* [**Pull Request (Modo Predefinido)**](https://github.com/natanfiuza/gitpr/blob/main/docs/pr-descricao-padrao.md) — Fluxo completo para gerar descrições de PR sem flags.
* [**Revisão de Código com IA**](https://github.com/natanfiuza/gitpr/blob/main/docs/code-review-ia.md) — Guia para modos de revisão (`--review`, `--fullreview`) e auditoria de ficheiros (`--input`).
* [**Mensagens de Commit com IA**](https://github.com/natanfiuza/gitpr/blob/main/docs/commit-message-ia.md) — Como gerar mensagens no formato Conventional Commits e integrar com Git Hooks.
* [**Geração de Issues e Interface TUI**](https://github.com/natanfiuza/gitpr/blob/main/docs/issue-tui-help.md) — Como usar a interface gráfica de terminal (TUI) e os 3 motores de contexto para gerir Issues estruturadas.
* [**Arqueólogo de Código (Git Blame)**](https://github.com/natanfiuza/gitpr/blob/main/docs/blame-arqueologo.md) — Como rastrear a origem de regras de negócio com `git blame` e IA.
* [**Sistema de Skills e Templates**](https://github.com/natanfiuza/gitpr/blob/main/docs/skill-template.md) — Como personalizar o comportamento da IA com ficheiros `.gitpr.*.md`.

### Configuração & Infraestrutura

* [**Fornecedores de IA**](https://github.com/natanfiuza/gitpr/blob/main/docs/providers-ia.md) — Configuração e seleção entre Google Gemini, DeepSeek e Ollama.
* [**Atualização Automática**](https://github.com/natanfiuza/gitpr/blob/main/docs/auto-update.md) — Como funciona a atualização automática (hot-swap) do GitPR.
* [**Token GitHub (PAT) e Segurança da Integração**](https://github.com/natanfiuza/gitpr/blob/main/docs/github-pat-integration.md) — Compreenda como o GitPR cria issues diretamente no repositório com autenticação.
* [**Internacionalização (i18n)**](https://github.com/natanfiuza/gitpr/blob/main/docs/i18n_explanation.md) — Arquitetura, padrões de utilização e como adicionar novos idiomas.
* [**Integração MCP**](https://github.com/natanfiuza/gitpr/blob/main/docs/mcp-integration.md) — Conecte o GitPR ao VS Code, Cursor e Claude Desktop via Model Context Protocol.

## ⚡ Sistema de Cache Local (Economia de Quotas)

O GitPR possui um motor de cache inteligente baseado em **MD5**. Sempre que executa um comando (`--review`, `--commit`, etc.), a ferramenta gera um hash exato do seu código atual (diff) e instruções.
Se executar o mesmo comando novamente sem alterar o código, o GitPR interceta o pedido e devolve o resultado instantaneamente (em milissegundos) a partir da pasta `~/.gitpr/cache/prompts/`, poupando-lhe tempo e as suas quotas da API Gemini!

## 🔄 Atualização Automática (Atualização Over-The-Air)

Nunca mais se preocupe em descarregar manualmente novas versões. O GitPR tem um Guardião de Ligação e um atualizador integrado:
* Verifica a disponibilidade da rede antes de iniciar para não bloquear o seu fluxo de trabalho offline.
* Em cada execução, verifica silenciosamente se existe uma nova release oficial na API do GitHub.
* Pode forçar a verificação e instalação executando `gitpr --update` ou `gitpr -u`.
* A ferramenta usa a técnica *Hot-Swap*, descarregando o novo `.exe` e substituindo transparentemente a versão antiga.

## Publicação no PyPI

```bash
pipenv run python -m build
pipenv run twine upload dist/*
```
## **🤝 Como Contribuir**

Contribuições são muito bem-vindas! Para contribuir:

1. Faça fork do projeto.
2. Crie uma branch para a sua *feature* (git checkout -b feature/NovaFuncionalidade).
3. Faça commit das suas alterações (git commit -m 'feat: adicionar nova funcionalidade'). Dica: Utilize o próprio GitPR para gerar esta mensagem! 😄
4. Envie para a branch (git push origin feature/NovaFuncionalidade).
5. Abra uma Pull Request.

## **✨ Agradecimentos e Autoria**

Projeto concebido e desenvolvido por:

**Natan Fiuza** - [contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br)

## **📄 Licença**

Este projeto está licenciado sob a **GNU Lesser General Public License v2.1 (LGPL-2.1)**. Consulte o ficheiro LICENSE para mais detalhes.

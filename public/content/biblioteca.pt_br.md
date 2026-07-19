# Tecnologias e Bibliotecas

O GitPR CLI é construído em **Python** e utiliza um conjunto cuidadosamente selecionado de bibliotecas para oferecer uma experiência rápida, segura e amigável.

---

## Bibliotecas Principais

### [Click](https://click.palletsprojects.com/)
Framework CLI robusto para construir interfaces de linha de comando componíveis e amigáveis. Alimenta todos os comandos, flags e formatação de terminal do GitPR.

### [Google GenAI SDK](https://pypi.org/project/google-genai/)
SDK oficial para integração direta com a **API Gemini**. Usado para code reviews, mensagens de commit e descrições de PR com IA.

### [OpenAI SDK](https://pypi.org/project/openai/)
Usado por sua total compatibilidade com a **API DeepSeek** e **Ollama** (modelos locais). Permite a arquitetura multi-provedor sem dependência de fornecedor.

### [Textual](https://textual.textualize.io/)
Framework TUI poderoso para construir interfaces de terminal ricas. Impulsiona o **chat interativo** (`--chat`), editor de issues e visualizador de diff em tempo real.

---

## Segurança e Configuração

### [Cryptography](https://cryptography.io/)
Fornece **criptografia simétrica Fernet** para armazenar chaves de API com segurança em disco. Sua `GEMINI_API_KEY` nunca é salva em texto puro.

### [Python-dotenv](https://pypi.org/project/python-dotenv/)
Gerencia variáveis de ambiente no arquivo de configuração `~/.gitpr/.env`, mantendo organizadas as configurações de provedores e preferências de idioma.

### [PyYAML](https://pyyaml.org/)
Faz o parsing das regras de análise estática definidas em `.gitpr.linter.yml`, permitindo definições de regras legíveis em YAML para o motor do linter.

---

## Testes e HTTP

### [Pytest](https://docs.pytest.org/)
Framework de testes moderno com saída colorida e legível no console. Usado para testes unitários e de integração em todos os módulos.

### [Requests](https://pypi.org/project/requests/)
Biblioteca HTTP elegante para comunicação com a API REST do GitHub — usada pelo auto-updater, envio de issues e verificação de releases.

---

## Por que Python?

Python foi escolhido por seu **ciclo de desenvolvimento rápido**, **compatibilidade multiplataforma** (Windows, macOS, Linux), rico ecossistema de bibliotecas de IA/LLM e a capacidade de compilar em um único binário sem dependências com **PyInstaller**.

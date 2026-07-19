# Contribuindo com o GitPR CLI

Contribuições são muito bem-vindas! Veja como participar.

---

## Como Contribuir

1. **Faça um Fork** do projeto no [GitHub](https://github.com/natafiuza/gitpr)
2. **Crie um ramo** para a sua funcionalidade:
   ```bash
   git checkout -b feature/NovaFuncionalidade
   ```
3. **Faça commit das suas alterações:**
   ```bash
   git commit -m 'feat: adicionar nova funcionalidade'
   ```
   > 💡 **Dica:** Use o próprio GitPR para gerar esta mensagem de commit! Basta executar `gitpr -c`.

4. **Envie** para o seu ramo:
   ```bash
   git push origin feature/NovaFuncionalidade
   ```
5. **Abra um Pull Request** no repositório principal

---

## Configuração de Desenvolvimento

```bash
# Clonar e entrar
git clone https://github.com/natafiuza/gitpr.git
cd gitpr

# Instalar todas as dependências (incluindo dev)
pipenv install --dev

# Executar testes
pipenv run pytest -v

# Executar GitPR a partir do código fonte
pipenv run python src/main.py
```

---

## Executando Testes

```bash
# Executar todos os testes com saída detalhada
pipenv run pytest -v

# Executar um ficheiro de teste específico
pipenv run pytest tests/test_core.py -v

# Executar com relatório de cobertura
pipenv run pytest --cov=src --cov-report=term-missing
```

---

## Áreas para Contribuir

| Área | Descrição |
| --- | --- |
| **Novos Fornecedores** | Adicionar suporte para fornecedores de IA adicionais (Claude, LLMs locais, etc.) |
| **Novos Idiomas** | Traduzir o GitPR para o seu idioma |
| **Regras de Linter** | Partilhar conjuntos úteis de regras para diferentes stacks |
| **Documentação** | Melhorar documentação, adicionar exemplos, corrigir erros |
| **Correção de Bugs** | Verificar o separador de issues para bugs reportados |
| **Melhorias na TUI** | Melhorar a interface do chat interativo e editor de issues |

---

## Estrutura do Projeto

```
gitpr/
├── src/
│   ├── main.py           # Ponto de entrada CLI e encaminhamento de comandos
│   ├── core.py            # Operações Git e integração com IA
│   ├── config.py          # Gestão de configuração e .env
│   ├── security.py        # Encriptação (Fernet)
│   ├── linter_engine.py   # Motor de análise estática
│   ├── updater.py         # Auto-updater (hot-swap)
│   └── i18n.py            # Helper de internacionalização
├── tests/                 # Testes unitários e de integração
├── langs/                 # Ficheiros de tradução (JSON)
├── docs/                  # Documentação estendida
└── run.py                 # Ponto de entrada do PyInstaller
```

---

## Publicando no PyPI

O [PyPI](https://pypi.org/project/gitpr-cli/) (Python Package Index) é o repositório oficial de pacotes Python — como o npm para JavaScript ou o Packagist para PHP. Publicar no PyPI permite que utilizadores Linux e macOS instalem o GitPR com um único comando:

```bash
pip install gitpr-cli
```

### Como Funciona a Publicação

Apenas o mantenedor do projeto (**Natan Fiuza**) possui as credenciais necessárias para publicar novas versões. Isto é intencional — garante que apenas código verificado e revisto chega aos utilizadores finais através do canal oficial.

O processo em duas etapas:

| Comando | O que faz |
| --- | --- |
| `pipenv run python -m build` | Empacota o código fonte em ficheiros distribuíveis `.tar.gz` e `.whl` na pasta `dist/` |
| `pipenv run twine upload dist/*` | Envia esses pacotes para o PyPI usando o token autenticado do mantenedor |

### Para Contribuidores

Não precisa de acesso ao PyPI para contribuir! Fork o repositório, faça as suas alterações e envie um Pull Request. Após o merge, o mantenedor incluirá a sua contribuição na próxima release do PyPI.

> 📦 **Página do projeto no PyPI:** [pypi.org/project/gitpr-cli](https://pypi.org/project/gitpr-cli/)

---

## Licença

Este projeto está licenciado sob a **GNU Lesser General Public License v2.1 (LGPL-2.1)**.

Consulte o ficheiro [LICENSE](https://github.com/natafiuza/gitpr/blob/main/LICENSE) para detalhes completos.

---

## Agradecimentos

### Criador e Mantenedor

Projeto idealizado e desenvolvido por **Natan Fiuza** — [contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br)

### Contribuidores

Obrigado a todos que já contribuíram com o GitPR CLI:

::: collaborators
https://github.com/natanfiuza
:::

> 💡 **Quer aparecer aqui?** [Contribua com o projeto →](#como-contribuir)

---

[← Cache e Atualizações](/cache)

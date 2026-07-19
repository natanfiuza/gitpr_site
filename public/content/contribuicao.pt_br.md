# Contribuindo com o GitPR CLI

Contribuições são muito bem-vindas! Veja como participar.

---

## Como Contribuir

1. **Faça um Fork** do projeto no [GitHub](https://github.com/natafiuza/gitpr)
2. **Crie uma branch** para sua funcionalidade:
   ```bash
   git checkout -b feature/NovaFuncionalidade
   ```
3. **Faça commit das suas alterações:**
   ```bash
   git commit -m 'feat: adicionar nova funcionalidade'
   ```
   > 💡 **Dica:** Use o próprio GitPR para gerar esta mensagem de commit! Basta executar `gitpr -c`.

4. **Envie** para sua branch:
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

# Executar um arquivo de teste específico
pipenv run pytest tests/test_core.py -v

# Executar com relatório de cobertura
pipenv run pytest --cov=src --cov-report=term-missing
```

---

## Áreas para Contribuir

| Área | Descrição |
| --- | --- |
| **Novos Provedores** | Adicionar suporte para provedores de IA adicionais (Claude, LLMs locais, etc.) |
| **Novos Idiomas** | Traduzir o GitPR para seu idioma |
| **Regras de Linter** | Compartilhar conjuntos úteis de regras para diferentes stacks |
| **Documentação** | Melhorar documentação, adicionar exemplos, corrigir erros |
| **Correção de Bugs** | Verificar a aba de issues para bugs reportados |
| **Melhorias na TUI** | Melhorar a interface do chat interativo e editor de issues |

---

## Estrutura do Projeto

```
gitpr/
├── src/
│   ├── main.py           # Ponto de entrada CLI e roteamento de comandos
│   ├── core.py            # Operações Git e integração com IA
│   ├── config.py          # Gerenciamento de configuração e .env
│   ├── security.py        # Criptografia (Fernet)
│   ├── linter_engine.py   # Motor de análise estática
│   ├── updater.py         # Auto-updater (hot-swap)
│   └── i18n.py            # Helper de internacionalização
├── tests/                 # Testes unitários e de integração
├── langs/                 # Arquivos de tradução (JSON)
├── docs/                  # Documentação estendida
└── run.py                 # Ponto de entrada do PyInstaller
```

---

## Publicando no PyPI

Para mantenedores:

```bash
pipenv run python -m build
pipenv run twine upload dist/*
```

---

## Licença

Este projeto está licenciado sob a **GNU Lesser General Public License v2.1 (LGPL-2.1)**.

Consulte o arquivo [LICENSE](https://github.com/natafiuza/gitpr/blob/main/LICENSE) para detalhes completos.

---

## Agradecimentos

Projeto idealizado e desenvolvido por **Natan Fiuza** — [contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br)

---

[← Cache e Atualizações](/cache)

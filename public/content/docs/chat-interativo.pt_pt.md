# 💬 Chat Interativo (TUI)

O GitPR inclui uma interface de chat interativa completa construída com **Textual**, permitindo conversas de pair programming com IA diretamente no seu terminal.

## Funcionalidades

- **Histórico de mensagens** — A sua conversa é persistida por ramo, permitindo continuar de onde parou.
- **Input multi-linha** — Escreva prompts longos com navegação completa por teclado.
- **Comandos Slash** — Use `/explain`, `/tests`, `/optimize` e `/clear` para ações rápidas.
- **Auto-Patching (F5)** — Extraia blocos de código sugeridos pela IA para um ficheiro de patch.
- **Atualização de Diff (F2)** — Recarregue o `git diff` sem reiniciar a sessão.
- **Exportação de Sessão (F6)** — Guarde o histórico completo do chat em Markdown.

## Como iniciar

```bash
gitpr --chat          # Abre a TUI interativa de chat
gitpr -c --chat       # Inicia com o diff atual carregado
```

👉 Para um guia completo, veja a [Documentação de Funcionalidade do Chat](/docs/understanding_chat_functionality?lang=pt_pt).

🔗 Repositório: [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)

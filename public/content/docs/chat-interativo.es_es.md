# 💬 Chat Interactivo (TUI)

GitPR incluye una interfaz de chat interactiva completa construida con **Textual**, que permite conversaciones de pair programming con IA directamente en tu terminal.

## Funcionalidades

- **Historial de mensajes** — Tu conversación se persiste por rama, permitiendo retomar donde lo dejaste.
- **Input multi-línea** — Escribe prompts largos con navegación completa por teclado.
- **Comandos Slash** — Usa `/explain`, `/tests`, `/optimize` y `/clear` para acciones rápidas.
- **Auto-Patching (F5)** — Extrae bloques de código sugeridos por la IA a un archivo patch.
- **Actualización de Diff (F2)** — Recarga el `git diff` sin reiniciar la sesión.
- **Exportación de Sesión (F6)** — Guarda el historial completo del chat en Markdown.

## Cómo iniciar

```bash
gitpr --chat          # Abre la TUI interactiva de chat
gitpr -c --chat       # Inicia con el diff actual cargado
```

👉 Para una guía completa, consulta la [Documentación del Chat](/docs/understanding_chat_functionality?lang=es).

🔗 Repositorio: [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)

# 💬 Chat Interactif (TUI)

GitPR inclut une interface de chat interactive complète construite avec **Textual**, permettant des conversations de pair programming avec l'IA directement dans votre terminal.

## Fonctionnalités

- **Historique des messages** — Votre conversation est persistée par branche, vous permettant de reprendre où vous vous étiez arrêté.
- **Input multi-ligne** — Écrivez de longs prompts avec une navigation complète au clavier.
- **Commandes Slash** — Utilisez `/explain`, `/tests`, `/optimize` et `/clear` pour des actions rapides.
- **Auto-Patching (F5)** — Extrayez les blocs de code suggérés par l'IA dans un fichier patch.
- **Rafraîchissement du Diff (F2)** — Rechargez le `git diff` sans redémarrer la session.
- **Export de Session (F6)** — Sauvegardez l'historique complet du chat en Markdown.

## Comment démarrer

```bash
gitpr --chat          # Ouvre la TUI interactive de chat
gitpr -c --chat       # Démarre avec le diff actuel chargé
```

👉 Pour un guide complet, consultez la [Documentation du Chat](/docs/understanding_chat_functionality?lang=fr).

🔗 Dépôt : [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)

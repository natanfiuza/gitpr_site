# Fournisseurs IA — Architecture Multi-Modèle

GitPR est **agnostique de l'IA** : il n'est pas lié à un seul modèle ou fournisseur. Lors de la configuration, choisissez votre moteur par défaut. Changez à tout moment avec un seul flag.

---

## Fournisseurs Supportés

### Google Gemini

Le fournisseur par défaut et recommandé. Rapide, économique et nativement supporté.

```bash
# Définir par défaut dans ~/.gitpr/.env
GITPR_PROVIDER=gemini
GEMINI_API_MODEL=gemini-2.5-flash

# Ou utiliser une fois
gitpr --provider gemini
```

**Modèle par défaut :** `gemini-2.5-flash`

**Obtenez votre clé API :** [Google AI Studio](https://aistudio.google.com/apikey)

---

### DeepSeek

Alternative puissante avec d'excellentes capacités d'analyse de code. Utilise le SDK compatible OpenAI.

```bash
# Définir par défaut dans ~/.gitpr/.env
GITPR_PROVIDER=deepseek
DEEPSEEK_API_MODEL=deepseek-chat

# Ou utiliser une fois
gitpr --provider deepseek
```

**Modèle par défaut :** `deepseek-chat`

**Obtenez votre clé API :** [DeepSeek Platform](https://platform.deepseek.com/api_keys)

---

### Ollama (Local)

Exécutez des modèles **totalement hors ligne** — sans internet, sans clés API, sans quotas.

```bash
# Définir par défaut dans ~/.gitpr/.env
GITPR_PROVIDER=ollama

# Ou utiliser une fois
gitpr --provider ollama
```

Ollama utilise le format d'API compatible OpenAI, rendant l'intégration transparente. Les modèles s'exécutent localement sur votre machine avec une confidentialité totale.

**Commencez ici :** [Ollama](https://ollama.com/)

---

## Comparaison des Fournisseurs

| Caractéristique | Gemini | DeepSeek | Ollama |
| --- | --- | --- | --- |
| **Internet requis** | Oui | Oui | Non |
| **Clé API requise** | Oui | Oui | Non |
| **Coût** | Niveau gratuit disponible | Payant | Gratuit |
| **Confidentialité** | Cloud | Cloud | Totalement local |
| **Vitesse** | Rapide | Rapide | Dépend du matériel |
| **Idéal pour** | Usage quotidien | Analyse approfondie | Hors ligne/air-gapped |

---

## Personnalisation des Modèles

Remplacez la version par défaut du modèle par fournisseur :

```bash
# ~/.gitpr/.env
GEMINI_API_MODEL=gemini-2.5-pro     # Pour des analyses plus lourdes
DEEPSEEK_API_MODEL=deepseek-reasoner # Pour le raisonnement complexe
```

---

## Comment Ça Marche

GitPR utilise une **couche d'abstraction de fournisseur** qui normalise les appels API quel que soit le moteur sous-jacent. L'outil :

1. Lit votre préférence de fournisseur depuis `~/.gitpr/.env` (ou le flag `--provider`)
2. Achemine le prompt vers le SDK approprié (Google GenAI, compatible OpenAI, ou Ollama local)
3. Parse la réponse dans un format unifié
4. Applique le cache, le map-reduce et le formatage de sortie de manière identique pour tous les fournisseurs

Cela signifie que changer de fournisseur modifie **uniquement** le moteur IA — toutes les autres fonctionnalités (linter, cache, i18n, skills) fonctionnent exactement de la même manière.

---

[← Guide du Linter](/linter) &nbsp;|&nbsp; [Système de Skills →](/skills)

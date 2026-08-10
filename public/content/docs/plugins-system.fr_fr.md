# 🧩 Système de plugins GitPR

Le système de plugins GitPR vous permet d'étendre les capacités de l'outil **globalement**, sur **tous vos projets**, sans dupliquer les fichiers de configuration.

## 📂 Structure des répertoires

Les plugins sont stockés dans votre dossier de configuration globale GitPR :

```
~/.gitpr/plugins/
├── linter/          # Global linter rule packs (.yml / .yaml)
│   ├── security.yml
│   ├── no-debug.yml
│   └── php-psr.yml
└── prompts/         # Custom AI prompt templates (.md)
    ├── audit_security.md
    ├── generate_tests.md
    └── explain_to_business.md
```

> **Astuce :** Ces répertoires sont créés automatiquement lorsque vous exécutez une commande GitPR. Vous pouvez également exécuter `gitpr --plugins` pour vérifier qu'ils existent et lister tous les plugins actifs.

---

## 🔍 Plugins de lint (`plugins/linter/`)

### Ce que c'est

Les plugins de lint sont des fichiers YAML contenant des règles au même format que `.gitpr.linter.yml`, mais appliqués **globalement** — sur tous les projets de votre machine.

### Différence entre local et global

| Aspect | Local (`.gitpr.linter.yml`) | Global (`~/.gitpr/plugins/linter/*.yml`) |
|--------|---------------------------|----------------------------------------|
| **Portée** | Spécifique au projet | Tous les projets de la machine |
| **Versionnage** | Versionné avec le projet | Personnel — non versionné par projet |
| **Cas d'usage** | Conventions d'équipe pour un dépôt | Standards personnels, contrôles de sécurité |

### Fonctionnement

Lorsque GitPR exécute le lint (via `-l`, `-r`, `-f` ou les hooks pre-commit), il :

1. Charge les règles du `.gitpr.skill/.gitpr.linter.yml` local (s'il existe)
2. Parcourt tous les fichiers `.yml` et `.yaml` de `~/.gitpr/plugins/linter/`
3. Fusionne les deux ensembles en une seule liste de règles
4. Applique les règles combinées au diff

Si un plugin global contient du YAML invalide, GitPR affiche un **avertissement jaune** et continue — votre flux de travail n'est jamais bloqué par un plugin cassé.

### Exemple : Pack sécurité

Créez `~/.gitpr/plugins/linter/security.yml` :

```yaml
rules:
  - name: "AWS Access Key leak"
    regex: "AKIA[0-9A-Z]{16}"
    severity: error
    message: "AWS Access Key ID found — this should never be committed."

  - name: "Generic password assignment"
    regex: "(?i)(password|passwd|senha)\\s*=\\s*['\"][^'\"]+['\"]"
    severity: warning
    message: "Hardcoded password detected. Use environment variables."
```

### Exemple : Pack No-Debug

Créez `~/.gitpr/plugins/linter/no-debug.yml` :

```yaml
rules:
  - name: "console.log left behind"
    regex: "console\\.log\\("
    severity: error
    extensions: [".js", ".ts", ".jsx", ".tsx"]
    message: "Remove console.log() before committing."

  - name: "var_dump left behind"
    regex: "var_dump\\("
    severity: error
    extensions: [".php"]
    message: "Remove var_dump() before committing."
```

---

## 💬 Plugins de prompts (`plugins/prompts/`)

### Ce que c'est

Les plugins de prompts sont des fichiers Markdown (`.md`) qui définissent des prompts IA personnalisés. Chaque fichier devient disponible sous la forme :

- D'une **ressource MCP** à `prompt://plugin/<filename>`
- D'un **prompt MCP** nommé `Plugin: <filename>`

Cela permet aux éditeurs dotés d'IA (VS Code, Cursor, Claude Desktop, Zed) d'utiliser vos flux de travail personnalisés.

### Fonctionnement

Au démarrage du serveur MCP (`gitpr --mcp`), GitPR :

1. Analyse `~/.gitpr/plugins/prompts/` à la recherche de fichiers `.md`
2. Enregistre chacun d'eux comme ressource et prompt MCP
3. Les liste aux côtés des prompts intégrés dans `prompt://list`

### Exemple : Auditeur de sécurité

Créez `~/.gitpr/plugins/prompts/audit_security.md` :

```markdown
You are a Senior Security Engineer. Perform a thorough security review of the current diff.

Focus on:
1. **Injection vulnerabilities** (SQL, NoSQL, Command, XPath)
2. **XSS / Cross-Site Scripting** vectors
3. **Sensitive data exposure** (keys, tokens, PII in logs)
4. **Authentication / Authorization** flaws
5. **Insecure deserialization**
6. **Path traversal** risks

For each finding, provide:
- **Severity**: Critical / High / Medium / Low
- **File & Line**: Where the issue is
- **Description**: What the vulnerability is
- **Fix**: Concrete code suggestion

Use the format:
### [Severity] Vulnerability Name
- **File**: path/to/file:line
- **Description**: ...
- **Fix**: ...
```

### Exemple : Générateur de tests PHPUnit

Créez `~/.gitpr/plugins/prompts/generate_tests.md` :

```markdown
You are a Senior PHP Developer specialized in Test-Driven Development.

For the code changes in this diff, generate comprehensive PHPUnit tests following these rules:

1. **100% coverage target** — cover all new/changed methods
2. **Follow PSR-12** coding standards
3. **Use data providers** for multiple input scenarios
4. **Mock external dependencies** (APIs, databases, file systems)
5. **Test edge cases**: null, empty, boundary values, exceptions

Output a ready-to-run PHPUnit test class with:
- Class name matching the source + "Test" suffix
- setUp() for shared fixtures
- test methods prefixed with "test"
- @test, @dataProvider, and @covers annotations
```

---

## 🖥️ CLI : Lister les plugins actifs

Exécutez `gitpr --plugins` pour voir tous les plugins installés :

```
🧩 GitPR Plugin System

🔍 Linter Packs (2):
  - security.yml
  - no-debug.yml

💬 Custom Prompts (1):
  - audit_security.md

💡 Plugin directory: ~/.gitpr/plugins/
```

Utilisez `gitpr -h --plugins` pour obtenir l'aide contextuelle sur le système de plugins.

---

## 🔄 Ordre d'exécution et priorité

| Couche | Priorité | Comportement de remplacement |
|-------|----------|-------------------|
| `.gitpr.linter.yml` local | Chargé en premier | — |
| `plugins/linter/*.yml` global | Ajouté après le local | Même nom de règle = les deux s'exécutent (pas de déduplication) |

Les règles sont **additives** — les plugins globaux ne remplacent jamais les règles locales ; ils s'y ajoutent.

---

## 🛡️ Gestion des erreurs

- **YAML global malformé** → Avertissement jaune, plugin ignoré. Le flux principal continue.
- **Répertoire de plugins manquant** → Ignoré silencieusement. Aucun avertissement.
- **Fichier de plugin vide** → Ignoré sans message.
- **Démarrage du serveur MCP** → Les échecs d'enregistrement des plugins sont interceptés silencieusement. MCP démarre normalement.

---

## 📚 Voir aussi

- [Règles de lint personnalisées](linter-regras-customizadas) — Comment écrire les règles `.gitpr.linter.yml`
- [Skills et modèles](skill-template) — Prompts et règles IA propres au projet
- [Intégration MCP](mcp-integration) — Utiliser GitPR avec les éditeurs IA

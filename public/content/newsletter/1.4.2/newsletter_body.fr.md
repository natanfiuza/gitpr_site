# GitPR 1.4.2 — Nouveautés

## Nouveautés de cette version

- **Deux releases clos — 1.4.1 et 1.4.2 :** la v0.0.18 laissait la 1.4.1 « préparée, mais pas encore close ». Cette fenêtre a clos **les deux** : le tag `v1.4.1` a été créé le 2026-10-18 (PR #218) et le `v1.4.2` le 2026-10-27 (PR #222), avec `CHANGELOG.md` commité dans les deux. PyPI sert déjà la **1.4.2** ; `v1.4.0` n'est plus le dernier tag.
- **Le diff disparaissait dans les dépôts au contenu hérité :** `get_git_full_diff()` décodait la sortie de `git diff` en UTF-8 strict. Un seul octet Latin-1 (`0xFA`) tuait le thread lecteur du subprocess, `stdout` revenait `None` **sans exception** et la CLI annonçait « Aucun nouveau code trouvé » — comme si la branche n'avait aucun changement. Les **10** appels à `subprocess` qui capturent la sortie de git décodent désormais avec `errors="replace"`, et un `None` renvoyé par git **sort avec le code 1** dans PR, review, full review et commit. Un diff réellement vide sort toujours avec **0**, et `--hook` échoue **ouvert**, parce qu'un `prepare-commit-msg` non nul interromprait le commit de l'utilisateur.
- **`explain` et `tests generate` enfin documentés — et corrigés au passage :** c'étaient les **deux dernières** fonctionnalités sans topic dans `docs/`, héritées comme dette pendant plusieurs fenêtres. Elles ont gagné **5 langues chacune** (`docs/explain-pr.*` et `docs/tests-generate.*`, 10 fichiers). Documenter a révélé **quatre défauts réels** : `parse_explain_payload()` plantait sur le dict déjà parsé du fournisseur (`json.loads(dict)` → `TypeError`) ; `generate_test_file` avait la même incompatibilité ; `gitpr tests generate --finding` lisait `cand.finding.suggested_test` et `cand.diff`, des attributs inexistants ; et `gitpr -h --explain` était cassé par un décalage de `dest` (`explain_flag` × `explain`).
- **Les README pointent vers le site de documentation officiel :** **342** destinations de documentation dans les 5 README ont quitté `https://github.com/gitpr-cli/gitpr.git/blob/main/docs/*.md` et pointent désormais vers `https://gitpr.natanfiuza.dev.br/docs/*`, avec la langue dans `?lang=` et l'anglais **sans** paramètre — exactement le format que `src/doc_links.py` canonicalise déjà. **217 URLs uniques** vérifiées avec `curl` : **0 échec**.
- **La dette i18n n'a pas été payée :** les dictionnaires restent à **1620 clés** (×4) et **1618** (`es.json`, `fr.json`), avec **8 clés utilisées dans le code sans traduction** et **2 orphelines** — les mêmes chiffres que le rapport précédent. Ajoutées aux **7 échecs de fixture** de `tests/badge/test_badge_paths.py` et aux **2 échecs connus** de `tests/test_mentor_cli.py`, la suite se referme à **2950 réussis sur 2964** (12 échecs, 2 skips).

## Comment l'utiliser

Mettez à jour depuis PyPI :

```
pip install --upgrade gitpr-cli
```

Les deux dernières fonctionnalités sans documentation ont désormais un guide chacune :

```
gitpr explain                                    # guide du relecteur pour le diff courant
gitpr tests generate --file src/app.py           # dry run : affiche la suite sans rien écrire
gitpr tests generate --file src/app.py --apply   # écrit le fichier de test après confirmation
```

`gitpr -h --explain` remarche et ouvre le guide du relecteur, et `gitpr tests` comme `gitpr tests generate` portent désormais un lien vers leur propre guide dans l'épilogue de l'aide. Le tout est publié en cinq langues sur https://gitpr.natanfiuza.dev.br/docs — et les README pointent maintenant là aussi, avec la langue dans `?lang=`.

Si votre dépôt contient des fichiers dans un encodage hérité, mettez à jour : la 1.4.2 décode la sortie de git avec `errors="replace"` et sort avec le code 1 quand git échoue, au lieu d'annoncer « Aucun nouveau code trouvé » sur une branche qui a bien des changements.

## Astuces utiles

Smart Excludes filtre les lockfiles, fichiers minifiés et générés du diff grâce à la syntaxe pathspec native de Git, avant même que l'IA ne voie quoi que ce soit — l'exemple de la documentation fait passer ~15 000 tokens à ~250 (98 % de moins), tout en injectant la liste des fichiers modifiés comme métadonnée.

# 🧪 Comment Tester Sans Utiliser une Version sur PyPI

Avant de télécharger une nouvelle version sur PyPI et d'utiliser la version `0.1.1`, **testons sur votre propre machine**.

## Installer en Mode "Développeur" (Éditable)

Au lieu d'utiliser la commande normale, ajoutez le flag `-e` (pour editable) :

Ouvrez le terminal, assurez-vous d'être à la racine du projet et exécutez :

```bash
pip install -e .

```
> (Attention à l'espace et au point à la fin)
*(Ce point `.` à la fin signifie "installer le paquet depuis ce répertoire courant").*


### 🪄 Pourquoi Est-ce Magique ?
Lorsque vous utilisez `-e`, Python ne copie pas les fichiers. Il crée un raccourci (lien symbolique) directement vers votre dossier de développement.
Cela signifie qu'à partir de maintenant, toute modification enregistrée dans VS Code prendra effet instantanément dans le terminal, sans jamais avoir besoin de relancer `pip install` pour tester !

Après l'installation, tapez `gitpr` dans votre terminal. Si la bannière s'affiche correctement et que vous n'obtenez pas l'erreur de module — bingo ! Le problème est résolu.

## Publier une Nouvelle Version

Pour publier sur PyPI, il suffit d'exécuter les deux commandes :

```bash
pipenv run python -m build
pipenv run twine upload dist/*

```
> Assurez-vous qu'il n'y a pas d'autres fichiers dans le dossier `/dist`, comme `gitpr.exe`, car cela provoque une erreur.

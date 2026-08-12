# 🧪 How to Test Without Using a PyPI Version

Before uploading a new version to PyPI and spending version `0.1.1`, **let's test on your own machine**.

## Install in "Developer" Mode (Editable)

Instead of using the normal command, add the `-e` flag (for editable):

Open the terminal, make sure you're at the project root, and run:

```bash
pip install -e .

```
> (Pay attention to the space and the dot at the end)
*(That `.` at the end means "install the package from this current directory").*


### 🪄 Why Is This Magic?
When you use `-e`, Python doesn't copy the files. It creates a shortcut (symbolic link) directly to your development folder.
This means that from now on, any change you save in VS Code will take effect instantly in the terminal, without ever needing to run `pip install` again to test!

After installing, type `gitpr` in your terminal. If the banner opens up nicely and you don't get the module error — bingo! The problem is solved.

## Publishing a New Version

To publish to PyPI, just run the pair of commands:

```bash
pipenv run python -m build
pipenv run twine upload dist/*

```
> Make sure there are no other files in the `/dist` folder, such as `gitpr.exe`, as this causes an error.

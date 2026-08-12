# 🧪 Cómo Probar Sin Gastar una Versión en PyPI

Antes de subir una nueva versión a PyPI y gastar la versión `0.1.1`, **vamos a probar en tu propia máquina**.

## Instalar en Modo "Desarrollador" (Editable)

En lugar de usar el comando normal, añade la flag `-e` (de editable):

Abre el terminal, asegúrate de estar en la raíz del proyecto y ejecuta:

```bash
pip install -e .

```
> (Atención al espacio y al punto al final)
*(Ese punto `.` al final significa "instala el paquete desde este directorio actual").*


### 🪄 ¿Por Qué Es Mágico?
Cuando usas `-e`, Python no copia los archivos. Crea un atajo (enlace simbólico) directo a tu carpeta de desarrollo.
¡Esto significa que a partir de ahora, cualquier cambio que guardes en VS Code tendrá efecto instantáneo en el terminal, sin necesidad de ejecutar `pip install` nunca más para probar!

Después de instalar, escribe `gitpr` en tu terminal. Si el banner aparece correctamente y no aparece el error del módulo — ¡bingo! El problema está resuelto.

## Publicar una Nueva Versión

Para publicar en PyPI, solo ejecuta el par de comandos:

```bash
pipenv run python -m build
pipenv run twine upload dist/*

```
> Asegúrate de que no haya otros archivos en la carpeta `/dist`, como `gitpr.exe`, ya que esto causa un error.

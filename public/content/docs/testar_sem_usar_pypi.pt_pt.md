# 🧪 Como Testar Sem Gastar Versão no PyPI

Antes de fazermos o upload de uma nova versão para o PyPI e gastar a versão `0.1.1`, **vamos testar na sua própria máquina**.

## Instalar no Modo "Programador" (Editável)

Em vez de usar o comando normal, adicione a flag `-e` (de editable):

Abra o terminal, garanta que está na raiz do projeto e execute:

```bash
pip install -e .

```
> (Atenção ao espaço e ao ponto no final)
*(Esse ponto `.` no final significa "instalar o pacote a partir deste diretório atual").*


### 🪄 Porque é que isto é mágico?
Quando usa o `-e`, o Python não copia os ficheiros. Ele cria um atalho (link simbólico) direto para a sua pasta de desenvolvimento.
Isto significa que, a partir de agora, qualquer alteração que guardar no VS Code já terá efeito instantaneamente no terminal, sem nunca mais precisar de executar `pip install` para testar!

Depois de instalar, digite `gitpr` no seu terminal. Se o banner abrir corretamente e não der o erro do módulo — bingo! O problema está resolvido.

## Publicar uma Nova Versão

Para publicar no PyPI, basta executar os dois comandos:

```bash
pipenv run python -m build
pipenv run twine upload dist/*

```
> Verifique se não existem outros ficheiros na pasta `/dist`, como o `gitpr.exe`, pois isto causa um erro.

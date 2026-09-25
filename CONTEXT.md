# Documentação do Site

Vocabulário do domínio de documentação do site: como a documentação técnica publicada se organiza em tópicos e idiomas, e como ela se relaciona com a documentação do repositório da CLI GitPR, que é a sua autoridade de conteúdo.

## Language

**Tópico**:
Uma página de documentação técnica, identificada pelo nome do arquivo sem o sufixo de idioma (ex.: `commit-message-ia`). É a unidade que existe ou não existe de cada lado, e é o que o menu do site referencia.
_Avoid_: documento, artigo, página

**Variante**:
Uma das versões de um tópico, uma por idioma. Um tópico tem até cinco variantes.
_Avoid_: tradução, versão

**Sufixo de idioma**:
O trecho que distingue a variante no nome do arquivo. Os códigos diferem entre os dois repositórios: a fonte usa `es_es`/`fr_fr` onde o site usa `es`/`fr`.
_Avoid_: extensão, código de locale

**Canônico**:
O conjunto de sufixos que o site reconhece — `en`, `pt_br`, `pt_pt`, `es`, `fr`. Uma variante fora dele não é lida: cai no fallback inglês.
_Avoid_: correto, oficial

**Monolíngue**:
Um tópico que existe em um único idioma. Não é uma lacuna a preencher: não se inventa tradução que a fonte não tem.
_Avoid_: incompleto, sem tradução

**Legado**:
Uma variante presente no site com sufixo de idioma que não é canônico. É um duplicado obsoleto do ponto de vista do site, mas não é removido sem confirmação.
_Avoid_: antigo, deprecado

**Link cruzado**:
Um link de um tópico para outro, ambos dentro da documentação. Distingue-se de um link externo (fora da documentação) e de uma âncora local (dentro do mesmo tópico), que nunca são alterados.
_Avoid_: referência, link interno

**Reescrita**:
A conversão do destino de um link cruzado para a forma que o site serve. Altera apenas o destino; o texto visível do link é preservado sempre.
_Avoid_: normalização, correção de link

**Idioma do destino**:
O idioma da página para a qual um link cruzado leva. É o idioma do tópico que **contém** o link, não o do tópico apontado — e não depende de a variante existir.
_Avoid_: idioma alvo, idioma de origem
